<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleAuth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'You must be logged in to access this page.');
            return redirect()->to('/login');
        }

        // Get user role from session
        $userRole = strtolower(session('role') ?? '');
        $currentPath = $request->getUri()->getPath();

        // Define role-based access rules
        $accessRules = [
            'admin' => ['/admin', '/announcements'],
            'teacher' => ['/teacher', '/announcements'],
            'student' => ['/student', '/announcements']
        ];

        // Check if user has access to the current path
        $hasAccess = false;
        
        if (isset($accessRules[$userRole])) {
            foreach ($accessRules[$userRole] as $allowedPath) {
                // Check if current path starts with allowed path
                if (strpos($currentPath, $allowedPath) === 0) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        // If user doesn't have access, redirect with error message
        if (!$hasAccess) {
            session()->setFlashdata('error', 'Access Denied: Insufficient Permissions');
            return redirect()->to('/announcements');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
