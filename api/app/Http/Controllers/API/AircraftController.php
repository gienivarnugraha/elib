<?php

namespace App\Http\Controllers\API;

use App\Aircraft;
use App\Http\Controllers\Controller;
use App\Notifications\DataNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AircraftController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return Aircraft::orderBy('type', 'asc')->get();
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->authorize('isAdmin');
    $this->validate($request, [
      'type' => 'required',
      'serial_num' => 'required|unique:aircraft',
      'reg_code' => 'required|unique:aircraft',
      'effectivity' => 'required',
      'manuf_date' => 'date_format:Y',
    ]);
    $aircraft = new Aircraft($request->toArray());
    $aircraft->save();
    return response()->json(['message' => 'New Aircraft Added with Reg: ' . $aircraft->reg_code, 'entry' => $aircraft], 200);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    return Aircraft::findOrFail($id);
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
    $this->authorize('isAdmin');
    $aircraft = Aircraft::find($id);
    $aircraft->update($request->except('document', 'manual'));
    return response()->json(['status' => true, 'message' => 'Aircraft Reg: ' . $aircraft->reg_code . ' success updated ', 'entry' => $aircraft], 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $this->authorize('isAdmin');
    $aircraft = Aircraft::findOrFail($id);
    $aircraft->delete();
    Notification::send(User::active()->get(), new DataNotification($aircraft, 'uploaded', Auth::user()));
    return response()->json(['status' => true, 'message' => 'Aircraft Reg: ' . $aircraft->reg_code . ' success deleted ', 'id' => $id], 200);
  }
}
