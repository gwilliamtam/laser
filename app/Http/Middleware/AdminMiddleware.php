<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $username = Auth::user()->email;
        $admins = explode(',', env('ADMINISTRATORS'));
        if (!in_array($username, $admins))
        {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
