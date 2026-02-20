<?php

namespace App\Services;

use Melipayamak\Laravel\Facade as Melipayamak;
use Illuminate\Support\Facades\Log;

class SMS
{
    /**
     * Send a standard text SMS
     */
    public static function send(string $to, string $text): bool
    {
        try {
            $sms = Melipayamak::sms();
            // Assuming 5000... is your sender number. You should change this to your actual sender number.
            // Or better, read it from config/env
            $from = env('MELIPAYAMAK_SENDER', '5000...');
            $response = $sms->send($to, $from, $text);
            
            Log::info("SMS sent to {$to}", ['response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send OTP using pattern (Shared Service / Service Share)
     * This is highly recommended for OTPs to bypass blacklists
     */
    public static function sendOTP(string $to, string $code): bool
    {
        try {
            $sms = Melipayamak::sms();
            
            // استفاده از BODY_ID که شما تنظیم کردید
            $bodyId = env('MELIPAYAMAK_OTP_BODY_ID', '429194'); 
            
            // متد sendByBaseNumber پارامتر اول را به صورت آرایه دریافت می‌کند برای متغیرهای پترن (مثل {0})
            $response = $sms->sendByBaseNumber([$code], $to, $bodyId);
            
            Log::info("OTP SMS sent to {$to}", ['code' => $code, 'response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP SMS to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
