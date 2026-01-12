<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block Suspicious Requests Middleware
 * 
 * Mendeteksi dan memblokir request berbahaya seperti:
 * - SQL Injection attempts
 * - Path traversal attacks
 * - Common exploit paths
 * - Suspicious user agents
 */
class BlockSuspiciousRequests
{
    /**
     * Patterns yang menandakan SQL Injection attempt
     */
    protected array $sqlInjectionPatterns = [
        '/(\%27)|(\')|(\-\-)|(\%23)|(#)/i',
        '/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
        '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
        '/((\%27)|(\'))union/i',
        '/exec(\s|\+)+(s|x)p\w+/i',
        '/UNION(\s+)SELECT/i',
        '/INSERT(\s+)INTO/i',
        '/DELETE(\s+)FROM/i',
        '/DROP(\s+)TABLE/i',
        '/UPDATE(\s+)\w+(\s+)SET/i',
    ];

    /**
     * Path traversal patterns
     */
    protected array $pathTraversalPatterns = [
        '/\.\.\//i',
        '/\.\.\\\/i',
        '/%2e%2e%2f/i',
        '/%2e%2e\//i',
        '/\.%2e\//i',
        '/%2e\.\//i',
    ];

    /**
     * Common exploit paths yang sering di-scan bot
     */
    protected array $exploitPaths = [
        '/wp-admin',
        '/wp-login',
        '/wp-content',
        '/wordpress',
        '/phpmyadmin',
        '/pma',
        '/myadmin',
        '/mysql',
        '/admin.php',
        '/shell.php',
        '/c99.php',
        '/r57.php',
        '/.env',
        '/.git',
        '/config.php',
        '/xmlrpc.php',
        '/eval-stdin.php',
    ];

    /**
     * Suspicious user agents (scanning tools)
     */
    protected array $suspiciousAgents = [
        'sqlmap',
        'nikto',
        'nessus',
        'nmap',
        'masscan',
        'zgrab',
        'gobuster',
        'dirbuster',
        'wpscan',
        'acunetix',
        'netsparker',
        'havij',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for suspicious user agent
        if ($this->hasSuspiciousUserAgent($request)) {
            return $this->blockRequest($request, 'Suspicious user agent detected');
        }

        // Check for exploit paths
        if ($this->isExploitPath($request)) {
            return $this->blockRequest($request, 'Blocked exploit path attempt');
        }

        // Check for path traversal
        if ($this->hasPathTraversal($request)) {
            return $this->blockRequest($request, 'Path traversal attempt detected');
        }

        // Check for SQL injection in query string
        if ($this->hasSqlInjection($request)) {
            return $this->blockRequest($request, 'SQL injection attempt detected');
        }

        return $next($request);
    }

    /**
     * Check if request has suspicious user agent
     */
    protected function hasSuspiciousUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        // Block empty user agents (common in bots)
        if (empty($userAgent)) {
            return true;
        }

        foreach ($this->suspiciousAgents as $agent) {
            if (str_contains($userAgent, $agent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request is to an exploit path
     */
    protected function isExploitPath(Request $request): bool
    {
        $path = strtolower($request->path());

        foreach ($this->exploitPaths as $exploitPath) {
            if (str_starts_with('/' . $path, $exploitPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for path traversal attempts
     */
    protected function hasPathTraversal(Request $request): bool
    {
        $fullUrl = $request->fullUrl();

        foreach ($this->pathTraversalPatterns as $pattern) {
            if (preg_match($pattern, $fullUrl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fields to exclude from SQL injection check
     * These fields contain legitimate HTML/text content
     */
    protected array $excludedFields = [
        'content',
        'body',
        'description',
        'message',
        'bio',
        'notes',
        'text',
    ];

    /**
     * Check for SQL injection attempts in query string and input
     */
    protected function hasSqlInjection(Request $request): bool
    {
        // Check query string
        $queryString = $request->getQueryString() ?? '';

        // Check input values, excluding content fields that may contain legitimate HTML
        $inputsToCheck = array_filter($request->except($this->excludedFields), 'is_string');
        $allInput = implode(' ', $inputsToCheck);

        $toCheck = $queryString . ' ' . $allInput;

        foreach ($this->sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $toCheck)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Block the request and log the attempt
     */
    protected function blockRequest(Request $request, string $reason): Response
    {
        // Log the blocked request
        Log::warning('Blocked suspicious request', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Request blocked for security reasons',
        ], 403);
    }
}
