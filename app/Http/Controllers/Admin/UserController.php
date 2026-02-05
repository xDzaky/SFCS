<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
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

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nis_nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
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
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:siswa,guru,admin,teknisi,kepsek,superadmin'],
            'nis_nip' => ['nullable', 'string', 'max:50', 'unique:users'],
            'kelas' => ['nullable', 'string', 'max:20'],
            'no_telp' => ['nullable', 'string', 'max:15'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'nis_nip' => $validated['nis_nip'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'no_telp' => $validated['no_telp'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
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
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:siswa,guru,admin,teknisi,kepsek,superadmin'],
            'nis_nip' => ['nullable', 'string', 'max:50', 'unique:users,nis_nip,' . $user->id],
            'kelas' => ['nullable', 'string', 'max:20'],
            'no_telp' => ['nullable', 'string', 'max:15'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nis_nip' => $validated['nis_nip'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'no_telp' => $validated['no_telp'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
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
     * Import users from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Expected format: name, email, role, nis_nip, kelas
                if (count($row) < 3) continue;

                User::create([
                    'name' => $row[0],
                    'email' => $row[1],
                    'password' => Hash::make('password123'), // Default password
                    'role' => $row[2] ?? 'siswa',
                    'nis_nip' => $row[3] ?? null,
                    'kelas' => $row[4] ?? null,
                    'is_active' => true,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + count($errors) + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "{$imported} user berhasil diimport.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " baris gagal.";
        }

        return back()->with('success', $message);
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

            fputcsv($file, ['Name', 'Email', 'Role', 'NIS/NIP', 'Kelas', 'No. Telp', 'Status', 'Created At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->nis_nip ?? '-',
                    $user->kelas ?? '-',
                    $user->no_telp ?? '-',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
