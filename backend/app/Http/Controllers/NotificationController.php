<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => [
            'notifications' => [],
            'unread_count' => 0,
        ]]);
    }

    public function markAsRead(int $id)
    {
        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead()
    {
        return response()->json(['status' => 'success']);
    }

    public function unreadCount()
    {
        return response()->json(['status' => 'success', 'data' => ['unread_count' => 0]]);
    }
}


