<?php

namespace App\Http\Controllers\API;

use App\Events\UserAuth;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return User::all();
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    return User::findOrFail($id);
  }


  public function store(Request $request)
  {
    $this->authorize('isAdmin');
    //Validate fields
    $this->validate($request, [
      'email' => 'required|email|max:50|unique:users,email',
      'name' => 'required|max:50',
      'nik' => 'required|max:10|unique:users,nik',
      'org_code' => 'required|max:10',
      'dept' => 'required|max:20',
    ]);

    $user = new User($request->toArray());
    $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password
    $user->org_code = strtoupper($user->org_code);
    $user->save();
    return response()->json(['message' => 'New user Added with name: ' . $user->name, 'entry' => $user], 200);
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
    $user = User::findOrFail($id);
    $this->authorize('currentUser', $user);
    $this->validate($request, [
      'name' => 'required|max:50',
      'nik' => 'required|max:10',
      'org_code' => 'required|max:10',
      'dept' => 'required|max:50',
      'password_current' => 'nullable|password:sanctum',
      'password' => ['required' => Rule::requiredIf($request->password_current != null), 'confirmed'],
    ]);
    if ($request->password_current !== null) {
      if (Hash::check($request->password, $user->password)) {
        return response()->json(['entry' => $user, 'message' => 'New password is same with old Password!'], 400);
      } else {
        $user->password = Hash::make($request->password);
        $user->saveWithoutEvents($request->except('password_confirmation', 'password_current'));
        return response()->json(['entry' => $user, 'message' => 'Password Updated'], 200);
      }
    } else {
      $user->saveWithoutEvents($request->except('password_confirmation', 'password_current', 'password'));
      return response()->json(['entry' => $user, 'message' => 'Data Updated'], 200);
    }
  }


  /**
   * Change the status of the document
   *
   * @return \Illuminate\Http\Response
   */
  public function status(Request $request, $id)
  {
    $user = User::findOrFail($id);
    $user->is_active = $request->is_active;
    $user->saveWithoutEvents();
    broadcast(new UserAuth($user))->toOthers();
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

    $user = User::findOrFail($id);

    $user->delete();
    return response()->json(['message' => 'user deleted', 'id' => $user->id], 200);
  }
}
