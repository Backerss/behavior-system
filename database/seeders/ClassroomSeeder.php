<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassRoom;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['classes_id' => 1, 'classes_level' => 'ม.1', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 2, 'classes_level' => 'ม.2', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 3, 'classes_level' => 'ม.3', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 4, 'classes_level' => 'ม.4', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 5, 'classes_level' => 'ม.5', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 6, 'classes_level' => 'ม.6', 'classes_room_number' => '1', 'classes_academic_year' => '2568', 'teachers_id' => null],
            ['classes_id' => 7, 'classes_level' => 'ม.1', 'classes_room_number' => '2', 'classes_academic_year' => '2568', 'teachers_id' => null],
        ];

        foreach ($classes as $class) {
            ClassRoom::create($class);
        }
    }
}
