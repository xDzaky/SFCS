<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\User;
use App\Services\StudentClassPromotionService;
use App\Services\StudentBulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private readonly StudentBulkImportService $studentImportService,
        private readonly StudentClassPromotionService $studentClassPromotionService
    )
    {
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', 'like', '%'.$request->kelas.'%');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $stats = [
            'total' => User::count(),
            'siswa' => User::where('role', 'siswa')->count(),
            'guru' => User::where('role', 'guru')->count(),
            'teknisi' => User::where('role', 'teknisi')->count(),
            'admin' => User::whereIn('role', ['admin', 'superadmin'])->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:siswa,guru,admin,teknisi,kepsek,superadmin'],
            'nis' => [
                Rule::requiredIf(fn () => $request->input('role') === 'siswa'),
                'nullable',
                'string',
                'max:50',
                'unique:users,nis',
            ],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'nis' => $validated['nis'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'force_password_change' => false,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['pengaduans' => function ($query) {
            $query->latest()->take(5);
        }]);

        // Stats
        $stats = [
            'total_pengaduan' => $user->pengaduans()->count(),
            'pengaduan_selesai' => $user->pengaduans()->where('status', 'selesai')->count(),
            'ditangani' => $user->role === 'teknisi'
                ? $user->assignedPengaduans()->where('status', 'selesai')->count()
                : 0,
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:siswa,guru,admin,teknisi,kepsek,superadmin'],
            'nis' => [
                Rule::requiredIf(fn () => $request->input('role') === 'siswa'),
                'nullable',
                'string',
                'max:50',
                'unique:users,nis,' . $user->id,
            ],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip,' . $user->id],
            'kelas' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nis' => $validated['nis'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
            $data['force_password_change'] = false;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-delete
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        // Check if user has pengaduans
        if ($user->pengaduans()->exists()) {
            return back()->with('error', 'User memiliki pengaduan dan tidak dapat dihapus');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Prevent self-deactivate
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User berhasil {$status}");
    }

    /**
     * Import siswa from CSV.
     */
    public function import(Request $request)
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat melakukan import massal siswa.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'mode' => 'nullable|in:replace_siswa,upsert_only',
        ]);

        $mode = $request->input('mode', 'replace_siswa');
        $result = $this->studentImportService->import($request->file('file')->getPathname(), $mode);

        Log::create([
            'pengaduan_id' => null,
            'user_id' => Auth::id(),
            'action' => 'student_import_batch',
            'description' => 'Import massal siswa dijalankan dengan mode: '.$mode,
            'new_value' => [
                'total_rows' => $result['total_rows'],
                'created' => $result['created'],
                'updated' => $result['updated'],
                'deactivated' => $result['deactivated'],
                'failed' => $result['failed'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        $message = "Import selesai. Created: {$result['created']}, Updated: {$result['updated']}, Deactivated: {$result['deactivated']}, Failed: {$result['failed']}.";

        return back()
            ->with($result['failed'] > 0 ? 'error' : 'success', $message)
            ->with('import_errors', $result['errors']);
    }

    /**
     * Preview promote kelas siswa aktif.
     */
    public function promotePreview(Request $request)
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat melakukan promote kelas.');
        }

        $validated = $request->validate([
            'target_grade' => 'nullable|in:all,x,xi,xii',
        ]);

        $preview = $this->studentClassPromotionService->preview([
            'target_grade' => (string) ($validated['target_grade'] ?? 'all'),
            'actor_id' => (int) Auth::id(),
        ]);

        Log::create([
            'pengaduan_id' => null,
            'user_id' => Auth::id(),
            'action' => Log::ACTION_STUDENT_PROMOTE_PREVIEW,
            'description' => 'Preview promote kelas siswa dibuat.',
            'new_value' => [
                'target_grade' => $preview['options']['target_grade'] ?? 'all',
                'summary' => $preview['summary'] ?? [],
                'preview_token' => $preview['preview_token'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return back()
            ->with('success', 'Preview promote kelas berhasil dibuat. Periksa ringkasan sebelum menerapkan.')
            ->with('promote_preview', $preview);
    }

    /**
     * Apply promote kelas berdasarkan preview token.
     */
    public function promoteApply(Request $request)
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat menerapkan promote kelas.');
        }

        $validated = $request->validate([
            'preview_token' => 'required|string',
        ]);

        try {
            $result = $this->studentClassPromotionService->apply(
                (string) $validated['preview_token'],
                (int) Auth::id()
            );
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        Log::create([
            'pengaduan_id' => null,
            'user_id' => Auth::id(),
            'action' => Log::ACTION_STUDENT_PROMOTE_APPLY,
            'description' => 'Promote kelas siswa diterapkan.',
            'new_value' => [
                'options' => $result['options'] ?? [],
                'summary_before' => $result['summary_before'] ?? [],
                'applied' => $result['applied'] ?? [],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        $applied = $result['applied'] ?? [];
        $message = sprintf(
            'Promote selesai. Updated kelas: %d, Nonaktifkan XII: %d, Skipped: %d.',
            (int) ($applied['updated_class'] ?? 0),
            (int) ($applied['deactivated'] ?? 0),
            (int) ($applied['skipped'] ?? 0)
        );

        return back()
            ->with('success', $message)
            ->with('promote_apply_result', $result);
    }

    /**
     * Download CSV template for siswa import.
     */
    public function importTemplate()
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat mengunduh template import siswa.');
        }

        $rows = $this->studentImportService->templateRows();
        $filename = 'template-import-siswa-'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fwrite($stream, $row.PHP_EOL);
            }
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Name', 'Email', 'Role', 'NIS', 'NIP', 'Kelas', 'No. HP', 'Status', 'Created At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->nis ?? '-',
                    $user->nip ?? '-',
                    $user->kelas ?? '-',
                    $user->no_hp ?? '-',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
