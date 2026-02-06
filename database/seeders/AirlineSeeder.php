<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airlines = [
            ['name' => 'Saudi Arabian Airlines', 'status' => 'active'],
            ['name' => 'Emirates', 'status' => 'active'],
            ['name' => 'Qatar Airways', 'status' => 'active'],
            ['name' => 'Etihad Airways', 'status' => 'active'],
            ['name' => 'Flyadeal', 'status' => 'active'],
            ['name' => 'Flynas', 'status' => 'active'],
            ['name' => 'Gulf Air', 'status' => 'active'],
            ['name' => 'Kuwait Airways', 'status' => 'active'],
            ['name' => 'Oman Air', 'status' => 'active'],
            ['name' => 'Pakistan International Airlines', 'status' => 'active'],
            ['name' => 'Air India', 'status' => 'active'],
            ['name' => 'IndiGo', 'status' => 'active'],
            ['name' => 'SpiceJet', 'status' => 'active'],
            ['name' => 'Turkish Airlines', 'status' => 'active'],
            ['name' => 'British Airways', 'status' => 'active'],
        ];

        foreach ($airlines as $airline) {
            Airline::create($airline);
        }
    }
}
