<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is banned (status = 0)
            if ($user->status == 0) {
                Auth::logout();
                
                return redirect()->route('login')
                    ->with('error', 'Your account has been deactivated. Please contact support.');
            }
        }

        return $next($request);
    }
}

