<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\deptList;

class AccountController extends Controller
{
    //

    public function account()
    {
        return view('account.account_index', [
            'data' => User::all(),
            'deptList' => deptList::all()
        ]);
    }

    public function create_account(Request $request)
    {
        // dd($request->all());
        try{

            $request->validate([
                'name' => 'required',
                'email' => 'required|email:dns',
                'badge_no' => 'required',
                'role' => 'required',
                'deptList_id' => 'required'
            ]);

            User::create([
                'name' => $request->name,
                'badge_no' => $request->badge_no,
                'email' => $request->email,
                'role' => $request->role,
                'password' => bcrypt(12345),
                'dept_id' => $request->deptList_id
            ]);

            return response()->json(['message' => 'New Account Successfully added']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to Create New Account'], 500);
        }
    }

    public function delete_account($id)
    {
        try{
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['message' => 'New Account Successfully added']);

        } catch (\Exception $e){
            return response()->json(['error' => 'Failed to Create New Account'], 500);
        }
    }

    public function user_details($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'user' => $user,
                'dept' => $user->deptList,
                'hod' => $user->deptList->hod->email,
                'dept_list' => deptList::get()
            ]);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function update_account(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'badge_no' => 'required',
            'role' => 'required',
            'deptList_id' => 'required',
        ]);
        $findID = User::where('badge_no', $request->badge_no)->get()->first();
        $user = User::find($findID->id);
        if ($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->badge_no = $request->badge_no;
            $user->role = $request->role;
            $user->dept_id = $request->deptList_id;
            $user->save();

            return response()->json(['message' => 'User updated successfully']);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function menu()
    {
        return view('menu');
    }

    public function department()
    {
        return view('account.department_index', [
            'deptList' => deptList::all(),
            'users' => User::where('role', 'hod')->get()
        ]);
    }

    public function create_department(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'dept_name' => 'required',
                'dept_code' => 'required|unique:dept_lists,dept_code',
                'user_hod_id' => 'required'
            ]);

            deptList::create([
                'dept_name' => $request->dept_name,
                'dept_code' => $request->dept_code,
                'user_hod_id' => $request->user_hod_id
            ]);

            return response()->json(['message' => 'New Department Successfully added']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to Create New Department'], 500);
        }
    }

    public function getDepartmentUsers($id)
    {
        $users = User::where('dept_id', $id)->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found for this department'], 404);
        }

        return response()->json($users);
    }

    public function role()
    {
        $users = User::all();

        return view('account.role_index', [
            'adminCount' => $users->where('role', 'admin')->count(),
            'hodCount' => $users->where('role', 'hod')->count(),
            'regularCount' => $users->where('role', 'regular')->count(),
            'purchasingCount' => $users->where('role', 'purchasing')->count(),
            'deptList' => deptList::all()
        ]);
    }

    public function getRoleUsers($role)
    {
        $users = User::where('role', $role)->get();

        return response()->json($users);
    }

}
