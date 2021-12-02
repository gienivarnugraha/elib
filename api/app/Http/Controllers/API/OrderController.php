<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\manual;
use App\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return Order::orderBy('created_at', 'desc')->get();
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
      'manual_id' => 'required|max:50',
      'user_id' => 'required|max:50',
    ]);

    $order = new Order($request->toArray());
    $order->id = uniqid("id_");

    $aircraft = Manual::findOrFail($order->manual_id);
    if ($aircraft->is_available == true) {
      $aircraft->is_available = false;
      $aircraft->save();

      $order->is_closed = false;
      $order->manual()->associate($aircraft);
      $order->save();

      return response()->json(['message' => 'order created'], 201);
    } else {
      return response()->json(['status' => false, 'message' => 'document is not available']);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Order  $order
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    return Order::findOrFail($id);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Order  $order
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $order = Order::findOrFail($id);
    if ($order->is_closed == false) {
      $order->return_date = date("Y:m:d", strtotime(request('return_date')));
      $order->is_closed = true;
      $order->save();

      $aircraft = Manual::findOrFail($order->manual_id);
      $aircraft->is_available = true;
      $aircraft->save();

      return response()->json(['status' => true, 'message' => 'order for ' . $aircraft->type . ' updated'], 201);
    } else {
      return response()->json(['status' => false, 'message' => 'document is not returned! ']);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Order  $order
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $order = Order::findOrFail($id);
    $order->user()->dissociate();
    $order->manual()->dissociate();

    $order->delete();

    return response()->json(['status' => true, 'message' => 'order deleted'], 200);
  }
}
