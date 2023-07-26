<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;
class BlogController extends Controller
{

    /**
     * Display a listing of the resource without api token validation.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBlogNews(Request $request)
    {


       
        $word_filter = $request->word_filter;
        $page_numbers = $request->page_numbers;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $blogs = \DB::table('w_blog')
        ->join('users', 'w_blog.user_id', '=', 'users.id')
        ->select('w_blog.*', 'users.first_name', 'users.last_name')->where('w_blog.status', true)->orderBy('id', 'desc')->paginate($page_numbers);
        //->select('w_blog.*', \DB::raw('concat(users.first_name, users.last_name) as user') )->where('w_blog.status', true)->paginate(3);

        //$blogs = Blog::where('status', true)->paginate(3);
 
 //       return response()->json($blogs);

        return response()->json  ([
            'success' => true,
            'message' => 'Blogs obtenidos con exito',
            'data' => $blogs
        ]);
    }

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
        //$blogs = \DB::table('w_blog')
        //->join('users', 'w_blog.user_id', '=', 'users.id')
        //->select('w_blog.*', 'users.first_name', 'users.last_name')->where('w_blog.status', true)->orderBy('id', 'desc')->paginate($page_numbers);
        //->select('w_blog.*', \DB::raw('concat(users.first_name, users.last_name) as user') )->where('w_blog.status', true)->paginate(3);
        
            $blogs = Blog::wordFilter($word_filter,['title'],'created_at',$from_date,$to_date)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);
        
        foreach ($blogs as $blog) {
            $blog->actions = json_decode(json_encode($blog),true);
        }

        foreach($blogs as $blog){
            $path = public_path($blog->img_post);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $blog->img_post = $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
       


         /*    $full_path_front = Storage::path($blog->img_post);
            $base64Front = base64_encode(Storage::get($blog->img_post));
            $blog->img_post = 'data:'.mime_content_type($full_path_front) . ';base64,' . $base64Front; */
        }






 
        return response()->json([
            'success' => false,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $blogs
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
            'body' => 'required',
            'tags' => 'required',
            'img_post' => 'required'
        ]);
        
        $user = Auth::user();

        $blog = new Blog();
        $blog->title = $request->title;
        $blog->body = $request->body;
       
      
       
      /*   if ($request->hasFile('attachments')) {
            $filename = $attachment->storeAs('attachments/'.$notification->id, $attachment->getClientOriginalName());
            Attachments::create([
            'notification_id' => $notification->id,
            'name' => $attachment->getClientOriginalName(),
            'store_path' => $filename,
            'ext_type' => $attachment->extension(),
            'status' => true,
            ]);
        } */


        if ($request->hasFile('img_post')) {
           /*  $blog->img_post = $request->file('img_post')->storeAs(
                'blogs/'.$user->username , $request->file('img_post')->getClientOriginalName()
            ); */

            $image = $request->file('img_post');
            $image_name = $image->getClientOriginalName();
            $image->move(public_path('/images/blogs/'.$blog->title),$image_name);
            $image_path = "/images/blogs/".$blog->title."/" . $image_name;
            $blog->img_post = $image_path;
        }else {
            return response()->json([
                'status' => false,
                'message' => 'Image invalid'
            ], 422);
        }





       
        $blog->mini_img = $request->mini_img;
        $blog->user_id = $user->id;
        $blog->tags = $request->tags;
        $blog->status = true;

 
        if ($blog->save()){

            return response()->json([
                'success' => true,
                'blog' => $blog
            ]);

        }else{
            return response()->json([
                'success' => false,
                'message' => 'Blog not added'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$blog = Blog::find($id);

        $blog = \DB::table('w_blog')
        ->join('users', 'w_blog.user_id', '=', 'users.id')
        ->select('w_blog.*', 'users.first_name', 'users.last_name')->where('w_blog.id', '=', $id)->where('w_blog.status', true)->get();
 
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $blog
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $blog = Blog::find($request->id);
 
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found'
            ], 400);
        }
 



       
        $blog = $blog->fill($request->all());
        $blog->tags = $request->tags;
        if ($blog->save())
            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'data' => $blog
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Blog can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $blog = Blog::find($request->id);
      
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found'
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
                'message' => 'Blog can not be deleted'
            ], 500);
        }
    }
}
