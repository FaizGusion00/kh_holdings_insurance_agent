"use client";

import { useState, useEffect, useRef } from "react";
import { Bell, X, Check, Trash2, ExternalLink } from "lucide-react";
import { apiService, Notification } from "../../services/api";
import { LoadingSpinner } from "./LoadingSpinner";

interface NotificationBellProps {
  className?: string;
}

export function NotificationBell({ className = "" }: NotificationBellProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [isMarkingAsRead, setIsMarkingAsRead] = useState<number | null>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Fetch notifications
  const fetchNotifications = async () => {
    try {
      setIsLoading(true);
      console.log("🔔 Fetching notifications...");
      const response = await apiService.getNotifications(50, false); // Fetch all notifications, not just unread
      console.log("🔔 Notifications response:", response);
      if (response.success && response.data) {
        console.log("🔔 Setting notifications:", response.data.data);
        console.log("🔔 Unread count:", response.data.unread_count);
        setNotifications(response.data.data || []);
        setUnreadCount(response.data.unread_count || 0);
      }
    } catch (error) {
      console.error("🔔 Failed to fetch notifications:", error);
    } finally {
      setIsLoading(false);
    }
  };

  // Fetch unread count only
  const fetchUnreadCount = async () => {
    try {
      console.log("🔔 Fetching unread count...");
      const response = await apiService.getUnreadNotificationCount();
      console.log("🔔 Unread count response:", response);
      if (response.success && response.data) {
        console.log("🔔 Setting unread count to:", response.data.unread_count);
        setUnreadCount(response.data.unread_count);
      }
    } catch (error) {
      console.error("🔔 Failed to fetch unread count:", error);
    }
  };

  // Mark notification as read
  const markAsRead = async (notificationId: number) => {
    try {
      setIsMarkingAsRead(notificationId);
      const response = await apiService.markNotificationAsRead(notificationId);
      if (response.success) {
        setNotifications(prev => 
          prev.map(notif => 
            notif.id === notificationId 
              ? { ...notif, is_read: true, read_at: new Date().toISOString() }
              : notif
          )
        );
        setUnreadCount(prev => Math.max(0, prev - 1));
      }
    } catch (error) {
      console.error("Failed to mark notification as read:", error);
    } finally {
      setIsMarkingAsRead(null);
    }
  };

  // Mark all as read
  const markAllAsRead = async () => {
    try {
      const response = await apiService.markAllNotificationsAsRead();
      if (response.success) {
        setNotifications(prev => 
          prev.map(notif => ({ ...notif, is_read: true, read_at: new Date().toISOString() }))
        );
        setUnreadCount(0);
      }
    } catch (error) {
      console.error("Failed to mark all as read:", error);
    }
  };

  // Delete notification
  const deleteNotification = async (notificationId: number) => {
    try {
      const response = await apiService.deleteNotification(notificationId);
      if (response.success) {
        setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
        // Update unread count if the deleted notification was unread
        const deletedNotif = notifications.find(notif => notif.id === notificationId);
        if (deletedNotif && !deletedNotif.is_read) {
          setUnreadCount(prev => Math.max(0, prev - 1));
        }
      }
    } catch (error) {
      console.error("Failed to delete notification:", error);
    }
  };

  // Handle notification click
  const handleNotificationClick = async (notification: Notification) => {
    // Mark as read if not already read
    if (!notification.is_read) {
      await markAsRead(notification.id);
    }

    // Navigate to action URL if available
    if (notification.action_url) {
      window.location.href = notification.action_url;
    }
  };

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Fetch notifications when dropdown opens
  useEffect(() => {
    if (isOpen) {
      fetchNotifications();
    }
  }, [isOpen]);

  // Fetch unread count on mount and periodically
  useEffect(() => {
    fetchUnreadCount();
    const interval = setInterval(fetchUnreadCount, 30000); // Every 30 seconds
    return () => clearInterval(interval);
  }, []);

  return (
    <div className={`relative ${className}`} ref={dropdownRef}>
      {/* Bell Icon */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="relative w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-500 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
      >
        <Bell className="w-5 h-5" />
        {unreadCount > 0 && (
          <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-pulse">
            {unreadCount > 99 ? "99+" : unreadCount}
          </span>
        )}
      </button>

      {/* Dropdown */}
      {isOpen && (
        <div className="absolute right-0 top-12 w-80 sm:w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50">
            <h3 className="text-lg font-semibold text-gray-800">Notifications</h3>
            <div className="flex items-center gap-2">
              {unreadCount > 0 && (
                <button
                  onClick={markAllAsRead}
                  className="text-xs text-blue-600 hover:text-blue-800 font-medium"
                >
                  Mark all read
                </button>
              )}
              <button
                onClick={() => setIsOpen(false)}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
          </div>

          {/* Notifications List */}
          <div className="max-h-80 overflow-y-auto">
            {isLoading ? (
              <div className="flex items-center justify-center p-8">
                <LoadingSpinner size="md" />
              </div>
            ) : notifications.length === 0 ? (
              <div className="text-center py-8 text-gray-500">
                <Bell className="w-12 h-12 mx-auto mb-2 text-gray-300" />
                <p>No notifications yet</p>
              </div>
            ) : (
              <div className="divide-y divide-gray-100">
                {notifications.map((notification) => (
                  <div
                    key={notification.id}
                    className={`p-4 hover:bg-gray-50 transition-colors cursor-pointer ${
                      !notification.is_read ? "bg-blue-50 border-l-4 border-blue-400" : ""
                    }`}
                    onClick={() => handleNotificationClick(notification)}
                  >
                    <div className="flex items-start gap-3">
                      {/* Icon */}
                      <div className={`w-8 h-8 rounded-full flex items-center justify-center text-lg ${
                        notification.background_color
                      }`}>
                        {notification.icon}
                      </div>

                      {/* Content */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <h4 className={`text-sm font-semibold ${
                              notification.is_read ? "text-gray-800" : "text-gray-900"
                            }`}>
                              {notification.title}
                            </h4>
                            <p className="text-xs text-gray-600 mt-1 line-clamp-2">
                              {notification.message}
                            </p>
                            <div className="flex items-center gap-2 mt-2">
                              <span className="text-xs text-gray-400">
                                {notification.time_ago}
                              </span>
                              {notification.is_important && (
                                <span className="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                  Important
                                </span>
                              )}
                            </div>
                          </div>

                          {/* Actions */}
                          <div className="flex items-center gap-1 ml-2">
                            {!notification.is_read && (
                              <button
                                onClick={(e) => {
                                  e.stopPropagation();
                                  markAsRead(notification.id);
                                }}
                                disabled={isMarkingAsRead === notification.id}
                                className="p-1 text-gray-400 hover:text-green-600 transition-colors"
                                title="Mark as read"
                              >
                                {isMarkingAsRead === notification.id ? (
                                  <LoadingSpinner size="sm" />
                                ) : (
                                  <Check className="w-3 h-3" />
                                )}
                              </button>
                            )}
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                deleteNotification(notification.id);
                              }}
                              className="p-1 text-gray-400 hover:text-red-600 transition-colors"
                              title="Delete"
                            >
                              <Trash2 className="w-3 h-3" />
                            </button>
                            {notification.action_url && (
                              <button
                                onClick={(e) => {
                                  e.stopPropagation();
                                  window.location.href = notification.action_url!;
                                }}
                                className="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                title="View details"
                              >
                                <ExternalLink className="w-3 h-3" />
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Footer */}
          {notifications.length > 0 && (
            <div className="p-3 border-t border-gray-200 bg-gray-50 text-center">
              <p className="text-xs text-gray-500">
                Showing {notifications.length} notification{notifications.length !== 1 ? 's' : ''}
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
