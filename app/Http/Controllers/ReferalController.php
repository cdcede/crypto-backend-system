<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referal;

class ReferalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $referal = Referal::get();
 
        return response()->json([
            'success' => true,
            'data' => $referal
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
            'level' => 'required'
        ]);
 
        $referal = new Referal();
        $referal->name = $request->name;
        $referal->parent = $request->parent;
        $referal->ext_id = $request->ext_id;
 
        if ($referal->save())
            return response()->json([
                'success' => true,
                'data' => $referal->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Referal not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $referal = Referal::find($id);
 
        if (!$referal) {
            return response()->json([
                'success' => false,
                'message' => 'Referal not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $referal->toArray()
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $referal = Referal::find($id);
 
        if (!$referal) {
            return response()->json([
                'success' => false,
                'message' => 'Referal not found'
            ], 400);
        }
 
        $updated = $referal->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Referal can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $referal = Referal::find($id);
 
        if (!$referal) {
            return response()->json([
                'success' => false,
                'message' => 'Referal not found'
            ], 400);
        }
 
        if ($referal->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Referal can not be deleted'
            ], 500);
        }
    }
}
