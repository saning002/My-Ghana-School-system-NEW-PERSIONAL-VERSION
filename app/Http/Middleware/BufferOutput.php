<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * BufferOutput
 * ─────────────
 * Prevents the "white page with DOCTYPE" flash that occurs when PHP starts
 * streaming HTML to the browser before slow DB queries (Neon ~700ms) complete.
 *
 * How it works:
 *   1. ob_start() captures ALL output — nothing reaches the browser yet
 *   2. The full page renders (DB queries, Blade compilation, all of it)
 *   3. Only after the complete response is built does it flush to the browser
 *
 * The browser therefore receives the entire HTML in one shot — no partial
 * renders, no white flash, no "DOCTYPE" appearing alone.
 *
 * Also sets Cache-Control: no-store so browsers never serve stale admin pages.
 */
class BufferOutput
{
    public function handle(Request $request, Closure $next): Response
    {
        // Start output buffer — captures any PHP output / early echoes
        if (!ob_get_level()) {
            ob_start();
        }

        $response = $next($request);

        // Don't interfere with streamed / binary responses
        if ($response instanceof StreamedResponse) {
            ob_end_clean();
            return $response;
        }

        // Discard any stray output that leaked before the response object
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Ensure admin pages are never cached by browser or proxy
        if (!$response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return $response;
    }
}
