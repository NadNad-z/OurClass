<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Daftar Notifikasi */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(15);

        return view('notifications.index', compact('notifications', 'user'));
    }

    /** Tandai dibaca dan redirect ke link terkait */
    public function read(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->route('dashboard');
    }

    /** Tandai semua dibaca */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
