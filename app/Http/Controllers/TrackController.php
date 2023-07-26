<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Track;
use Browser;

class TrackController extends Controller
{
    
    public function track(Request $request){

        $track = new Track;

        $track->previous_url = $request->previous_url; 
        $track->history_number = $request->history_number;
        $track->ip = $request->getClientIp();

        $device = [
            'user-agent' => Browser::userAgent(),
            'OS' => Browser::platformName(),
            'browser' => Browser::browserName(),
            'device' => Browser::deviceFamily()
        ];
        $track->device = json_encode($device);

        $track->save();

        if ($track) {
            return response()->json([
                'success' => true,
                'data' => $track,
                'message' => 'Registro enviado correctamente'
            ]);
        }

    }

}
