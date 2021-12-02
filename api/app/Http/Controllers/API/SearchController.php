<?php

namespace App\Http\Controllers\API;

use App\Aircraft;
use App\Document;
use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentCollection;
use App\Http\Resources\SearchCollection;
use App\manual;
use Illuminate\Http\Request;
use Spatie\Searchable\ModelSearchAspect;
use Spatie\Searchable\Search;

class SearchController extends Controller
{

  /**
   * search records in database and display  results
   * @param  Request $request [description]
   * @return view      [description]
   */
  public function search(Request $request)
  {

    $searchterm = $request->input('query');

    $searchResults = (new Search())
      ->registerModel(\App\Document::class, 'subject')
      ->registerModel(\App\Aircraft::class, 'type', 'owner', 'reg_code', 'serial_num')
      ->registerModel(\App\User::class, 'name', 'nik')
      ->registerModel(\App\Order::class, 'user_id')
      ->perform($searchterm);

    if ($searchResults->count() > 0) {
      //return new SearchCollection($searchResults);
      return response()->json($searchResults, 200);
    } else {
      return response()->json(['status' => true, 'message' => 'Nothing found with keyword ' . $searchterm], 200);
    }
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function list(Request $request)
  {
    $sortBy = request()->has('sortBy') ? request()->get('sortBy') : 'id';
    $sort = request()->has('sort') ? request()->get('sort') : 'asc';
    $perPage = request()->has('perPage') ? request()->get('perPage') : 25;

    if (!empty(request()->get('type'))) {
      //Document::ignoreRequest('perPage')->filter(['filter' => request()->get('filter')])->with(['users:name,email,nik', 'aircrafts:type,serial_num,reg_code,effectivity,owner'])->orderBy($sortBy, $sort)->paginate($perPage, ['*'], 'page');
      Manual::ignoreRequest(['perPage'])->filter()->paginate(request()->get('perPage'), ['*'], 'page');
    } else {
      return Manual::orderBy($sortBy, $sort)->paginate($perPage, ['*'], 'page');
    }
  }
}
