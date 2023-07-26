<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Khsing\World\World;

class GeographyController extends Controller
{
    public function countryPhoneCode(){

        $countries = World::Countries();
        
       /*  $countries = \DB::table('countries')
            ->select('id', 'iso2', 'name', 'phone_code')
            ->get();
         */
        return response()->json($countries);
        
    }

    public function states(Request $request){

        $cities = \DB::table('world_cities')
        ->select('id', 'name', 'code')->where('country_id',$request->country_id)
        ->get();
        
        /*$states = \DB::table('states')
            ->select('id', 'name')->where('country_id',$request->country_id)
            ->get();
        */
        //return response()->json($states);
        return response()->json($cities);
        
    }
}
