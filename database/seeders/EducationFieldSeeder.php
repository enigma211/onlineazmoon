<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EducationField;

class EducationFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            ['name' => 'عمران', 'sort_order' => 1],
            ['name' => 'معماری', 'sort_order' => 2],
            ['name' => 'تاسیسات مکانیکی', 'sort_order' => 3],
            ['name' => 'تاسیسات برقی', 'sort_order' => 4],
            ['name' => 'نقشه برداری', 'sort_order' => 5],
        ];

        foreach ($fields as $field) {
            EducationField::create($field);
        }
    }
}
