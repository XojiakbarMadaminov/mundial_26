<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse;

class PendingApprovalRegisterResponse implements RegisterResponse
{
    public function toResponse($request)
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'message' => __('auth.registration_pending'),
            ], 201)
            : redirect('/login')->with('status', __('auth.registration_pending'));
    }
}
