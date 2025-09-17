<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\deptList;
use App\Models\PartStock;
use App\Models\PrLogHistory;

class AccountController extends Controller
{
    //

    public function account()
    {
        return view('account.account_index', [
            'data' => User::all()->map(function ($user) {
                $user->status = $user->status ?? 'Active'; // Dummy status
                return $user;
            }),
            'deptList' => deptList::all()
        ]);
    }

    public function create_account(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email:dns|unique:users,email',
                'badge_no' => 'required|unique:users,badge_no',
                'role' => 'required|in:hod,regular,purchasing,security',
                'deptList_id' => 'required|exists:dept_lists,id',
                'status' => 'required' // Validate status
            ]);

            User::create([
                'name' => $request->name,
                'badge_no' => $request->badge_no,
                'email' => $request->email,
                'role' => $request->role,
                'password' => bcrypt('12345'),
                'dept_id' => $request->deptList_id,
                'status' => $request->status // Store status (will be ignored until DB is updated)
            ]);

            return response()->json(['message' => 'New Account Successfully added']);
        } catch (\Exception $e) {
            \Log::error('Create account error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_account($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Cannot delete admin user'], 403);
            }
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Delete account error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete user'], 500);
        }
    }

    public function user_details($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'user' => array_merge($user->toArray(), ['status' => $user->status ?? 'Active']),
                'dept' => $user->deptList,
                'hod' => $user->deptList && $user->deptList->hod ? $user->deptList->hod->email : null,
                'dept_list' => deptList::all()
            ]);
        }
        \Log::error('User not found: ' . $id);
        return response()->json(['error' => 'User not found'], 404);
    }

    public function update_account(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:users,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $request->id,
                'badge_no' => 'required|unique:users,badge_no,' . $request->id,
                'role' => 'required|in:hod,regular,purchasing,security',
                'deptList_id' => 'required|exists:dept_lists,id',
                'status' => 'required'
            ]);

            $user = User::findOrFail($request->id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'badge_no' => $request->badge_no,
                'role' => $request->role,
                'dept_id' => $request->deptList_id,
                'status' => $request->status // Store status (will be ignored until DB is updated)
            ]);

            return response()->json(['message' => 'User updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Update account error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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

    public function department_details($id)
    {
        $dept = deptList::find($id);

        if ($dept) {
            return response()->json([
                'dept' => $dept,
                'hod' => $dept->hod ? $dept->hod->email : null
            ]);
        }
        return response()->json(['error' => 'Department not found'], 404);
    }

    public function update_department(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:dept_lists,id',
                'dept_name' => 'required|string|max:255',
                'dept_code' => 'required|string|max:50|unique:dept_lists,dept_code,' . $request->id,
                'user_hod_id' => 'required|exists:users,id'
            ]);

            $dept = deptList::findOrFail($request->id);
            $dept->update([
                'dept_name' => $request->dept_name,
                'dept_code' => $request->dept_code,
                'user_hod_id' => $request->user_hod_id
            ]);

            return response()->json(['message' => 'Department updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete_department($id)
    {
        try {
            $dept = deptList::findOrFail($id);
            
            // Check if department has associated users
            if (User::where('dept_id', $id)->exists()) {
                return response()->json(['error' => 'Cannot delete department with associated users'], 400);
            }

            $dept->delete();
            return response()->json(['message' => 'Department deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete department'], 500);
        }
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

    public function user_log()
    {

        // return PrLogHistory::orderBy('created_at', 'desc')->get();
        return view('parts.user_log', [
            'logs' => PrLogHistory::orderBy('created_at', 'asc')->get(),
            // 'partLogs' => PartStock::orderBy('created_at', 'desc')->get()
        ]);
    }

}
