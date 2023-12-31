<?php

namespace App\Http\Controllers;

use App\Models\TableReservation;
use Illuminate\Http\Request;

class TableReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $table = TableReservation::get();
 
        return response()->json($table);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TableReservation  $tableReservation
     * @return \Illuminate\Http\Response
     */
    public function show(TableReservation $tableReservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TableReservation  $tableReservation
     * @return \Illuminate\Http\Response
     */
    public function edit(TableReservation $tableReservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableReservation  $tableReservation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TableReservation $tableReservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableReservation  $tableReservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(TableReservation $tableReservation)
    {
        //
    }
}
