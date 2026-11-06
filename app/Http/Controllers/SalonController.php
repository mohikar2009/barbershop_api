<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Salon;
use App\Models\ImageGallery;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class SalonController extends Controller
{
    
    public function addSalon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:120',
            'address' => 'required|string|min:20|max:200',
            'description' => 'required|string|min:20|max:400',
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $salon = new Salon();
        $salon->name = $request->name;
        $salon->phone = $request->phone;
        $salon->description = $request->description;
        $salon->address = $request->address;
        $salon->is_active = $request->is_active ?? true;
        $salon->save();



        return response()->json([
            'status' => true,
            'message' => 'اطلاعات سالن با موفقیت ارسال شد',
            'data' => $salon
        ], 200);
    }

    public function addImageSalon(Request $request, int $id)
    {
        $salon = Salon::find($id);

        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'آرایشگاه مورد نظر پیدا نشد'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }




        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
            $path = $imageFile->storeAs('uploads/' . date('Y/m'), $imageName, 'public');
        } else {
            $path = 'uploads/default/img_salon.webp';
        }

        $url = Storage::disk('public')->url($path);
        $salon->salonImages()->create([
            'image' => $path,

        ]);

        return response()->json([
            'status' => true,
            'url' => $url,
            'message' => 'تصویر با موفقیت ارسال شد',
            'data' => $salon->load('salonImages')
        ], 200);
    }

    /////////////////////////
    public function getSalons()
    {
        $data = Salon::paginate(5);
        return response()->json([
            'status' => true,
            'data' => $data,
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            // 'data' => $data->load(['salonImages', 'barbers', 'salonServices.service'])

        ], 200);
    }
    /////////////////////////
    public function addBarber(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:20',
            'family' => 'required|string|min:3|max:50',
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'description' => 'required|string|min:20|max:400',
            'job_title' => 'required|string|min:5|max:120',
            'is_active' => 'boolean',
            'salon_id' => 'required|exists:salons,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "error" => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        $salon = Salon::find($request->input('salon_id'));
        $barber = new Barber();
        $barber->name = $request->name;
        $barber->family = $request->family;
        $barber->phone = $request->phone;
        $barber->description = $request->description;
        $barber->job_title = $request->job_title;
        $barber->is_active = $request->is_active ?? true;
        $salon->barbers()->save($barber);
        return response()->json([
            "status" => true,
            "message" => "ارایشگر با موفقیت ایجاد شد"
        ], 200);
    }

    public function getDataSalon()
    {
        $salon = Salon::with(['salonImages', 'barbers', 'salonServices'])->get();
        return response()->json([
            'status' => true,
            'data' => $salon

        ], 200);
    }

    public function getSalon(Request $request, int $categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json([
                'status' => true,
                'message' => 'همچین دسته بندی وجود ندارد'
            ], 404);
        }
        $search = $request->search;
        $query = Salon::whereHas('salonServices.service', function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })->with('salonImages')->when($search, function ($req) use ($search) {
            $req->where('name', 'like', '%' . $search . '%');
        });
        $salons = $query->get();
        return response()->json([
            'status' => true,
            'data' => $salons
        ]);
    }





    public function getSalonById(int $salonId)
    {
        $salon = Salon::with([
            'salonImages',
            'salonServices.service',
            'imageGalleries'
        ])->find($salonId);
        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'آرایشگاه پیدا نشد'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $salon
        ], 200);
    }



    public function imageGallerySalon(Request $request, int $salonId)
    {
        $salon = Salon::find($salonId);
        
        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'سالن مورد نظر یافت نشد',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:1048',
            // 'salon_id' => 'required|exists:salons,id',
            'is_active' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }
        if (!$request->hasFile('image')) {
            return response()->json([
                'status' => false,
                'message' => 'فایل یافت نشد'
            ], 404);
        }
        $imageFile = $request->file('image');
        $imageName = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
        $path = $imageFile->storeAs('uploads/' . date('Y/m'), $imageName, 'public');
        $url = Storage::disk('public')->url($path);
        $gallery = ImageGallery::create([
            'salon_id' => $salonId,
            'image' => $path,
            'is_active' => $request->boolean('is_active', true)
        ]);
        return response()->json([
            'status' => true,
            'message' => 'عکس گالری با موفقیت اضافه شد.',
            'url' => $url,
            'data' => $gallery
        ], 200);
    }
}
