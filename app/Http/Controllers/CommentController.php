<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\Salon;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|min:1|max:5',
            'text' => 'required|min:10|max:500',
            'salon_id' => 'required|exists:salons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'error' => $validator->errors()

            ], 422);
        }
        $user = $request->user();

        $profile = $user->profile;
        $comment = new Comment();
        $comment->rating = $request->rating;
        $comment->text = $request->text;
        $comment->salon_id = $request->salon_id;
        $comment->profile_id = $profile->id;
        $comment->save();

        return response()->json([
            'status' => true,
            'message' => 'کامنت با موفقیت ارسال شد'
        ], 200);
    }

    public function getComments(int $salonId)
    {
        $salon = Salon::find($salonId);
        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'سالن مورد نظر یافت نشد',
            ], 404);
        }

        $comment = Comment::where('salon_id', $salonId)->where('status', 'approved')->where('is_active', true)->with('profile')->get();
        return response()->json([
            'status' => true,
            'data' => $comment
        ], 200);
    }

    public function acceptComment(int $id, Request $request)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'status' => false,
                'message' => 'کامنت مورد نظر یافت نشد',
            ], 404);
        }

        $comment->status = 'approved';
        $comment->save();

        return response()->json([
            'status' => true,
            'message' => 'کامنت با موفقیت تأیید شد',
            'data' => $comment,
        ], 200);
    }



    public function getCommentPending(int $salonId)
    {
        $salon = Salon::find($salonId);

        if (!$salon) {
            return response()->json([
                'status' => false,
                'message' => 'سالن مورد نظر یافت نشد',
            ], 404);
        }

        $comment = Comment::where('salon_id', $salonId)->where('status', 'pending')->where('is_active', true)->with('profile')->get();

        return response()->json([
            'status' => true,
            'data' => $comment
        ], 200);
    }


    public function rejectionComment(int $id)
    {
        

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'status' => false,
                'message' => 'کامنت مورد نظر یافت نشد',
            ], 404);
        }
        $comment->delete();
         return response()->json([
                'status' => true,
                'message' => 'کامنت حدف شد',
            ], 404);
    }
}
