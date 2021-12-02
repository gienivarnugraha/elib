<?php

namespace App\Http\Controllers\API;

use App\File;
use App\Http\Controllers\Controller;
use App\Manual;
use App\Order;
use App\Revision;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Searchable\Search;

class ManualController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return Manual::all();
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
      'type' => 'required',
      'aircraft_id' => 'required',
      'part_num' => 'required',
      'subject' => 'required',
      'lib_call' => 'required',
      'volume' => 'required',
      'vendor' => 'required',
      'caplist' => 'required',
      'collector' => 'required',
      'index_date' => 'required',
      'index' => 'required',
    ]);
    $this->authorize('isAdmin');

    $manual = new Manual($request->except(['aircraft', 'revision']));
    $manual->save();

    $revision = new Revision($request->only(['index_date', 'index']));
    $revision->is_closed = 1;
    $revision->index = strtoupper($request->index);
    $manual->revision()->save($revision);

    $file = new File();
    $file->type = 'manual';

    $revision->file()->save($file);

    return response()->json(['message' => 'Manual success added'], 200);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $manual = Manual::findOrFail($id);
    return $manual->latestOrder();
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
    $manual = Manual::findOrFail($id);

    $this->authorize('isAdmin');

    $manual->update($request->all());

    return response()->json(['entry' => $manual->getChanges(), 'message' => 'Manual Number: ' . $manual->part_num . ' success updated '], 200);
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
    $manual = Manual::findOrFail($id);
    $manual->delete();
    return response()->json(['entry' => ['id' => $manual->id], 'message' => 'Manual Number: ' . $manual->part_num . ' success deleted '], 200);
  }

  /**
   * Change the status of the manual
   *
   * @return \Illuminate\Http\Response
   */
  public function status(Request $request, $id)
  {
    $this->authorize('isAdmin');
    $order = Order::findOrFail($id);

    if ($order->is_closed == 1) {
      return response()->json(['message' => 'Manual is available!'], 400);
    } else {
      $order->is_closed = 1;
      $order->return_date = now();
      $order->update();
      return response()->json(['message' => 'Manual is returned!'], 200);
    }
  }


  /**
   * addRevision
   *
   * @param  mixed $request
   * @return void
   */
  public function addOrder(Request $request)
  {
    $this->validate($request, [
      'manual_id' => 'required',
      'user_id' => 'required',
      'send_date' => 'required',
    ]);
    $this->authorize('isAdmin');

    $manual = Manual::findOrFail($request->manual_id);

    if ($manual->latestOrder()->is_closed == 0) {
      return response()->json(['message' => 'Manual: ' . $manual->subject . ' is not available',], 400);
    } else {

      $order = new Order($request->only(['manual_id', 'user_id', 'send_date']));
      $manual->order()->save($order);

      return response()->json(['message' => 'New Order for: ' . $manual->subject, 'entry' => $manual], 200);
    }
  }

  /**
   * addRevision
   *
   * @param  mixed $request
   * @return void
   */
  public function addRevision(Request $request)
  {
    $this->validate($request, [
      'index' => 'required',
      'index_date' => 'required',
      'manual_id' => 'required',
      'changes' => 'required',
    ]);
    $this->authorize('isAdmin');

    $manual = Manual::findOrFail($request->manual_id);

    if ($manual) {
      $revision = new Revision($request->only(['index', 'index_date', 'changes']));
      $revision->is_closed = 1;
      $revision->index = strtoupper($request->index);
      $manual->revision()->save($revision);

      $file = new File();
      $file->type = 'manual';

      $revision->file()->save($file);
      return response()->json(['message' => 'New Revision Added with Index: ' . $revision->index, 'entry' => $manual], 200);
    }
  }

  /**
   * openFile by
   *
   * @param  mixed REVISION ID
   * @return void
   */
  public function openFile($id)
  {
    $revision = Revision::findOrFail($id);

    $file = storage_path('app/manuals/' . $revision->revisable_id . '/') . $revision->index . '_' . $revision->id . '.pdf';

    if (file_exists($file)) {
      $headers = [
        'Content-Type' => 'application/pdf'
      ];
      return response()->file($file, $headers);
    } else {
      return response()->json(["message" => "File not Found! "], 404);
    }
  }


  /**
   * addFile
   *
   * @param  mixed $request FILE ID
   * @return void
   */
  public function addFile(Request $request)
  {
    $this->authorize('isAdmin');
    $this->validate($request, [
      'file' => 'mimes:pdf',
    ]);

    $file =  File::findOrFail($request->id);


    $manual = Manual::findOrFail($file->revision->revisable_id);


    if ($request->file('file')->isValid()) {

      $file->type = "manual";
      $file->filename = $file->revision->index . '_' . $file->revision->id . "." . $request->file('file')->getClientOriginalExtension();
      $file->filepath = 'manuals/' . $manual->id;

      $request->file('file')->storeAs(
        $file->filepath,
        $file->filename
      );

      if ($file->save()) {
        return response()->json(['message' => 'File index: ' . $file->revision->index . ' success uploaded ', 'entry' => $file], 200);
      }
    }
  }


  public function search($search)
  {
    $search = (new Search())
      ->registerModel(Manual::class, 'subject')
      ->perform($search);
    return response()->json($search);
    //return new ManualCollection($search);

  }
}
