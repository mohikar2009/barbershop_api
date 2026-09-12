<?php

namespace App\Http\Controllers;

use App\Models\UserCredential;
use App\Models\Profile;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:20',
            'family' => 'required|string|min:3|max:50',
            'age' => 'nullable|integer|min:14|max:95',
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'email' => 'nullable|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@#$!%&*]).{8,}$/',
            ],
            'confirmPassword' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $existingUser = UserCredential::where("phone", $request->phone)->where('is_deleted', false)->first();
        if ($existingUser) {
            return response()->json([
                "status" => false,
                "message" => "شما قبلا ثبت نام کردید،لطفا وارد حساب کاربری شوید."
            ], 409);
        }
        $user = new UserCredential();
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();
        $user->profile()->create([
            'name' => $request->name,
            'family' => $request->family,
            'age' => $request->age,
            'email' => $request->email
        ]);
        return response()->json([
            "status" => true,
            "message" => "ثبت نام با موفقیت انجام شد."
        ], 200);
    }



    public function getDataCustomer()
    {
        $profile = Profile::all();
        return response()->json([
            'data' => $profile
        ]);
    }




    public function loginCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',

            ],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $user = UserCredential::where("phone", $request->phone)->where('is_deleted', false)->first();
        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "کاربر یافت نشد"
            ], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "status" => false,
                "message" => 'شماره موبایل یا رمز عبور اشتباه است.'
            ], 401);
        }
        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            "status" => true,
            'message' => 'ورود با موفقیت انجام شد.',
            'token' => $token
        ], 200);
    }



    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/'
            ]
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                'message' => 'اطلاعات نامعتبر هست',
            ], 402);
        }
        $user = userCredential::where('phone', $request->phone)->where('is_deleted', false)->first();
        if (!$user) {
            return response()->json([

                "status" => false,
                'message' => 'کاربر با این شماره یافت نشد',

            ], 404);
        }

        $code = rand(100000, 999999);
        OtpCode::create([
            'code' => $code,
            'expires_at' => now()->addMinutes(2),
            'user_credentials_id' => $user->id

        ]);
        return response()->json([
            "status" => true,
            'message' => 'کد با موفقیت ارسال شد',
            "otp" => $code,

        ], 200);
    }


    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'size:6'

            ]
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }

        $otp = OtpCode::where('code', $request->code)->latest()->first();
        if (!$otp) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        if ($otp->used_at !== Null) {
            return response()->json([
                'status' => false,
                'message' => 'این کد قبلاً استفاده شده است.'
            ], 422);
        }
        if ($otp->expires_at < now()) {
            return response()->json([
                'status' => false,
                'message' => 'کد منقضی شده است.'
            ], 422);
        }
        $user = $otp->userCredential;
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'کاربر مربوط به این کد پیدا نشد.'
            ], 404);
        }
        $otp->update([
            'used_at' => now()
        ]);
        return response()->json([
            'status' => true,
            'message' => 'کد با موفقیت تأیید شد.',
            'phone' => $user->phone
        ], 200);
    }
    public function changePassword(Request $request)
    {
        $customer = UserCredential::where('phone',$request->userPhone)->first();
       
        if (!$customer) {
            return response()->json([
                "status" => false,
                "message" => "کاربر پیدا نشد"
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@#$!%&*]).{8,}$/',
            ],
            'confirmPassword' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $customer->update(['password'=>Hash::make($request->password)]);
        return response()->json([
            'status' => true,
              'message' => 'رمز عبور با موفقیت تغییر کرد.'
            
        ], 200);

    }
}
