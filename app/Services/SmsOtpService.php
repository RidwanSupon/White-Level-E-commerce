<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SmsOtpService
{
    public function generateOtp(string $phone): string
    {
        $otp = (string) rand(100000, 999999);
        Cache::put('otp_' . $phone, $otp, now()->addMinutes(5));

        return $otp;
    }

    public function verifyOtp(string $phone, string $inputOtp): bool
    {
        $cachedOtp = Cache::get('otp_' . $phone);
        if ($cachedOtp && $cachedOtp === $inputOtp) {
            Cache::forget('otp_' . $phone);
            return true;
        }

        return false;
    }
}
