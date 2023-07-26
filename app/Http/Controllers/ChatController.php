<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Models\Chat;

class ChatController extends Controller
{
    public function queryBot(Request $request){


        if ( is_numeric($request->search) ) {
            $chat = \DB::select("select * from chats where id_group_chat = $request->search");
       
        }else{
            $chat = \DB::select(
                "select * from chats where id_group_chat = (select id from chats 
                where UPPER(text) like UPPER('%$request->search%') and id_group_chat = $request->id_group_chat LIMIT 1)");
        }
       
        if (!$chat) {
            return response()->json([
                'success' => true,
                'errors' => $message = ['No se encontro una respuesta valida.'],
                'data' => $chat
            ],400);
        }
 
        return response()->json([
            'success' => false,
            'data' => $chat
        ], 200);
    }
}
