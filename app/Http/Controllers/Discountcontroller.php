<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Salon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Discountcontroller extends Controller
{

    public function addDiscount(Request $request, int $salonId)
    {

        $salon = Salon::find($salonId);
        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'سالن مورد نظر یافت نشد',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:4|max:60',
            'percent' => 'required|integer|min:5|max:100',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'اطلاعات وارد شده صحیح نیست.'
            ], 422);
        }

        $discount = new Discount();
        $discount->title = $request->title;
        $discount->percent = $request->percent;
        $discount->salon_id =$salonId;
        $discount->start_at = $request->start_at;
        $discount->end_at = $request->end_at;
        $discount->save();

        return response()->json([
            'status' => true,
            'message' => 'تخفیف با موفقیت ایجاد شد'
        ], 200);
    }


    public function getDiscountSalon(int $salonId)
    {
        $salon = Salon::find($salonId);
        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'ارایشگاه پیدا نشد'
            ], 404);
        }

        $now = Carbon::now();

        $discount = Discount::where('salon_id', $salonId)->where('is_active', true)->where('start_at', '<=', $now)->where('end_at', '>', $now)->get();
        return response()->json([
            'status' => true,
            'data' => $discount
        ], 200);
    }
}
