<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'family',
        'mobile',
        'national_code',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->family;
    }

    public function canAccessPanel(?Panel $panel = null): bool
    {
        // Only allow specific admin users to access Filament panel
        // For now, check if user has admin role or specific mobile number
        // You can modify this logic based on your requirements
        $adminMobileNumbers = [
            '09123456789', // Default admin
            '09876543211', // Test admin
            // Add more admin mobile numbers here
        ];
        
        return in_array($this->mobile, $adminMobileNumbers);
    }
}
