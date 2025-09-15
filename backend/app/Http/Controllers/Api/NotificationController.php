<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications.
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->id();
            $limit = $request->get('limit', 100);
            $unreadOnly = $request->get('unread_only', false);

            $notifications = $this->notificationService->getUserNotifications($userId, $limit, $unreadOnly);

            return response()->json([
                'success' => true,
                'data' => $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'data' => $notification->data,
                        'action_url' => $notification->action_url,
                        'action_text' => $notification->action_text,
                        'is_read' => $notification->is_read,
                        'is_important' => $notification->is_important,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at,
                        'time_ago' => $notification->time_ago,
                        'icon' => $notification->icon,
                        'color' => $notification->color,
                        'background_color' => $notification->background_color,
                    ];
                }),
                'unread_count' => $this->notificationService->getUnreadCount($userId),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount()
    {
        try {
            $userId = auth()->id();
            $count = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $success = $this->notificationService->markAsRead($id, $userId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            $userId = auth()->id();
            $this->notificationService->markAllAsRead($userId);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification.
     */
    public function destroy($id)
    {
        try {
            $userId = auth()->id();
            $notification = Notification::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a test notification (for development).
     */
    public function createTest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'title' => 'required|string',
                'message' => 'required|string',
                'is_important' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();
            $notification = $this->notificationService->create([
                'user_id' => $userId,
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'is_important' => $request->get('is_important', false),
                'action_url' => $request->get('action_url'),
                'action_text' => $request->get('action_text'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => 'Test notification created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create test notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}