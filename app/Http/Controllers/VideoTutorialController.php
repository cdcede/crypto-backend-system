<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoTutorial;

class VideoTutorialController extends Controller
{
    public function index(Request $request){
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $order = $request->order??'desc'; 

        
        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

        $video_tutorials = VideoTutorial::wordFilter($word_filter,['name','description'],'created_at',$from_date,$to_date)
        ->orderBy($column_name, $order)
        ->paginate($page_numbers);

        foreach ($video_tutorials as $video_tutorial) {
            $video_tutorial->actions = json_decode(json_encode($video_tutorial),true);
        }




        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $video_tutorials
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'link' => 'required',
            'status' => 'required',
        ]);

        $video_tutorial = new VideoTutorial();

        $video_tutorial->name = $request->name;
        $video_tutorial->description = $request->description;
        $video_tutorial->link = $request->link;
        $video_tutorial->status = $request->status;
        $video_tutorial->save();

        return response()->json([
            'success' => true, 
            'message' => 'Registro creado correctamente.',
            'data' => $video_tutorial
        ]);
    }
    
    public function update(Request $request){

        $video_tutorial = VideoTutorial::find($request->id);
 
        if (!$video_tutorial) {
            return response()->json([
                'success' => false,
                'message' => 'Video Tutorial not found'
            ], 400);
        }

        $video_tutorial->name = $request->name;
        $video_tutorial->description = $request->description;
        $video_tutorial->link = $request->link;
        $video_tutorial->status = $request->status;

        if ($video_tutorial->save())
            return response()->json([
                'success' => true,
                'message' => 'Video Tutorial actualizado correctamente.',
                'data' => $video_tutorial
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Video Tutorial can not be updated'
            ], 500);

    }

    public function destroy(Request $request){
        $video_tutorial = VideoTutorial::find($request->id);
 
        if (!$video_tutorial) {
            return response()->json([
                'success' => false,
                'message' => 'Video Tutorial not found'
            ], 400);
        }
 
        if ($video_tutorial->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Video Tutorial eliminado correctamente.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Video Tutorial can not be deleted'
            ], 500);
        }
    }
}
