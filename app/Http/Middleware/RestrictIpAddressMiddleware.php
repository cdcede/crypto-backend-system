<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;

class RestrictIpAddressMiddleware
{
    // Blocked IP addresses
    //public $restrictedIp = ['192.168.0.1', '192.168.1.3', '192.168.1.34', '192.168.1.200'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $blockedIp = DB::table('i_p_blockers')->select('ip')->where('status', true)->get();

        $ips = [];

        foreach ($blockedIp as $ip) {

            array_push($ips,$ip->ip);
        }
        //print_r ($ips);

        if (in_array($request->ip(), $ips)) {
            return response()->json(['message' => "No se le permite acceder a este sitio. Comunícate con soporte"],401);
        }
        return $next($request);
    }
}
