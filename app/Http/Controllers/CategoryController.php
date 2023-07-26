<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    /**
     * Display a listing of the resource without api token validation.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {

        $categories = Category::where('status', true)->get();
 
        //return response()->json($categories);

         return response()->json  ([
            'success' => false,
            'message' => 'Categorias obtenidos con exito',
            'data' => $categories
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::get();
 
        return response()->json([
            'success' => true,
            'data' => $category
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
            'name' => 'required',
            'status' => 'required',
        ]);
 
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->status = $request->status;
 
        if ($category->save())
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Category not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);
 
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $category
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
 
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 400);
        }
 
        $updated = $category->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Category can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
 
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 400);
        }
 
        if ($category->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category can not be deleted'
            ], 500);
        }
    }
}
