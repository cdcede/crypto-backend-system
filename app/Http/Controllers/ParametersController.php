<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parameters;
use DB;

class ParametersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$parameters = Parameters::get();

        $parameters = DB::table('parameters')->select('commission_level')->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $parameters
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
 
        $parameters = new Parameters();
        $parameters->name = $request->name;
        $parameters->parent = $request->parent;
        $parameters->ext_id = $request->ext_id;
 
        if ($parameters->save())
            return response()->json([
                'success' => true,
                'data' => $parameters->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Parameters not added'
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
        $parameters = Parameters::find($id);
 
        if (!$parameters) {
            return response()->json([
                'success' => false,
                'message' => 'Parameters not found '
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $parameters->toArray()
        ], 400);
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
        $parameters = Parameters::find($id);
 
        if (!$parameters) {
            return response()->json([
                'success' => false,
                'message' => 'Parameters not found'
            ], 400);
        }
 
        $updated = $parameters->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Parameters can not be updated'
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
        $parameters = Parameters::find($id);
 
        if (!$parameters) {
            return response()->json([
                'success' => false,
                'message' => 'Parameters not found'
            ], 400);
        }
 
        if ($parameters->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Parameters can not be deleted'
            ], 500);
        }
    }

    public function createLevel(Request $request){

        $this->validate($request, [
            'commission_level' => 'required'
        ]);

        $parameters = Parameters::find(1);
 
        if (!$parameters) {
            return response()->json([
                'success' => false,
                'message' => 'Parameters not found'
            ], 400);
        }
 
        $parameters->commission_level = $request->commission_level;
 
        if ($parameters->save())
            return response()->json([
                'success' => true,
                'message' => 'Actualizado correctamente',
                'data' => $parameters
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Parameters can not be updated'
            ], 500);

    }

    public function depositFee(){
        $parameters = DB::table('parameters')->select('deposit_fee')->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $parameters
        ]);
    }

    public function coinpaymentsConfig(Request $request){

        $this->validate($request, [
            'coinpayments' => 'required'
        ]);

        $coinpayments = Parameters::find(1);
 
        if (!$coinpayments) {
            return response()->json([
                'success' => false,
                'message' => 'Coinpayments not found'
            ], 400);
        }
 
        $coinpayments->coinpayments = $request->coinpayments;
 
        if ($coinpayments->save())
            return response()->json([
                'success' => true,
                'message' => 'Actualizado correctamente',
                'data' => $coinpayments->coinpayments
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Coinpayments can not be updated'
            ], 500);

    }


    public function reinventionConfig(Request $request){

        $this->validate($request, [
            'reinvention' => 'required'
        ]);

        $reinvention = Parameters::find(1);
 
        if (!$reinvention) {
            return response()->json([
                'success' => false,
                'message' => 'Reinversion not found'
            ], 400);
        }

        $reinvention->reinvention = $request->reinvention;
 
        if ($reinvention->save())
            return response()->json([
                'success' => true,
                'message' => 'Actualizado correctamente',
                'data' => $reinvention->reinvention
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Reinvention can not be updated'
            ], 500);

    }

    public function getCoinpayments()
    {
        $coinpayments = DB::table('parameters')->select('coinpayments')->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $coinpayments
        ]);
    }
    public function getReinvention()
    {
        $reinvention = DB::table('parameters')->select('reinvention')->get();
 
        return response()->json([
            'success' => true,
            'message' => 'Consulta ejecutada correctamente',
            'data' => $reinvention
        ]);
    }
}
