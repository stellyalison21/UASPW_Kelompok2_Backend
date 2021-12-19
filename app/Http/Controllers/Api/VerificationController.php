<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use VerifiesEmails;
use App\Models\User;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function verify(Request $request) {
        $userID = $request['id'];
        $user = User::findOrFail($userID);
        $date = Carbon::now();
        $user->email_verified_at = $date; 
        $user->save();
        return response()->json('Email verified');
        //redirect ke link hostingan nanti
    }

    public function resend(Request $request){
        if ($request->user()->hasVerifiedEmail()) {
        return response()->json('Email has already verified', 422);
    }
        $request->user()->sendEmailVerificationNotification();
        return response()->json('The email verification has been resent');
    }
}
