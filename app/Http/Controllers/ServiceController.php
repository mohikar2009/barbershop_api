<?php

namespace App\Http\Controllers;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Category;
use App\Models\Barber;
use App\Models\SalonService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function addService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:60',
            'description' => 'required|string|min:10|max:300',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $category = Category::find($request->input('category_id'));

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'دسته بندی پیدا نشد'
            ], 404);
        }

        $service = new Service();
        $service->name = $request->name;
        $service->description = $request->description;
        $service->is_active = $request->is_active ?? true;
        $category->service()->save($service);
        return response()->json([
            'status' => true,
            'message' => "خدمت با موفقیت ثبت شد",
            'data' => $service
        ], 200);
    }



    public function editService(Request $request, int $id)
    {
        $service = Service::with('category')->find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => "سرویس پیدا نشد",
            ], 404);
        }

        $rule = [];

        if ($request->has('name')) {
            $rule['name'] = 'string|min:3|max:60';
        }

        if ($request->has('description')) {
            $rule['description'] = 'string|min:10|max:300';
        }

        if ($request->has('category_id')) {
            $rule['category_id'] = 'exists:categories,id';
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
        $service->fill($data);
        $service->save();
        return response()->json([
            'status' => true,
            'message' => "سرویس با موفقیت ویرایش شد",

        ], 200);
    }

    public function dataService()
    {

        $data = Service::with('category')->paginate(5);
        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }

    public function dataServiceAll()
    {
        $data = Service::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }



    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:40',
            'is_active' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $category = new Category();
        $category->name = $request->name;
        $category->is_active = $request->is_active ?? true;
        $category->save();
        return response()->json([
            'status' => true,
            'message' => "دسته بندی با موفقیت ثبت شد",
        ], 200);
    }

    public function getCategory()
    {
        $category = Category::where('is_active', true)->get()->select(['name', 'id']);
        return response()->json([
            'status' => true,
            'data' => $category

        ], 200);
    }

   



    public function barberService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $barber = Barber::find($request->input('barber_id'));
        $barber->services()->syncWithoutDetaching([
            $request->service_id
        ]);
        return response()->json([
            'status' => true,
            'message' => 'خدمت با موفقیت به آرایشگر اضافه شد.',
            'data' => $barber->load('services')
        ], 200);
    }

   







    public function salonService(Request $request, int $salon_id)
    {

        $salon = Salon::find($salon_id);

        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'آرایشگاه پیدا نشد.'
            ], 404);
        }


        $validator = Validator::make($request->all(), [
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|integer|distinct|exists:services,id',
            'services.*.price' => 'required|integer|min:100000|max:100000000',
            'services.*.duration' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'errors' => $validator->errors()
            ], 422);
        }


        $salonServices = collect($request->services)->map(
            function ($service) use ($salon_id) {
                return [
                    'salon_id' => $salon_id,
                    'service_id' => $service['service_id'],
                    'price' => $service['price'],
                    'duration' => $service['duration'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        );


        SalonService::insert($salonServices->toArray());

        return response()->json([
            'status' => true,
            'message' => 'خدمات با موفقیت به آرایشگاه اضافه شدند.',
            'data' => $salonServices->values()
        ], 201);
    }



    public function deletService(int $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => "سرویس  پیدا نشد",
            ], 404);
        }

        $service->is_active = false;
        $service->save();
        return response()->json([
            'status' => true,
            'message' => 'سرویس با موفقیت غیرفعال شد',
        ], 200);
    }
}
