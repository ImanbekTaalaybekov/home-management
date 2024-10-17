<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DevActionReverseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-Dev-Action-Reverse')) {
            DB::beginTransaction();
            try {
                $response = $next($request);
                DB::rollBack();
                return $response;
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }
        return $next($request);
    }
}
