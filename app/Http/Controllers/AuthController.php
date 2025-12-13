<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\JustNotify;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $verifyOTP = rand(100000, 999999);
        $otp_expires_at = Carbon::now()->addMinutes(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $verifyOTP,
            'otp_expires_at' => $otp_expires_at,
        ]);

        $user->notify(new UserNotification($verifyOTP, 'You register Google!'));
        return response()->json([
            'message' => 'User Registration successful, please verify OTP sent to your email.',
            'user' => $user,
        ]);                                                                              
    }

    public function resendOTP(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $verifyOTP = rand(100000, 999999);
        $otp_expires_at = Carbon::now()->addMinutes(10);

        $user->otp = $verifyOTP;
        $user->otp_expires_at = $otp_expires_at;
        $user->save();

        $user->notify(new UserNotification($verifyOTP, 'Resend OTP for Google verification!'));
        return response()->json([
            'message' => 'OTP resent, please verify OTP sent to your email.',
            'user' => $user,
        ]);
    }

    public function verifyOTP(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
            'otp' => 'required|integer',
        ]);

        $user = User::where('email', $request->email)->first();

        if($user->otp != $request->otp){
            $user->notify(new JustNotify('Invalid OTP attempt for email verification!'));
            return response()->json([
                'message' => 'Invalid OTP!',
            ]);
        }

        if(Carbon::now()->greaterThan($user->otp_expires_at)){
            $user->notify(new JustNotify('Your OTP has expired! Please request a new one.'));
            return response()->json([
                'message' => 'OTP expired!',
            ]);
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        $user->notify(new JustNotify('Your email has been verified successfully!'));
        return response()->json([
            'message' => 'OTP verified successfully!',
            'user' => $user,
        ]);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user || !hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'Invalid email or password!',
            ]);
        }

        if(!$user->email_verified_at){
            $user->notify(new JustNotify('Unverified email login attempt!'));
            return response()->json([
                'message' => 'User is not verified! Please verify your email.',
            ]);
        }

        $token = JWTAuth::attempt($request->only('email', 'password'));

        $user->notify(new JustNotify('Login successful!'));
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function forgotPassword(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $resetOTP = rand(100000, 999999);
        $otp_expires_at = Carbon::now()->addMinutes(10);

        $user->otp = $resetOTP;
        $user->otp_expires_at = $otp_expires_at;
        $user->save();

        $user->notify(new UserNotification($resetOTP, 'Password Reset OTP for Google!'));
        return response()->json([
            'message' => 'Password reset OTP sent to your email.',
            'user' => $user,
        ]);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
            'otp' => 'required|integer',
            'new_password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if($user->otp != $request->otp){
            $user->notify(new JustNotify('Invalid OTP attempt for password reset!'));
            return response()->json([
                'message' => 'Invalid OTP!',
            ]);
        }

        if(Carbon::now()->greaterThan($user->otp_expires_at)){
            $user->notify(new JustNotify('Your OTP for password reset has expired! Please request a new one.'));
            return response()->json([
                'message' => 'OTP expired!',
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $user->notify(new JustNotify('Your password has been reset successfully!'));
        return response()->json([
            'message' => 'Password reset successful!',
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request){
        $user = User::find(Auth::user()->id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if($request->name){
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
            $user->name = $request->name;
        }

        if($request->change_password){
            $request->validate([
                'current_password' => 'required|string|min:6',
                'new_password' => 'required|string|min:6',
            ]);

            if(!Hash::check($request->current_password, $user->password)){
                $user->notify(new JustNotify('Incorrect current password attempt during profile update!'));
                return response()->json([
                    'message' => 'Current password is incorrect!',
                ]);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        $user->notify(new JustNotify('Profile updated successfully!'));
        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function delete(Request $request)
    {
        $user = User::find(Auth::user()->id);
        JWTAuth::invalidate(JWTAuth::getToken());

        $user->notify(new JustNotify('Your account has been deleted successfully!'));
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
