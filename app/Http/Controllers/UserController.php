<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\OTPMail;
use App\Helper\JWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    function loginPage()
    {
        return view('pages.auth.login-page');
    }
    function registrationPage()
    {
        return view('pages.auth.registration-page');
    }
    function sendOtpPage()
    {
        return view('pages.auth.send-otp-page');
    }
    function verifyOtpPage()
    {
        return view('pages.auth.verify-otp-page');
    }
    function resetPassPage()
    {
        return view('pages.auth.reset-pass-page');
    }
    function profilePage()
    {
        return view('pages.dashboard.profile-page');
    }



    public function UserRegistration(Request $request)
    {
        try {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password'),

            ]);

            return response()->json([
                'status' => "success",
                'message' => "user created successfully"
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'user not created',
            ], 200);
        }
    }


    public function UserLogin(Request $request)
    {
        $count = User::where('email', '=', $request->input('email'))
            ->where('password', '=', $request->input('password'))
            ->select('id')->first();

        if ($count !== null) {
            $token = JWTToken::createToken($request->input('email'), $count->id);
            return response()->json([
                'status' => "success",
                'message' => "login successfully",

            ], 200)->cookie('token', $token, 60 * 60 * 24);
        } else {
            return response()->json([
                'status' => "failed",
                'message' => "unauthorized"
            ]);
        }
    }


    function sendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();

        if ($count == 1) {
            // OTP Email Address
            Mail::to($email)->send(new OTPMail($otp));
            // OTO Code Table Update
            User::where('email', '=', $email)->update(['otp' => $otp]);

            return response()->json([
                'status' => 'success',
                'message' => '4 Digit OTP Code has been send to your email !'
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'email not found'
            ]);
        }

    }


    function verifyOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)->where('otp', '=', $otp)->count();

        if ($count == 1) {

            $token = JWTToken::CreateTokenForSetPassword($email);
            User::where('email', '=', $email)->update(['otp' => '0']);
            return response()->json([
                'status' => "success",
                'message' => "OTP verified successfully",

            ], 200)->cookie('token', $token, 60 * 60);

        } else {
            return response()->json([
                'status' => "failed",
                'message' => 'invalid OTP',
            ]);
        }


    }

    function resetPass(Request $request)
    {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->update(['password' => $password]);
            return response()->json([
                'status' => "success",
                'message' => "password reset successfully",
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'user not found',
            ]);
        }
    }


    function logout(Request $request)
    {
        return redirect('/userLogin')->cookie('token', '', -1);
    }

    function userProfile(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', '=', $email)->first();
        return response()->json([
            'status' => "success",
            'message' => 'Request successful',
            'data' => $user
        ], 200);
    }

    function updateProfile(Request $request)
    {

        try {
            $email = $request->header('email');
            $firstName = $request->input('firstName');
            $lastName = $request->input('lastName');
            $mobile = $request->input('mobile');
            $password = $request->input('password');
            User::where('email', '=', $email)->update([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobile' => $mobile,
                'password' => $password
            ]);
            return response()->json([
                'status' => "success",
                'message' => "profile updated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'user not found',
            ]);
        }
    }

}
