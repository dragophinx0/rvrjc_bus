<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['branch', 'designation', 'department']);

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        return response()->json($query->paginate(15));
    }

    public function show(User $user)
    {
        return response()->json($user->load(['branch', 'designation', 'department', 'currentBus']));
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->except(['password', 'role', 'is_verified']));
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot delete admin user'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function referenceData()
    {
        return response()->json([
            'branches' => \App\Models\Branch::all(),
            'designations' => \App\Models\Designation::all()
        ]);
    }
}
