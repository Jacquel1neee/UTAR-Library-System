<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@utar.edu.my',
            'password' => Hash::make('password123'),
            'student_id' => 'ADMIN001',
            'phone_number' => '0123456789',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Sample students
        $students = [
            ['name' => 'John Doe', 'email' => 'john@utar.edu.my', 'student_id' => '2204287'],
            ['name' => 'Jane Smith', 'email' => 'jane@utar.edu.my', 'student_id' => '2204288'],
            ['name' => 'Mike Johnson', 'email' => 'mike@utar.edu.my', 'student_id' => '2204289'],
            ['name' => 'Sarah Lee', 'email' => 'sarah@utar.edu.my', 'student_id' => '2204290'],
            ['name' => 'David Tan', 'email' => 'david@utar.edu.my', 'student_id' => '2204291'],
            ['name' => 'Amy Wong', 'email' => 'amy@utar.edu.my', 'student_id' => '2204292'],
            ['name' => 'Chris Lim', 'email' => 'chris@utar.edu.my', 'student_id' => '2204293'],
            ['name' => 'Nurul Ismail', 'email' => 'nurul@utar.edu.my', 'student_id' => '2204294'],
            ['name' => 'Ethan Khoo', 'email' => 'ethan@utar.edu.my', 'student_id' => '2204295'],
            ['name' => 'Priya Raman', 'email' => 'priya@utar.edu.my', 'student_id' => '2204296'],
            ['name' => 'Kumar Raj', 'email' => 'kumar@utar.edu.my', 'student_id' => '2204297'],
            ['name' => 'Lina Chen', 'email' => 'lina@utar.edu.my', 'student_id' => '2204298'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password123'),
                'student_id' => $student['student_id'],
                'phone_number' => '01' . rand(10000000, 99999999),
                'role' => 'student',
                'is_active' => true,
            ]);
        }
    }
}