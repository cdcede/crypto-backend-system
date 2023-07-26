<?php

namespace App\Http\Controllers;

use App\Models\UserGroups;
use Illuminate\Http\Request;

class UserGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$userGroups = UserGroups::with('user', 'group')->get();

        $userGroups = \DB::table('users')->join('user_groups', 'users.id', '=', 'user_groups.user_id')
        ->join('groups', 'groups.id', '=', 'user_groups.group_id')
        ->select('user_groups.id', 'users.username', 'groups.name as group', 'user_groups.status')
        ->where('user_groups.status', true)->orderBy('user_groups.id', 'desc')->get();
 
        return response()->json([
            'success' => true,
            'data' => $userGroups
        ]);
    }
    
    public function getUsersGroup(Request $request)
    {
        $this->validate($request, [
            'group_id' => 'required',
        ]);

        $userGroups = UserGroups::where('group_id', $request->group_id)->get();

        return response()->json($userGroups);
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
        /* if ($request->id) {
            //update
        }else {
            //create
        } */

        /* $this->validate($request, [
            'users_id' => 'required',
            'group_id' => 'required',
        ]); */

        $userGroup = UserGroups::where('group_id', $request->group_id)->get();

        foreach ($userGroup as $group) {

            $user_group = UserGroups::find($group->id);

            //return $user_group;

            $user_group->status = false;

            $user_group->save();
            
        }

        //return $userGroup;

        if (count($request->users_id) > 0) {

            foreach ($request->users_id as $user_id) {
                
                $exists = UserGroups::query()->group($request->group_id)->where('user_id',$user_id)->count();
                
                //return $exists;
                $userGroups = new UserGroups();

                if ($exists == 0) {
                    
                    $userGroups->user_id = $user_id;
                    $userGroups->group_id = $request->group_id;
                    $userGroups->status = true;
                    $userGroups->save();
                    $users[] = $user_id;
                }
                else {

                    $user_group = UserGroups::query()->group($request->group_id)->where('user_id',$user_id)->first();
                    //return $user_group;
                    $user_group->status = true;
                    $user_group->save();
                    $users[] = $user_id;

                }
                
            }

            return response()->json([
                'success' => true,
                'group_id' => $userGroups->group_id,
                'users_id' => $users
            ]);

        }

        return response()->json([
            'success' => true
        ]);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserGroups  $userGroups
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userGroups = UserGroups::find($id);
 
        if (!$userGroups) {
            return response()->json([
                'success' => false,
                'message' => 'UserGroups not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $userGroups->toArray()
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserGroups  $userGroups
     * @return \Illuminate\Http\Response
     */
    public function edit(UserGroups $userGroups)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserGroups  $userGroups
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $userGroups = UserGroups::find($id);
 
        if (!$userGroups) {
            return response()->json([
                'success' => false,
                'message' => 'UserGroup not found'
            ], 400);
        }
 
        $updated = $userGroups->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'UserGroup can not be updated'
            ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserGroups  $userGroups
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserGroups $userGroups, $id)
    {
        $userGroups = UserGroups::find($id);
 
        if (!$userGroups) {
            return response()->json([
                'success' => false,
                'message' => 'UserGroup not found'
            ], 400);
        }
 
        if ($userGroups->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'UserGroup can not be deleted'
            ], 500);
        }
    }
}
