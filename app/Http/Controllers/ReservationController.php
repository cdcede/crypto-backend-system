<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /* $reservations = Reservation::get();

        return response()->json($reservations); */

        $month =  $request->month;
        $year = $request->year;

        /* $string = now();

        $timestamp = strtotime($string); */

        $actualDay = date('d',strtotime(now()));

        $next_month_ts = strtotime($year.$month.$actualDay.'+1 month');

        $prev_month_ts = strtotime($year.$month.$actualDay.'-1 month');

        $next_month = date('Y-m-d', $next_month_ts);

        $prev_month = date('Y-m-d', $prev_month_ts);

        /* $from = date($year . '-' . ($month - 1) . '-20');
        $to = date($year . '-' . ($month + 1) . '-14'); */
        $user = Auth::user();
        $reservations = Reservation::whereBetween('from_date',[$prev_month, $next_month])->where('status', true)->get();

        foreach ($reservations as $reservation) {
            if($reservation->user_id != $user->id){
                $reservation->title = 'Reservate';
                $reservation->note = 'Private';
            } 
        }

        //$query_reservation = \DB::select("SELECT * FROM reservations where from_date between '" . $prev_month . "' and '" . $next_month . "' and status = true");
        
        return response()->json($reservations);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $month =  $request->$month;
        $year = $request->$year;
        $from = date($year . '-' . ($month - 1) . '-20');
        $to = date($year . '-' . ($month + 1) . '-14');
        $query_reservation = \DB::select("SELECT * FROM reservations where from_date between '" . $from . "' and '" . $to . "' and status = true");
        return response()->json($query_reservation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reservation $reservation)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $reservation = Reservation::find($request->id);
 
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 400);
        }
 
        if ($reservation->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Reservation can not be deleted'
            ], 500);
        }
    }

    public function validateReservationDate($from, $to, $table_id)
    {

        $query_reservation = \DB::select("SELECT * FROM reservations 
        where ((from_date between '" . $from . "' and '" . $to . "')
        or (to_date between '" . $from . "' and '" . $to . "')
        or (from_date <=  '" . $from . "' and to_date >= '" . $to . "')
        )
        and table_id = " . $table_id);

        return (count($query_reservation) == 0);
    }

    public function validateReservationUpdate($from, $to, $table_id, $reservation_id)
    {

        $query_reservation = \DB::select("SELECT * FROM reservations 
        where ((from_date between '" . $from . "' and '" . $to . "')
        or (to_date between '" . $from . "' and '" . $to . "')
        or (from_date <=  '" . $from . "' and to_date >= '" . $to . "')
        )
        and table_id = " . $table_id."
        and id != " . $reservation_id);

        return (count($query_reservation) == 0);
    }

    public function createReservation(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'table_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $user = Auth::user();

        $reservation = new Reservation();
        $reservation->title = $request->title;
        $reservation->user_id = $user->id;
        $reservation->table_id = $request->table_id;
        $reservation->from_date = $request->from_date;
        $reservation->to_date = $request->to_date;
        $reservation->note = $request->note;
        $reservation->status = true;

        //$reservation_query = Reservation::whereBetween('from_date',[])

        /* $query_reservation = \DB::select("SELECT * FROM reservations 
        where ((from_date between '".$request->from_date."' and '".$request->to_date."')
        or (to_date between '".$request->from_date."' and '".$request->to_date."')
        or (from_date <=  '".$request->from_date."' and to_date >= '".$request->to_date."')
        )
        and table_id = ".$request->table_id); */

        //return $query_reservation;

        if ($this->validateReservationDate($request->from_date, $request->to_date, $request->table_id)) {

            if ($reservation->save())
                return response()->json($reservation);
            else
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not added'
                ], 500);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not added'
            ], 409);
        }
    }

    public function updateReservation(Request $request)
    {

        $this->validate($request, [
            'id' => 'required',
            'table_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'note' => 'required',
        ]);

        $user = Auth::user();

        try {
            $reservation = Reservation::findOrFail($request->id);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Reservation not found.'
            ], 403);
        }
        if ($user->id == $reservation->user_id) {
            if ($this->validateReservationUpdate($request->from_date, $request->to_date, $request->table_id, $request->id)) {
                $reservation->from_date = $request->from_date;
                $reservation->to_date = $request->to_date;
                $reservation->table_id = $request->table_id;
                $reservation->title = $request->title;
                $reservation->note = $request->note;
                if ($reservation->save())
                    return response()->json($reservation);
                else
                    return response()->json([
                        'success' => false,
                        'message' => 'Reservation not updated'
                    ], 500);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not updated - incorrect date'
                ], 409);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not updated - user not authorized'
            ], 401);
        }
    }
}
