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

        // If AJAX request
        if (request()->ajax()) {
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

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Get unread count (for AJAX)
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get latest unread notification (for browser notification)
     */
    public function latest()
    {
        $notification = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->latest()
            ->first();

        return response()->json($notification);
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
                    if (in_array($user->role, ['admin', 'superadmin'])) {
                        // Ensure admin always gets /admin/pengaduan/ link
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
