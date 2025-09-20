<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\prTicket;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function fetch()
    {
        $userId = auth()->id();
        $notifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->with('prTicket')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $count = Notification::where('user_id', $userId)->where('is_read', false)->count();
        $html = '';
        foreach ($notifications as $notification) {
            $ticket = $notification->prTicket;
            $html .= '<a href="' . route('ticketDetails', $ticket->id) . '" class="text-reset notification-item" data-id="' . $notification->id . '">';
            $html .= '<div class="d-flex">';
            $html .= '<div class="avatar-xs me-3">';
            $html .= '<span class="avatar-title bg-' . ($notification->status === 'Approved' ? 'success' : ($notification->status === 'Rejected' ? 'danger' : 'primary')) . ' rounded-circle font-size-16">';
            $html .= '<i class="bx bx-file"></i>'; 
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<div class="flex-grow-1">';
            $html .= '<h6 class="mb-1">' . htmlspecialchars($notification->message, ENT_QUOTES, 'UTF-8') . '</h6>';
            $html .= '<div class="font-size-12 text-muted">';
            $html .= '<p class="mb-0"><i class="mdi mdi-clock-outline"></i> ' . $notification->created_at->diffForHumans() . '</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }

        return response()->json(['count' => $count, 'html' => $html ?: '<p class="p-3 text-center">No new notifications.</p>']);
    }

    public function read(Request $request)
    {
        $notification = Notification::find($request->id);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function all()
    {
        $userId = auth()->id();
        $notifications = Notification::where('user_id', $userId)
            ->with('prTicket')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.all', compact('notifications'));
    }
}