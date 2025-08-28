<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HealthcareFacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            // Hospitals
            [
                'name' => 'Hospital Kuala Lumpur',
                'type' => 'hospital',
                'address' => 'Jalan Pahang, Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'phone' => '+603-26155555',
                'email' => 'info@hkl.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Hospital Selangor',
                'type' => 'hospital',
                'address' => 'Jalan Hospital, Shah Alam',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'phone' => '+603-55163300',
                'email' => 'info@hselangor.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Hospital Putrajaya',
                'type' => 'hospital',
                'address' => 'Presint 7, Putrajaya',
                'city' => 'Putrajaya',
                'state' => 'Putrajaya',
                'phone' => '+603-83124200',
                'email' => 'info@hputrajaya.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Hospital Melaka',
                'type' => 'hospital',
                'address' => 'Jalan Mufti Haji Khalil, Melaka',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'phone' => '+606-2892344',
                'email' => 'info@hmelaka.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Hospital Pulau Pinang',
                'type' => 'hospital',
                'address' => 'Jalan Residensi, George Town',
                'city' => 'George Town',
                'state' => 'Pulau Pinang',
                'phone' => '+604-2225333',
                'email' => 'info@hpp.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],

            // Clinics
            [
                'name' => 'Klinik Kesihatan Petaling Jaya',
                'type' => 'clinic',
                'address' => 'Jalan SS1/1, Petaling Jaya',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'phone' => '+603-79562222',
                'email' => 'kkpj@moh.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Klinik Desa Setapak',
                'type' => 'clinic',
                'address' => 'Jalan Setapak, Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'phone' => '+603-40211111',
                'email' => 'kds@moh.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Klinik Kesihatan Bangi',
                'type' => 'clinic',
                'address' => 'Jalan Bangi, Kajang',
                'city' => 'Kajang',
                'state' => 'Selangor',
                'phone' => '+603-89261111',
                'email' => 'kkb@moh.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Klinik Kesihatan Melaka Tengah',
                'type' => 'clinic',
                'address' => 'Jalan Tun Ali, Melaka',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'phone' => '+606-2821111',
                'email' => 'kkmt@moh.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ],
            [
                'name' => 'Klinik Kesihatan Bukit Mertajam',
                'type' => 'clinic',
                'address' => 'Jalan Perda, Bukit Mertajam',
                'city' => 'Bukit Mertajam',
                'state' => 'Pulau Pinang',
                'phone' => '+604-5391111',
                'email' => 'kkbm@moh.gov.my',
                'is_panel_facility' => true,
                'status' => 'active'
            ]
        ];

        foreach ($facilities as $facility) {
            DB::table('healthcare_facilities')->insert($facility);
        }
    }
}
