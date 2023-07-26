<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Session;
use Closure;
use Illuminate\Http\Request;
use Auth;
use DB;
class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->user()!=null){
            return $next($request);
        }
        $user = Auth::user();
        $date_now = date("Y-m-d h:i:s");
        $variable = $user->last_access_time;
        $diff = abs(strtotime($date_now) - strtotime($user->last_access_time));

        $years = floor($diff / (365*60*60*24)); 

        $months = floor(($diff - $years * 365*60*60*24)
        / (30*60*60*24)); 
        $days = floor(($diff - $years * 365*60*60*24 - 
             $months*30*60*60*24)/ (60*60*24));
             $hours = floor(($diff - $years * 365*60*60*24 
       - $months*30*60*60*24 - $days*60*60*24)
                                   / (60*60));
        if($hours>7){
            $token = $request->user()->token();
            $token->revoke();
            Session::flush();

            return response()->json($hours, 422);
        }    else{
            $user->last_access_time = date("Y-m-d h:i:s");
            $user->save();
            return $next($request);
        }                           
       


 
       
    }
}
