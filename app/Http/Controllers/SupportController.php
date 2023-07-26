<?php

namespace App\Http\Controllers;

use App\Models\Support;
use Browser;
use App\Notifications\SupportNotification;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    //Store Support on database
    public function createSupport(Request $request){

        /* $request->validate([
            'name' =>  'required',
            'email' =>  'required|email|',
            'phone' =>  'required|numeric',
            'type' =>  'required',
            //'device' =>   'required',
            //'ip' =>   'required'
        ]); */

        $support = new Support;

        $support->name = $request->name;
        $support->email = $request->email;
        $support->phone = $request->phone;
        $support->type = $request->type;
        //$support->device = $request->header('User-Agent');

        // json from user web browser
        $device = [
            'user-agent' => Browser::userAgent(),
            'OS' => Browser::platformName(),
            'browser' => Browser::browserName(),
            'device' => Browser::deviceFamily()
        ];
        $support->device = json_encode($device);

        $support->ip = $request->getClientIp();

        $support->save();

        if ($support->type == 'C') {
            $asunto = 'Contacto';
            $mensaje = 'Un agente se pondrá en contacto con usted';
        } elseif ($support->type == 'A') {
            $asunto = 'Asesoría';
            $mensaje = 'Un agente se pondrá en contacto con usted';
        } else {
            $asunto = 'Suscripción';
            $mensaje = 'Usted se ha suscrito al boletin de noticias de Wage Dollar';
        }

        $data = [
            'name' => $support->name,
            'asunto' => $asunto,
            'mensaje' => $mensaje
        ];

        \Notification::route('mail', [
            $request->email => $request->name,
        ])->notify(new SupportNotification($data));

        return response()->json([
            'success' => true,
            'data' => $support,
            'message' => 'Registro enviado correctamente'
        ]);

    }
}
