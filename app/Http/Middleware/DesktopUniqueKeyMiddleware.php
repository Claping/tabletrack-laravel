<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;

class DesktopUniqueKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {


        // if ($request->isMethod('OPTIONS')) {
        //     return response('', 200)->header('Access-Control-Max-Age', '86400');
        // }

        $key = $request->header('X-TABLETRACK-KEY');
        $dev = env('APP_ENV') === 'development';

        if (!$key && !$dev) {
            return response()->json(['message' => 'No authentication key found'], 401);
        }

        $branch = $dev ? Branch::first() : Branch::where('unique_hash', $key)->first();

        if (!$branch) {
            return response()->json(['message' => 'Invalid authentication key'], 401);
        }

        $request->merge(['branch' => $branch]);
        return $next($request);
    }
}
