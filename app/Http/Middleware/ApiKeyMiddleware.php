<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

//Проверил соответствие ключа значению из енв
class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->query('key');
        if ($key !== config('app.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
