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
            // استفاده از کانفیگ‌های ملی‌پیامک
            $username = config('melipayamak.username');
            $password = config('melipayamak.password');
            $bodyId = env('MELIPAYAMAK_OTP_BODY_ID', '429194'); 
            
            // با توجه به مشکلات پکیج در ارسال http_build_query برای متد BaseServiceNumber، 
            // درخواست را مستقیما به صورت JSON با cURL ارسال می‌کنیم که ۱۰۰٪ پایدار است.
            $data = [
                'username' => $username,
                'password' => $password,
                'to' => $to,
                'bodyId' => $bodyId,
                'text' => $code // مقدار کد مستقیماً فرستاده می‌شود
            ];

            $ch = curl_init('https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::info("OTP SMS sent to {$to}", [
                'code' => $code, 
                'http_code' => $httpcode, 
                'response' => $response
            ]);

            // بررسی می‌کنیم که آیا مقدار بازگشتی موفقیت‌آمیز بوده یا خیر (RetStatus: 1 یعنی موفق)
            $decodedResponse = json_decode($response, true);
            if (isset($decodedResponse['RetStatus']) && $decodedResponse['RetStatus'] == 1) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP SMS to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
