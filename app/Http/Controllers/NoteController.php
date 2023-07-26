<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Auth;
use Carbon\Carbon;
use Closure;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        if($from_date!=null){
            $notes = Note::where(
                [
                    ['user_id', '=' ,$user->id],
                    ['body', 'like', "%" .  $word_filter . "%"],
                ])
                ->orWhere(
                [
                        ['user_id', '=' ,$user->id],
                        ['title', 'like', "%" .  $word_filter . "%"],
                ])
            ->whereBetween('created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
            ->orderBy('favorite', 'desc')           
            ->paginate($page_numbers);
        }else {
            $notes = Note::where(
                [
                    ['user_id', '=' ,$user->id],
                    ['body', 'like', "%" .  $word_filter . "%"],
                ])
                ->orWhere(
                [
                        ['user_id', '=' ,$user->id],
                        ['title', 'like', "%" .  $word_filter . "%"],
                ])
            ->orderBy('favorite', 'desc')     
            ->paginate($page_numbers);
        }

       

        foreach($notes as $note){
            if($note->lock){
                $note->title = '';
                $note->body = '';
            }
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente.',
            'data' => $notes
        ]);
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            //'tags' => 'required'
        ]);
        
        $user = Auth::user();

        $note = new Note();
        $note->body = $request->body;
        $note->title = $request->title;
        $note->date = $request->date?? Carbon::now();
        $note->lock = $request->lock??false;
        $note->favorite = $request->favorite??false;
        $note->shared = $request->shared?? json_encode([]);
        $note->user_id = $user->id;

 
        if ($note->save()){

            return response()->json([
                'success' => true,
                'message' => 'Registro creado exitosamente.',
                'data' => $note
            ]);

        }else{
            return response()->json([
                'success' => false,
                'message' => 'Note not added'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $note = Note::where('id',$request->id)
        ->where('user_id',$user->id)
        ->first();
        
        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 400);
        }
        $validatePass = true;
        if($note->lock!=$request->lock){
            if(\Hash::check($request->password,$user->password)){
                $validatePass = true;
            }else{
                $validatePass = false;
            }
        }
        
        if($validatePass){
            $updated = $note->fill($request->all())->save();
        }else{
            $updated = false;
        }

        if ($updated)
            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'data' => $note
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Note can not be updated'
            ], 500);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        $note = Note::where('id',$request->id)
        ->where('user_id',$user->id)
        ->first();

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 400);
        }
 
        if ($note->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'data' => $note
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Note can not be deleted'
            ], 500);
        }
    }

    function read_note(Request $request){
        $user = Auth::user();
        $note = Note::find($request->id);

        
        $note = Note::where('id',$request->id)
        ->where('user_id',$user->id)
        ->first();
      
        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found'
            ], 400);
        }else{
            if($note->lock){
                if(\Hash::check($request->password,$user->password)){
                    return response()->json([
                        'success' => true,
                        'message' => 'Registro actualizado correctamente',
                        'data' => $note
                    ]); 
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Ivalid password'
                    ], 400);
                }
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Registro actualizado correctamente',
                    'data' => $note
                ]); 
            }
            
            

        }
 
    }

}
