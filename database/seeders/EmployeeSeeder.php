<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create employees from the original database
        $employees = [
            [
                'id' => '2024DEV244K',
                'emp_name' => 'KOUAYE ALPHONSE',
                'gender' => 'MALE',
                'emp_mail' => 'kouaye5376@gmail.com',
                'emp_number' => 690312654,
                'department' => 'development',
            ],
            [
                'id' => '2024DEV500S',
                'emp_name' => 'Sinak Justo',
                'gender' => 'Male',
                'emp_mail' => 'sinakjusto@gmail.com',
                'emp_number' => 45633212,
                'department' => 'Development',
            ],
            [
                'id' => '2024WEB176T',
                'emp_name' => 'TCHEUMANI SINAK',
                'gender' => 'MALE',
                'emp_mail' => 'tcheumanisinakjusto@gmail.com',
                'emp_number' => 680312765,
                'department' => 'WEB-Designer',
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}
