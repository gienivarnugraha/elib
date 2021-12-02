<?php

namespace App\Http\Controllers;

use App\Events\UserAuth;
use App\Http\Controllers\Controller;
use App\Notifications\AuthNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

  public function login(Request $request)
  {
    //Validate fields
    $this->validate($request, ['email' => 'required|email|exists:users', 'password' => 'required']);
    //Attempt validation
    $credentials = $request->only(['email', 'password']);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      //err.response.data.errors
      return response([
        'errors' => ['password' => 'Wrong Password.'],
        'message' => 'These credentials do not match our records'
      ], 422);
    } else {
      $user->is_active = true;
      $user->saveWithoutEvents();

      $abilities =  ['*'];
      $token = $user->createToken('elib-token', $abilities)->plainTextToken;

      return response()->json(compact('token'));
    }
  }

  public function logout()
  {
    /**
     * Revoke all tokens
     */
    $user = User::findOrFail(Auth::user()->id);
    $user->is_active = true;
    $user->saveWithoutEvents();
    $user->tokens()->delete();

    return response()->json(['message' => 'succesfully logout']);
  }

  public function user()
  {
    return response()->json(Auth::user());
  }
}
