<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureMiddleware
{
    /**
     * Usage in routes:  ->middleware('feature:attendance')
     *
     * Multiple features (ALL required): ->middleware('feature:exams,results')
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = TenantService::current();

        // No tenant context (owner panel, artisan, etc.) — allow through
        if (!$tenant) {
            return $next($request);
        }

        foreach ($features as $featureKey) {
            if (!$tenant->hasFeature(trim($featureKey))) {
                // AJAX / JSON requests get a JSON error
                if ($request->expectsJson()) {
                    return response()->json([
                        'error'   => 'feature_disabled',
                        'message' => "The '{$featureKey}' feature is not available on your plan.",
                    ], 403);
                }

                // Regular requests get a clean blade page
                return response()->view('tenant.feature-blocked', [
                    'tenant'     => $tenant,
                    'featureKey' => $featureKey,
                ], 403);
            }
        }

        return $next($request);
    }
}
