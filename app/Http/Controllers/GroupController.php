<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use DB;
class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
/*             case 'activity_amount':
                $column_name =  'amount';
            break; */
            default:
                $column_name =  $request->column_name??'id';
            break;
        }

      



            $groups =  Group::wordFilter($word_filter,['name','description'],'created_at',$from_date,$to_date)
            ->orderBy($column_name, $order)
            ->paginate($page_numbers);

            foreach ($groups as $group) {
                $group->actions = json_decode(json_encode($group),true);
            }

            return response()->json([
                'success' => false,
                'message' => 'Consulta ejecutada correctamente.',
                'data' => $groups
            ]);

 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function groups()
    {
        $groups = Group::get();

        return response()->json([
            'success' => true,
            'data' => $groups
        ], 200);
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
            'description' => 'required',
            'status' => 'required',
        ]);
 
        $group = new Group();
        $group->name = $request->name;
        $group->description = $request->description;
        $group->status = $request->status;
 
        if ($group->save())
            return response()->json($group);
        else
            return response()->json([
                'success' => false,
                'message' => 'Group not added'
            ], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::find($id);
 
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $group->toArray()
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //return $request->id;

        $group = Group::find($request->id);
        $group->name = $request->name;
        $group->description = $request->description;
        $group->status = $request->status;



        

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 400);
        }
 
        $updated = $group->save();
 
        if ($updated)
            return response()->json([
                'success' => true,
                'group' => $group
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Group can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::find($id);
 
        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 400);
        }
 
        if ($group->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Group can not be deleted'
            ], 500);
        }
    }
}
