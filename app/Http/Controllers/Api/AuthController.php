<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Rules\InstituteEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'required|string|max:15',
            'role' => ['required', Rule::in(['bus_coordinator', 'driver', 'faculty', 'student'])],

            // Student specific
            'roll_number' => 'required_if:role,student|string|unique:users,roll_number',
            'course' => ['required_if:role,student', Rule::in(['B.Tech', 'M.Tech', 'MBA', 'BBA', 'MCA'])],
            'branch_id' => 'required_if:role,student,faculty|exists:branches,id',
            'year' => 'required_if:role,student|string',
            'date_of_birth' => 'required_if:role,student|date',

            // Faculty specific
            'employee_id' => 'required_if:role,faculty|string|unique:users,employee_id',
            'designation_id' => 'required_if:role,faculty|exists:designations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['password', 'password_confirmation']);
        $data['name'] = $request->first_name . ' ' . $request->last_name;
        $data['password'] = Hash::make($request->password);
        $data['is_verified'] = false;

        $user = User::create($data);

        // Generate and send OTP
        $otp = $user->generateOtp();
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

        // Create verification request
        VerificationRequest::create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Registration successful. Please verify your email with the OTP sent to your inbox.',
            'user' => $user
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->verifyOtp($request->otp)) {
            return response()->json(['message' => 'Email verified successfully. You can login once your account is verified by the coordinator/faculty.']);
        }

        return response()->json(['message' => 'Invalid or expired OTP'], 422);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'Your account is pending verification. Only verified users can login.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load(['branch', 'designation', 'department']));
    }
}
