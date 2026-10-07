<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());

        // Filter by status
        if ($request->status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->status === 'read') {
            $query->whereNotNull('read_at');
        }

        // Filter by type
        if ($request->type) {
            $query->where('jenis', 'like', '%' . $request->type . '%');
        }

        $notifications = $query->latest()->paginate(20);
        $unreadCount = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Check ownership
        if ($notification->user_id != Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        // If AJAX / JSON request
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Get unread count and latest unread notification (for real-time AJAX polling)
     */
    public function unreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0, 'latest_id' => 0, 'latest' => null]);
        }

        $count = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $latest = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->first();

        $latestData = null;
        if ($latest) {
            $link = $latest->link;
            if ($link && str_contains($link, 'pengaduan')) {
                if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas'])) {
                    if (!str_contains($link, '/admin/pengaduan')) {
                        $link = preg_replace('#/(teknisi/)?pengaduan/#', '/admin/pengaduan/', $link, 1);
                    }
                } elseif ($user->role === 'teknisi') {
                    if (!str_contains($link, '/teknisi/pengaduan')) {
                        $link = preg_replace('#/(admin/)?pengaduan/#', '/teknisi/pengaduan/', $link, 1);
                    }
                } elseif (in_array($user->role, ['siswa', 'guru'])) {
                    $link = str_replace('/admin/pengaduan', '/pengaduan', $link);
                    $link = str_replace('/teknisi/pengaduan', '/pengaduan', $link);
                }
            } elseif ($link && str_contains($link, 'pinjaman')) {
                if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas', 'sarpras_bawah'])) {
                    if (!str_contains($link, '/admin/pinjaman')) {
                        $link = preg_replace('#/pinjaman/#', '/admin/pinjaman/', $link, 1);
                    }
                } elseif (in_array($user->role, ['siswa', 'guru'])) {
                    $link = str_replace('/admin/pinjaman', '/pinjaman', $link);
                }
            }
            $link = $link ? preg_replace('#^https?://[^/]+#', '', $link) : null;

            $latestData = [
                'id'      => $latest->id,
                'judul'   => $latest->judul,
                'pesan'   => $latest->pesan,
                'jenis'   => $latest->jenis,
                'icon'    => $latest->jenis_icon,
                'link'    => $link,
                'is_read' => $latest->isRead(),
                'time'    => $latest->created_at ? $latest->created_at->diffForHumans() : 'baru saja',
            ];
        }

        return response()->json([
            'count'     => $count,
            'latest_id' => $latest ? $latest->id : 0,
            'latest'    => $latestData,
        ]);
    }

    /**
     * Get latest unread notification (for browser notification & alert toast)
     */
    public function latest()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(null);
        }

        $notification = Notification::where('user_id', $user->id)
            ->where(fn ($q) => $q->where('is_read', false)->orWhereNull('read_at'))
            ->latest()
            ->first();

        if (!$notification) {
            return response()->json(null);
        }

        $link = $notification->link;
        if ($link && str_contains($link, 'pengaduan')) {
            if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas'])) {
                if (!str_contains($link, '/admin/pengaduan')) {
                    $link = preg_replace('#/(teknisi/)?pengaduan/#', '/admin/pengaduan/', $link, 1);
                }
            } elseif ($user->role === 'teknisi') {
                if (!str_contains($link, '/teknisi/pengaduan')) {
                    $link = preg_replace('#/(admin/)?pengaduan/#', '/teknisi/pengaduan/', $link, 1);
                }
            } elseif (in_array($user->role, ['siswa', 'guru'])) {
                $link = str_replace('/admin/pengaduan', '/pengaduan', $link);
                $link = str_replace('/teknisi/pengaduan', '/pengaduan', $link);
            }
        } elseif ($link && str_contains($link, 'pinjaman')) {
            if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas', 'sarpras_bawah'])) {
                if (!str_contains($link, '/admin/pinjaman')) {
                    $link = preg_replace('#/pinjaman/#', '/admin/pinjaman/', $link, 1);
                }
            } elseif (in_array($user->role, ['siswa', 'guru'])) {
                $link = str_replace('/admin/pinjaman', '/pinjaman', $link);
            }
        }

        $link = $link ? preg_replace('#^https?://[^/]+#', '', $link) : null;

        return response()->json([
            'id'      => $notification->id,
            'judul'   => $notification->judul,
            'pesan'   => $notification->pesan,
            'jenis'   => $notification->jenis,
            'icon'    => $notification->jenis_icon,
            'link'    => $link,
            'is_read' => $notification->isRead(),
            'time'    => $notification->created_at->diffForHumans(),
        ]);
    }

    /**
     * Get latest notifications for dropdown (AJAX)
     * GET /notifications/dropdown
     */
    public function dropdown()
    {
        $user          = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(7)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where(fn ($q) => $q->where('is_read', false)->orWhereNull('read_at'))
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(function ($n) use ($user) {
                $link = $n->link;

                // Fix stale/wrong links stored in DB for existing notifications
                if ($link && str_contains($link, 'pengaduan')) {
                    if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas'])) {
                        // Ensure admin and sarpras_atas always gets /admin/pengaduan/ link
                        if (!str_contains($link, '/admin/pengaduan')) {
                            $link = preg_replace('#/(teknisi/)?pengaduan/#', '/admin/pengaduan/', $link, 1);
                        }
                    } elseif ($user->role === 'teknisi') {
                        if (!str_contains($link, '/teknisi/pengaduan')) {
                            $link = preg_replace('#/(admin/)?pengaduan/#', '/teknisi/pengaduan/', $link, 1);
                        }
                    } elseif (in_array($user->role, ['siswa', 'guru'])) {
                        $link = str_replace('/admin/pengaduan', '/pengaduan', $link);
                        $link = str_replace('/teknisi/pengaduan', '/pengaduan', $link);
                    }
                } elseif ($link && str_contains($link, 'pinjaman')) {
                    if (in_array($user->role, ['admin', 'superadmin', 'sarpras_atas', 'sarpras_bawah'])) {
                        if (!str_contains($link, '/admin/pinjaman')) {
                            $link = preg_replace('#/pinjaman/#', '/admin/pinjaman/', $link, 1);
                        }
                    } elseif (in_array($user->role, ['siswa', 'guru'])) {
                        $link = str_replace('/admin/pinjaman', '/pinjaman', $link);
                    }
                }

                // Strip absolute URL scheme+host so navigation stays on same host
                $link = $link ? preg_replace('#^https?://[^/]+#', '', $link) : null;

                return [
                    'id'      => $n->id,
                    'judul'   => $n->judul,
                    'pesan'   => $n->pesan,
                    'jenis'   => $n->jenis,
                    'icon'    => $n->jenis_icon,
                    'link'    => $link,
                    'is_read' => $n->isRead(),
                    'time'    => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }
}
