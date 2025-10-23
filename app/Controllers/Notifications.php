<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Get notifications for the current user
     * Returns JSON response with unread count and notifications list
     */
    public function get()
    {
        // Debug logging
        log_message('info', 'Notifications::get() called');
        log_message('info', 'Session userID: ' . (session('userID') ?? 'null'));
        log_message('info', 'Session role: ' . (session('role') ?? 'null'));
        
        // Check if user is logged in
        if (!session('userID')) {
            log_message('warning', 'User not logged in for notifications');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $userId = session('userID');
        log_message('info', 'Getting notifications for user ID: ' . $userId);
        
        try {
            // Get unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            // Get latest notifications (limit 5)
            $notifications = $this->notificationModel->getNotificationsForUser($userId, 5);
            
            // Format notifications for JSON response
            $formattedNotifications = [];
            foreach ($notifications as $notification) {
                $formattedNotifications[] = [
                    'id' => $notification['id'],
                    'message' => $notification['message'],
                    'is_read' => (bool) $notification['is_read'],
                    'created_at' => $notification['created_at'],
                    'time_ago' => $this->timeAgo($notification['created_at'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'unread_count' => $unreadCount,
                'notifications' => $formattedNotifications
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Notification fetch error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to fetch notifications'
            ])->setStatusCode(500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        // Debug logging
        log_message('info', 'MarkAsRead called for notification ID: ' . $notificationId);
        log_message('info', 'Session userID: ' . (session('userID') ?? 'null'));
        
        // Check if user is logged in
        if (!session('userID')) {
            log_message('warning', 'User not logged in for mark as read');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $userId = session('userID');
        log_message('info', 'Marking notification as read for user: ' . $userId);
        
        try {
            // Verify the notification belongs to the current user
            $notification = $this->notificationModel->find($notificationId);
            log_message('info', 'Found notification: ' . json_encode($notification));
            
            if (!$notification || $notification['user_id'] != $userId) {
                log_message('warning', 'Notification not found or access denied. Notification: ' . json_encode($notification) . ', User: ' . $userId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Notification not found or access denied'
                ])->setStatusCode(404);
            }

            // Mark as read
            log_message('info', 'Attempting to mark notification as read');
            $result = $this->notificationModel->markAsRead($notificationId);
            log_message('info', 'Mark as read result: ' . ($result ? 'success' : 'failed'));
            
            if ($result) {
                // Get updated unread count
                $unreadCount = $this->notificationModel->getUnreadCount($userId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Notification marked as read',
                    'unread_count' => $unreadCount
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to mark notification as read'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Mark as read error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ])->setStatusCode(500);
        }
    }

    /**
     * Create a new notification (for testing purposes)
     */
    public function createTestNotification()
    {
        // Check if user is logged in
        if (!session('userID')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in'
            ])->setStatusCode(401);
        }

        $userId = session('userID');
        $message = 'Test notification created at ' . date('Y-m-d H:i:s');
        
        try {
            $result = $this->notificationModel->createNotification($userId, $message);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test notification created successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create test notification'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Create notification error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create test notification'
            ])->setStatusCode(500);
        }
    }

    /**
     * Helper function to format time ago
     */
    private function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return 'just now';
        } elseif ($time < 3600) {
            $minutes = floor($time / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time < 86400) {
            $hours = floor($time / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($time / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }
}
