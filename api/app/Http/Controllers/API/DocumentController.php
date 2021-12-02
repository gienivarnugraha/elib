<?php

namespace App\Http\Controllers\API;

use App\Document;
use App\File;
use App\Http\Controllers\Controller;
use App\Notifications\DataNotification;
use App\Revision;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Spatie\Searchable\Search;

class DocumentController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return Document::latest()->get();
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
      'office' => 'required',
      'type' => 'required',
      'aircraft_id' => 'required',
      'assignee_id' => 'required',
      'subject' => 'required',
    ]);
    $date = Carbon::parse('now');
    $count = Document::all()->count();
    //$documents = []; //for response data entry

    if ($request->assignee_id === Auth::user()->id || Auth::user()->is_admin) {

      foreach ($request->subject as $key => $value) {
        $document = new Document($request->except(['assignee', 'aircraft', 'index', 'revision']));
        if ($document->office == 'DOA') {
          $document->no = $document->aircraft->type . '.AS.' . $document->type . '.' . $date->isoFormat('YY') . '.' . str_pad(($count + (int)$key + 1), 3, "00", STR_PAD_LEFT);
        } else {
          $document->no = $document->type . '-' . $document->aircraft->type . '-' . $date->isoFormat('YYYY') . '-' .  str_pad(($count + (int)$key + 1), 3, "00", STR_PAD_LEFT);
        }
        $document->subject = $value;

        $document->save();

        $revision = new Revision(['revisable_id' => $document->id, 'revisable_type' => 'App\Document']);

        $revision->save();

        $file = new File();
        $file->type = 'document';

        $revision->file()->save($file);


        //$documents[] = $document;
      }

      return response()->json(['message' => 'Document success added'], 200);
    } else {
      return response()->json(['message' => 'Cannot use others name to submit! unless you are admin! '], 403);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //return Document::findOrFail($id);\

    /* FOR TESTING PURPOSE */
    $document = Document::findOrFail($id);
    Notification::send(User::active()->get(), new DataNotification($document, 'updated', Auth::user()));
    return User::active()->get();
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
    $doc = Document::findOrFail($id);

    $this->authorize('docBelongsTo', $doc);

    if ($doc->update($request->only(['office', 'type', 'subject', 'aircraft_id', 'assignee_id']))) {
      return response()->json(['entry' => $doc->getChanges(), 'message' => 'Document Number: ' . $doc->no . ' success updated '], 200);
    } else {
      return response()->json(['message' => 'Document failed to update '], 400);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $doc = Document::findOrFail($id);
    $this->authorize('isAdmin');
    if ($doc->delete()) {
      return response()->json(['entry' => ['id' => $doc->id], 'message' => 'Document Number: ' . $doc->no . ' success deleted '], 200);
    } else {
      return response()->json(['message' => 'Failed to delete document'], 400);
    }
  }

  /**
   * Change the status of the document
   *
   * @return \Illuminate\Http\Response
   */
  public function status(Request $request, $id)
  {
    $this->authorize('isAdmin');
    $revision = Revision::findOrFail($id);
    $status = $request->is_closed == true ? 'closed' : 'open';

    if ($revision->is_closed == $request->is_closed) {
      return response()->json(['message' => 'Document is already ' . $status], 400);
    } elseif ($revision->file->filename != null) {
      $revision->is_closed = $request->is_closed;
      $revision->kpi_status = $this->getKpiStatus($revision->id);
      $revision->update();

      return response()->json(['message' => 'Document Number: ' . $revision->revisable->no . ' is ' . $status], 200);
    } else {
      return response()->json(['message' => 'Cant close document, you are not uploading anything yet! '], 400);
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
      'changes' => 'required',
      'document_id' => 'required',
    ]);

    $document = Document::findOrFail($request->document_id);

    $this->authorize('docBelongsTo', $document);

    $latestDoc = $document->revision()->latest()->first();

    if ($latestDoc->is_closed == true) {
      $revision = new Revision($request->only(['changes']));

      $getLastIndex = (int) ltrim($latestDoc->index, 'R');
      $revision->index = 'R' . $getLastIndex + 1;

      $document->revision()->save($revision);
      $file = new File();
      $file->type = 'document';

      $revision->file()->save($file);

      return response()->json(['message' => 'New Revision Added with Index: ' . $revision->index, 'entry' => $latestDoc], 200);
    } else {
      return response()->json(['message' => 'You have not closed Index: ' . $latestDoc->index . ' yet!'], 403);
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

    $file = storage_path('app/documents/' . $revision->revisable_id . '/') . $revision->index . '_' . $revision->id . '.pdf';

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
    $this->validate($request, [
      'file' => 'mimes:pdf',
    ]);

    $file =  File::findOrFail($request->id);


    $document = Document::findOrFail($file->revision->revisable_id);

    $this->authorize('docBelongsTo', $document);

    if ($request->file('file')->isValid()) {

      $file->type = "document";
      $file->filename = $file->revision->index . '_' . $file->revision->id . "." . $request->file('file')->getClientOriginalExtension();
      $file->filepath = 'documents/' . $document->id;

      $request->file('file')->storeAs(
        $file->filepath,
        $file->filename
      );

      if ($file->save()) {
        return response()->json(['message' => 'File index: ' . $file->revision->index . ' success uploaded ', 'entry' => $file], 200);
      }
    }
  }

  public function getKpiStatus($id)
  {
    $revision = Revision::findOrFail($id);

    $created = CarbonImmutable::create($revision->created_at);
    $nextWeekFromCreated = $created->addWeek();

    $updated = CarbonImmutable::create($revision->file->updated_at->toDateTimeString());
    if ($updated->lessThanOrEqualTo($nextWeekFromCreated)) {
      return 1;
    } else {
      return 0;
    }
  }

  public function countKpi()
  {
    $kpi = Revision::groupBy('kpi_status')->select('kpi_status', DB::raw('count(*) as Total'))->orderBy('kpi_status')->without('file')->get();
    $total = $kpi[0]['Total'] + $kpi[1]['Total'];

    $kpiTotal = array(
      'fullfilled ' . intval(($kpi[1]['Total'] / $total) * 100) . '%' => $kpi[1]['Total'],
      'pending ' . intval(($kpi[0]['Total'] / $total) * 100) . '%'  => $kpi[0]['Total']
    );
    $perMonthRaw = Revision::groupBy(['month', 'kpi_status'])->select(DB::raw('MONTH(created_at) AS month'), 'kpi_status', DB::raw('count(*) as total'))->orderBy('month', 'asc')->without('file')->get()->groupBy('month');


    $perMonth = [];
    foreach ($perMonthRaw as $key => $value) {
      $false = $value[0]['kpi_status'] == false ? $value[0]['total'] : 0;
      $true =  isset($value[1]['kpi_status']) == true ? $value[1]['total'] : $value[0]['total'];
      $total =  $false + $true;
      $perMonth[date("F", mktime(0, 0, 0, $key, 10))] = intval(($true / $total) * 100);
    }
    return ['total' => $kpiTotal, 'perMonth' => $perMonth];
  }

  public function filterByType(Request $request)
  {
    return Document::ofType($request->type)->get();
  }

  public function search($search)
  {
    $search = (new Search())
      ->registerModel(Document::class, 'subject')
      ->perform($search);
    return response()->json($search);
    //return new DocumentCollection($search);

  }
}
