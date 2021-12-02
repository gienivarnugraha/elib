<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
  public $user;

  public function __construct()
  {
    $user = Auth::user();
    if ($user) {
      $this->user = User::findOrFail($user->id);
    } else {
      return response()->json('Unauthorized', 401);
    }
  }

  public function index()
  {
    return $this->user->notifications->sortByDesc('created_at');
  }

  public function paginate($page)
  {
    return response()->json($this->user->id->notifications()->sortByDesc('created_at')->paginate(5, ['*'], 'page', $page));

    /*     $pages = $user->notifications()->orderBy('created_at', 'asc');
    $total = $pages->paginate()->total();
    if(($request->page*5) > $total){
      return response()->json(['notification' => $pages->paginate($total, ['*'], 'page', 1 ) ]);
    }
    else {
      return response()->json(['notification' =>  $pages->paginate(5*$page, ['*'], 'page', 1 )]);

    } */
    //return response()->json(['notification' =>   $user->notifications()->orderBy('created_at', 'asc')->paginate(5, ['*'], 'page', $page )]);
  }
}
