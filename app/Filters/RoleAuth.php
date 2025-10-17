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
        $uri = $request->getUri();
        $currentPath = $uri->getPath();
        
        // Remove base URL path if present (e.g., /ITE311-MALILAY/)
        // Get the route path only
        $baseURL = config('App')->baseURL;
        $basePath = parse_url($baseURL, PHP_URL_PATH);
        if ($basePath && strpos($currentPath, $basePath) === 0) {
            $currentPath = substr($currentPath, strlen($basePath));
        }
        
        // Normalize path (remove trailing slashes and ensure it starts with /)
        $currentPath = '/' . trim($currentPath, '/');
        
        // Debug logging
        log_message('debug', "RoleAuth Filter - User Role: '{$userRole}', Current Path: '{$currentPath}'");

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
                    log_message('debug', "RoleAuth Filter - Access GRANTED for '{$userRole}' to '{$currentPath}' via '{$allowedPath}'");
                    break;
                }
            }
        }

        // If user doesn't have access, redirect with error message
        if (!$hasAccess) {
            log_message('debug', "RoleAuth Filter - Access DENIED for '{$userRole}' to '{$currentPath}'");
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
