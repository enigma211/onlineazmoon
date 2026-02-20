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
            
            // You MUST create a pattern in your Melipayamak panel
            // Example pattern: "کد تایید شما: {0}"
            // Replace the pattern ID below with your actual pattern ID
            $patternId = env('MELIPAYAMAK_OTP_PATTERN_ID', '12345'); 
            
            // The method might differ slightly based on the exact melipayamak/laravel version
            // Usually it's sendByBaseNumber or similar. We'll use the standard shared method.
            $response = $sms->sendByBaseNumber($code, $to, $patternId);
            
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
