<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Attachments;
use Illuminate\Http\Request;
use Storage;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        {

            $order = $request->order??'desc';
            $page_numbers = $request->page_numbers;
            $word_filter = $request->word_filter;
            $verified = $request->verified;
            $from_date = $request->from_date;
            $to_date = $request->to_date;
    
            switch ($request->column_name) {
                case 'actions':
                    $column_name =  'id';
                break;
                default:
                    $column_name =  $request->column_name??'id';
                break;
            }
    
            if($from_date!=null){
                 $notifications = Notification::with('attachments')
                 ->wordFilter($word_filter)
                ->whereBetween('created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])
                ->orderBy($column_name, $order)
                ->paginate($page_numbers);
            }else{
                $notifications =  Notification::with('attachments')
                ->wordFilter($word_filter)
                ->orderBy($column_name, $order)
                ->paginate($page_numbers);
    
              
            }
            foreach($notifications as $notification){
                $notification->actions = json_decode(json_encode($notification),true);
               /*  foreach ($notification->attachments as $attachment) {
     
                    $attachment->size = Storage::size($attachment->store_path);
        
                    $attachment->time = Storage::lastModified($attachment->store_path);
                    
                } */
            }
            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $notifications
            ]);  
    
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'title' => 'required',
            'body' => 'required',
            'date' => 'required',
            'attachments.*' => 'required|mimes:jpeg,pdf,jpg,png,docx,xlsx,zip,rar|max:20000'
        ]);

        $notification = new Notification();
        $notification->name = $request->name;
        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->date = $request->date;
        $notification->group_id = $request->group_id;
        $notification->status = $request->status;
 
        if ($notification->save()){

            if ($request->hasFile('attachments')) {
                       
                foreach ($request->attachments as $attachment) {

                    $filename = $attachment->storeAs('attachments/'.$notification->id, $attachment->getClientOriginalName());
                    Attachments::create([
                    'notification_id' => $notification->id,
                    'name' => $attachment->getClientOriginalName(),
                    'store_path' => $filename,
                    'ext_type' => $attachment->extension(),
                    'status' => true,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => $notification
                ]);

            }/* else {
                return response()->json([
                    'success' => false,
                    'message' => 'No attachments'
                ], 422);
            } */


        }else{
            return response()->json([
                'success' => false,
                'message' => 'Notification not added'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::with('attachments')->find($id);        
 
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found '
            ], 400);
        }

        foreach ($notification->attachments as $attachment) {

            $full_path = Storage::path($attachment->store_path);
            
            $base64 = base64_encode(Storage::get($attachment->store_path));
            
            $image_data = 'data:'.mime_content_type($full_path) . ';base64,' . $base64;

            $attachment->size = Storage::size($attachment->store_path);

            $attachment->time = Storage::lastModified($attachment->store_path);

            $attachment->file = $image_data;

        }
 
        return response()->json([
            'success' => true,
            'data' => $notification
        ], 200);
    }


    public function get_events()
    {
        
        $notifications = Notification::with('attachments')->get();        
        
        if (!$notifications) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found '
            ], 400);
        }
        foreach($notifications as $notification){

            /* foreach ($notification->attachments as $attachment) {

                $full_path = Storage::path($attachment->store_path);
                
                $base64 = base64_encode(Storage::get($attachment->store_path));
                
                $image_data = 'data:'.mime_content_type($full_path) . ';base64,' . $base64;
    
                $attachment->size = Storage::size($attachment->store_path);
    
                $attachment->time = Storage::lastModified($attachment->store_path);
    
                $attachment->file = $image_data;
    
            } */
            foreach ($notification->attachments as $attachment) {
 
                $attachment->size = Storage::size($attachment->store_path);
    
                $attachment->time = Storage::lastModified($attachment->store_path);
                
            }
        }

      
 
        return response()->json([
            'success' => true,
            'data' => $notifications
            
        ], 200);
    }


 
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $notification = Notification::find($id);
 
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 400);
        }

        $notification->name = $request->name;
        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->date = $request->date;
        $notification->group_id = $request->group_id;
        $notification->status = $request->status;
        //$notification->save();

        if ($request->hasFile('attachments')) {
                       
            foreach ($request->attachments as $attachment) {

                $filename = $attachment->storeAs('attachments/'.$notification->id, $attachment->getClientOriginalName());
                Attachments::create([
                'notification_id' => $notification->id,
                'name' => $attachment->getClientOriginalName(),
                'store_path' => $filename,
                'ext_type' => $attachment->extension(),
                'status' => true,
                ]);
            }
        }
        //echo count($request->file_delete);
        if ($request->file_delete) {
            foreach ($request->file_delete as $files) {

                $file = json_decode($files, true);
    
                foreach ($file as $id) {
                    //echo $id;
                    $attachment = Attachments::find($id);
                    if ($attachment) {
                        $attachment->delete();
                    }
                        
                }
            }    
        }

        if ($notification->save())
            return response()->json([
                'success' => true,
                'message' => 'Notification updated' 
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Notification can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = Notification::find($id);
 
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 400);
        }
 
        if ($notification->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Notification can not be deleted'
            ], 500);
        }
    }
}
