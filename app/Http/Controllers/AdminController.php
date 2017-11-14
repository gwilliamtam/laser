<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;

class AdminController extends Controller
{

    public function listUsers(Request $request)
    {
        $usersQuery = User::orderBy('users.email');
        if($usersQuery->count()>0){
            $users = $usersQuery->get();
        }else{
            $users = null;
        }

        return view('admin.users', [
            'users'=>$users
        ]);
    }

    public function viewUser(Request $request)
    {
        if(!empty($request->userId)){

            $userQuery = User::where('id','=', $request->userId);
            if($userQuery->count()>0){
                $user = $userQuery->first();

                return view('admin.user-view',[
                   "user" => $user
                ]);
            }

        }

        return redirect()->route('listUers');
    }
}
