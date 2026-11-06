<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AdminCredential;
use App\Models\AdminProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use function Laravel\Prompts\search;

class AdminController extends Controller
{
    public function createAdmin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|min:3|max:20',
                'family' => 'required|string|min:5|max:50',
                'phone' => [
                    'required',
                    'string',
                    'regex:/^09[0-9]{9}$/'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@#$!%&*]).{8,}$/',
                ],
                'confirmPassword' => 'required|same:password',
                'role_id' => 'required|in:2,3'
            ],
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }

        $existingAdmin = AdminCredential::where('phone', $request->phone)->first();

        if ($existingAdmin) {
            return response()->json([
                "status" => false,
                "message" => "ادمین قبلا ثبت شده"
            ], 409);
        }
        $admin = new AdminCredential();
        $admin->phone = $request->phone;
        $admin->password = Hash::make($request->password);
        $admin->role_id = $request->role_id;
        $admin->save();

        $admin->adminProfile()->create([
            'name' => $request->name,
            'family' => $request->family,
        ]);

        return response()->json([
            'status' => true,
            'message' => "ادمین با موفقیت ثبت شد",
            'data' => $admin
        ], 200);
    }

    public function getAdmins(Request $request)
    {

        $query = AdminCredential::query();


        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $admin = $query->select([
            'id',
            'phone',
            'role_id',
            'is_active'
        ])->with('adminProfile')->paginate(5);

        if ($admin->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'ادمینی با شرایط مورد نظر پیدا نشد',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "اطلاعات با موفقیت دریافت شد",
            'data' => $admin->items(),
            'currentPage' => $admin->currentPage(),
            'lastPage' => $admin->lastPage(),
            'total' => $admin->total(),
        ], 200);
    }

    public function EditAdmin(Request $request, int $id)
    {
        $admin = AdminProfile::with('adminCredential')->find($id);

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => "ادمین پیدا نشد",
            ], 404);
        }

        $rule = [];

        if ($request->has('name')) {
            $rule['name'] = 'string|min:3|max:20';
        }

        if ($request->has('family')) {
            $rule['family'] = 'string|min:5|max:50';
        }

        if ($request->has('role_id')) {
            $rule['role_id'] = 'in:2,3';
        }

        $validator = Validator::make(
            $request->all(),
            $rule
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }

        $data = $validator->validate();
        $admin->fill($data);
        $admin->save();


        $admin->adminCredential->update([
            'role_id' => $data['role_id'] ?? $admin->adminCredential->role_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => "ادمین با موفقیت ویرایش شد",

        ], 200);
    }

    public function delete(int $id)
    {
        $admin = AdminCredential::find($id);

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => "ادمین پیدا نشد",
            ], 404);
        }

        $admin->is_active = false;
        $admin->save();
        return response()->json([
            'status' => true,
            'message' => 'ادمین با موفقیت غیرفعال شد',
        ], 200);
    }

    public function loginAdmin(Request $request)
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
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }

        $admin = AdminCredential::where('phone', $request->phone)->where('is_active', true)->first();

        if (!$admin) {
            return response()->json([
                "status" => false,
                "message" => "ادمین با این شماره یافت نشد"
            ], 404);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return response()->json([
                "status" => false,
                "message" => 'شماره موبایل یا رمز عبور اشتباه است.'
            ], 401);
        }
        $token = $admin->createToken('auth-token')->plainTextToken;
        return response()->json([
            "status" => true,
            'message' => 'ورود با موفقیت انجام شد.',
            'token' => $token
        ], 200);
    }
    public function searchAdmin(Request $request)
    {
        $query = AdminCredential::query();
        if ($request->filled('search')) {

            $search = $request->search;

            $query->whereHas('adminProfile', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')->orWhere('family', 'like', '%' . $search . '%');
            });
        }
        $admins = $query->select([
            'id',
            'phone',
            'role_id',
            'is_active'
        ])->with('adminProfile')->paginate(5);

        if ($admins->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'ادمینی با نام مورد نظر پیدا نشد',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'اطلاعات با موفقیت دریافت شد',
            'data' => $admins->items(),
            'currentPage' => $admins->currentPage(),
            'lastPage' => $admins->lastPage(),
            'total' => $admins->total(),
        ], 200);
    }
}
