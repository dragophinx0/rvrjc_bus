<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function pending(Request $request)
    {
        $user = $request->user();

        $query = VerificationRequest::with('user.branch', 'user.designation')->where('status', 'pending');

        if ($user->isAdmin()) {
            // Admin verifies Bus Coordinators
            $query->whereHas('user', function ($q) {
                $q->where('role', 'bus_coordinator');
            });
        } elseif ($user->isCoordinator()) {
            // Coordinators verify drivers and faculty
            $query->whereHas('user', function ($q) {
                $q->whereIn('role', ['driver', 'faculty']);
            });
        } elseif ($user->isFaculty()) {
            // Faculty verifies students in their department
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('role', 'student')
                    ->where('branch_id', $user->department_id);
            });
        } else {
            return response()->json(['message' => 'You do not have permission to view pending verifications.'], 403);
        }

        return response()->json($query->paginate(15));
    }

    public function approve(Request $request, User $user)
    {
        $authenticator = $request->user();

        if (!$authenticator->canVerify($user)) {
            return response()->json(['message' => 'You do not have permission to verify this user.'], 403);
        }

        $user->update([
            'is_verified' => true,
            'verified_by' => $authenticator->id
        ]);

        VerificationRequest::where('user_id', $user->id)->update([
            'status' => 'approved',
            'verified_by' => $authenticator->id
        ]);

        return response()->json(['message' => 'User verified successfully.']);
    }

    public function reject(Request $request, User $user)
    {
        $authenticator = $request->user();

        if (!$authenticator->canVerify($user)) {
            return response()->json(['message' => 'You do not have permission to reject this user.'], 403);
        }

        $request->validate(['reason' => 'required|string']);

        VerificationRequest::where('user_id', $user->id)->update([
            'status' => 'rejected',
            'verified_by' => $authenticator->id,
            'rejection_reason' => $request->reason
        ]);

        return response()->json(['message' => 'User verification rejected.']);
    }

    public function status(Request $request)
    {
        $request->user()->load('verificationRequest');
        return response()->json([
            'is_verified' => $request->user()->is_verified,
            'verification_request' => $request->user()->verificationRequest
        ]);
    }
}
