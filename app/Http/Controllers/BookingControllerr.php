<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SalonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Discount;

class BookingControllerr extends Controller
{



    public function infoBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|exists:salons,id',
            'service_id' => 'required|exists:services,id',
            'profile_id' => 'required|exists:profiles,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'error' => $validator->errors()

            ], 422);
        }

        $salonService = SalonService::where('salon_id', $request->salon_id)->where('service_id', $request->service_id)->where('is_active', true)->first();

        if (!$salonService) {
            return response()->json([
                'status' => false,
                'message' => 'این خدمت برای سالن انتخاب‌شده فعال نیست.',
            ], 422);
        }

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->booking_date . ' ' . $request->start_time
        );

        $endTime = $startTime->copy()->addMinutes($salonService->duration);

        if ($startTime->format('H:i') < '08:00' || !$startTime->isSameDay($endTime) ||  $endTime->format('H:i') > '21:00') {
            return response()->json([
                'status' => false,
                'message' => 'زمان انتخاب‌شده خارج از ساعت کاری سالن است.'
            ], 422);
        }



        if ($startTime->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'امکان رزرو نوبت در گذشته وجود ندارد.'
            ], 422);
        }

        $conflict = Booking::where('salon_id', $request->salon_id)->where('booking_date', $request->booking_date)->where(
            'status',
            '!=',
            'cancelled'
        )->where('start_time', '<', $endTime->format('H:i:s'))->where('end_time', '>', $startTime->format('H:i:s'))->exists();

        if ($conflict) {
            return response()->json([
                'status' => false,
                'message' => 'این زمان قبلاً رزرو شده است. لطفاً زمان دیگری انتخاب کنید.'
            ], 422);
        }


        $now = Carbon::now();
        $originalPrice = $salonService->price;
        $discount = Discount::where('salon_id', $request->salon_id)
            ->where('is_active', true)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)
            ->first();

        $discountPercent = $discount ? $discount->percent : 0;
        $discountAmount = ($originalPrice * $discountPercent) / 100;

        $finalPrice = $originalPrice - $discountAmount;

        return response()->json([
            'status' => true,
            'finalPrice' => $finalPrice,
            'seviceInfo' => $salonService,
            'discountPercent' => $discountPercent,
            'discountAmount' => $discountAmount,

        ], 200);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|exists:salons,id',
            'service_id' => 'required|exists:services,id',
            'profile_id' => 'required|exists:profiles,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'error' => $validator->errors()

            ], 422);
        }

        $salonService = SalonService::where('salon_id', $request->salon_id)->where('service_id', $request->service_id)->where('is_active', true)->first();

        if (!$salonService) {
            return response()->json([
                'status' => false,
                'message' => 'این خدمت برای سالن انتخاب‌شده فعال نیست.',
            ], 422);
        }

        $startTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->booking_date . ' ' . $request->start_time
        );

        $endTime = $startTime->copy()->addMinutes($salonService->duration);

        if ($startTime->format('H:i') < '08:00' || !$startTime->isSameDay($endTime) ||  $endTime->format('H:i') > '21:00') {
            return response()->json([
                'status' => false,
                'message' => 'زمان انتخاب‌شده خارج از ساعت کاری سالن است.'
            ], 422);
        }



        if ($startTime->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'امکان رزرو نوبت در گذشته وجود ندارد.'
            ], 422);
        }

        $conflict = Booking::where('salon_id', $request->salon_id)->where('booking_date', $request->booking_date)->where(
            'status',
            '!=',
            'cancelled'
        )->where('start_time', '<', $endTime->format('H:i:s'))->where('end_time', '>', $startTime->format('H:i:s'))->exists();

        if ($conflict) {
            return response()->json([
                'status' => false,
                'message' => 'این زمان قبلاً رزرو شده است. لطفاً زمان دیگری انتخاب کنید.'
            ], 422);
        }


        $now = Carbon::now();
        $originalPrice = $salonService->price;
        $discount = Discount::where('salon_id', $request->salon_id)
            ->where('is_active', true)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now)
            ->first();

        $discountPercent = $discount ? $discount->percent : 0;
        $discountAmount = ($originalPrice * $discountPercent) / 100;

        $finalPrice = $originalPrice - $discountAmount;

        $booking = new Booking();
        $booking->salon_id = $request->salon_id;
        $booking->service_id = $request->service_id;
        $booking->profile_id = $request->profile_id;

        $booking->booking_date = $request->booking_date;
        $booking->start_time = $startTime->format('H:i:s');
        $booking->end_time = $endTime->format('H:i:s');

        $booking->original_price = $originalPrice;
        $booking->discount_percent = $discountPercent;
        $booking->discount_amount = $discountAmount;
        $booking->final_price = $finalPrice;

        $booking->status = 'confirmed';

        $booking->save();

        return response()->json([
            'status' => true,
            'message' => 'نوبت با موفقیت ثبت شد.',
            'data' => $booking
        ], 200);
    }



    public function availableTimes(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|exists:salons,id',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date_format:Y-m-d',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'error' => $validator->errors()

            ], 422);
        }

        $salonService = SalonService::where('salon_id', $request->salon_id)->where('service_id', $request->service_id)->where('is_active', true)->first();


        if (!$salonService) {
            return response()->json([
                'status' => false,
                'message' => 'این خدمت برای سالن انتخاب‌شده فعال نیست.',
            ], 422);
        }

        $start = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->booking_date . ' 08:00'
        );

        $closingTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->booking_date . ' 21:00'
        );

        $duration = $salonService->duration;

        $availableTimes = [];

        $bookings = Booking::where('salon_id', $request->salon_id)->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->get();

        while ($start->copy()->addMinutes($duration)->lte($closingTime)) {
            $end = $start->copy()->addMinutes($duration);
            $conflict = false;
            foreach ($bookings as $booking) {
                if (
                    $booking->start_time < $end->format('H:i:s') &&
                    $booking->end_time > $start->format('H:i:s')
                ) {
                    $conflict = true;
                    break;
                }
            }

            if (!$conflict && !$start->isPast()) {

                $availableTimes[] = $start->format('H:i');
            }

            $start->addMinutes($duration);
        }

        return response()->json([
            'status' => true,
            'available_times' => $availableTimes
        ], 200);
    }

    public function getBooking(int $salonId)
    {
        $booking = Booking::where('salon_id', $salonId)->with(['service', 'profile'])->get();

        if ($booking->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => "اطلاعات یافت نشد",
                'data' => []
            ], 404);
        }



        return response()->json([
            'status' => true,
            'data' => $booking
        ], 200);
    }
}
