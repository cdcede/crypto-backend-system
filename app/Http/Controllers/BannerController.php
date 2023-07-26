<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Storage;
//use Illuminate\Http\File;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $order = $request->order??'desc';
        switch ($request->column_name) {
            case 'actions':
                $column_name =  'id';
            break;
/*             case 'activity_amount':
                $column_name =  'amount';
            break; */
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

        $banners = Banner::wordFilter($word_filter,['title'],'created_at',$from_date,$to_date)
        ->orderBy($column_name, $order)
        ->paginate($page_numbers);
        
        foreach ($banners as $banner) {
            $banner->actions = json_decode(json_encode($banner),true);
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $banners
        ]);
    }


    public function banners_noPaginate(Request $request)
    {
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        //return $page_numbers;
        if($from_date!=null){
            $banners = Banner::wordFilter($word_filter)->whereBetween('created_at',[$from_date.' 00:00:00',$to_date.' 23:59:59'])->paginate($page_numbers);
        }else {
            $banners = Banner::wordFilter($word_filter)->get();
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $banners
        ]);
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
            'title' => 'required',
            'path' => 'required|mimes:jpeg,jpg,png|max:20000',
            'date' => 'required',
        ]);

        if ($request->hasFile('path')) {
            //$path = Storage::putFile('path', new File('/public'));
            /* $file = $request->file('avatar');
 
            $name = $file->hashName(); // Generate a unique, random name...
            $extension = $file->extension(); // Determine the file's extension based on the file's MIME type... */

            $imageName = time().'.'.$request->path->getClientOriginalExtension();
            $request->path->move(public_path('images/banners'), $imageName);
        }

        $banner = new Banner();
        $banner->title = $request->title;
        $banner->date = $request->date;
        $banner->path = '/images/banners/'.$imageName;
        $banner->status = $request->status;
 
        if ($banner->save()){

            return response()->json([
                'success' => true,
                'message' => 'Registro creado correctamente.',
                'data' => $banner
            ]);

        }else{
            return response()->json([
                'success' => false,
                'message' => 'Banner not added'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $banner = \DB::table('w_banner')
        ->where('w_banner.id', '=', $id)
        ->where('w_banner.status', true)
        ->get();
 
        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $banner
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function edit(Banner $banner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Banner $banner)
    {
        $banner = Banner::find($request->id);
 
        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 400);
        }

        $banner->title = $request->title;
        $banner->date = $request->date;

        if ($request->hasFile('path')) {

            //$delete_banner = Storage::delete($banner->path);
            //Storage::disk('local')->delete('path/file.jpg');

            //return $banner->path;

            $imageName = time().'.'.$request->path->getClientOriginalExtension();
            $request->path->move(public_path('images/banners'), $imageName);
            $banner->path = '/images/banners/'.$imageName;
        }

        $banner->status = $request->status;

        if ($banner->save())
            return response()->json([
                'success' => true,
                'message' => 'Banner updated' 
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Banner can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $blog = Banner::find($request->id);
 
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found'
            ], 400);
        }
 
        if ($blog->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente',
                'data' => $blog
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Banner can not be deleted'
            ], 500);
        }
    }
}
