<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

class Announcement extends BaseController
{
    protected $announcementModel;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
    }

    /**
     * Display all announcements
     * Fetches announcements from database and passes them to view
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'You must be logged in to view announcements.');
            return redirect()->to('/login');
        }

        // Fetch all announcements ordered by created_at in descending order (newest first)
        $announcements = $this->announcementModel->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Announcements',
            'announcements' => $announcements
        ];

        return view('announcements', $data);
    }
}
