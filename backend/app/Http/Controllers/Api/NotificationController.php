<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);
            $type = $request->input('type');

            $query = Notification::where('user_id', $user->id);

            if ($type) {
                $query->where('type', $type);
            }

            $notifications = $query->latest()->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $notifications
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            
            $count = Notification::where('user_id', $user->id)
                ->unread()
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => ['unread_count' => $count]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }
}