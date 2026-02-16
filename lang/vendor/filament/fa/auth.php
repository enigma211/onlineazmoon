<?php

return [
    'login' => [
        'heading' => 'ورود به پنل مدیریت',
        'actions' => [
            'authenticate' => 'ورود',
        ],
        'messages' => [
            'failed' => 'اطلاعات ورود صحیح نیست.',
            'throttled' => 'تلاش‌های زیاد برای ورود. لطفاً پس از :seconds ثانیه مجدداً تلاش کنید.',
        ],
    ],
    'register' => [
        'heading' => 'ثبت نام',
        'actions' => [
            'register' => 'ثبت نام',
        ],
        'messages' => [
            'failed' => 'خطا در ثبت نام.',
        ],
    ],
    'password_reset' => [
        'heading' => 'بازیابی رمز عبور',
        'actions' => [
            'request' => 'ارسال لینک بازیابی',
            'reset' => 'بازیابی رمز عبور',
        ],
        'messages' => [
            'sent' => 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد.',
            'failed' => 'ایمیل یافت نشد.',
            'throttled' => 'تلاش‌های زیاد برای بازیابی رمز عبور. لطفاً پس از :seconds ثانیه مجدداً تلاش کنید.',
        ],
    ],
    'email_verification' => [
        'heading' => 'تایید ایمیل',
        'actions' => [
            'resend' => 'ارسال مجدد ایمیل تایید',
        ],
        'messages' => [
            'sent' => 'ایمیل تایید مجدداً ارسال شد.',
        ],
    ],
];
