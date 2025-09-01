<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = [
            [
                'name' => 'Hospital Kuala Lumpur',
                'description' => 'Government hospital providing comprehensive healthcare services',
                'address' => 'Jalan Pahang, Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '53000',
                'country' => 'Malaysia',
                'phone' => '+603-26155555',
                'email' => 'info@hkl.gov.my',
                'website' => 'https://www.hkl.gov.my',
                'specialties' => json_encode(['Cardiology', 'Neurology', 'Orthopedics', 'Emergency Medicine']),
                'license_number' => 'HKL001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Selangor',
                'description' => 'State hospital serving Selangor residents',
                'address' => 'Jalan Hospital, Shah Alam',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postal_code' => '40000',
                'country' => 'Malaysia',
                'phone' => '+603-55163300',
                'email' => 'info@hselangor.gov.my',
                'website' => 'https://www.hselangor.gov.my',
                'specialties' => json_encode(['General Medicine', 'Surgery', 'Pediatrics', 'Obstetrics']),
                'license_number' => 'HS001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Putrajaya',
                'description' => 'Modern government hospital in Putrajaya',
                'address' => 'Presint 7, Putrajaya',
                'city' => 'Putrajaya',
                'state' => 'Putrajaya',
                'postal_code' => '62250',
                'country' => 'Malaysia',
                'phone' => '+603-83124200',
                'email' => 'info@hputrajaya.gov.my',
                'website' => 'https://www.hputrajaya.gov.my',
                'specialties' => json_encode(['Internal Medicine', 'Surgery', 'Radiology', 'Pathology']),
                'license_number' => 'HP001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Melaka',
                'description' => 'State hospital in Melaka',
                'address' => 'Jalan Mufti Haji Khalil, Melaka',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'postal_code' => '75400',
                'country' => 'Malaysia',
                'phone' => '+606-2892344',
                'email' => 'info@hmelaka.gov.my',
                'website' => 'https://www.hmelaka.gov.my',
                'specialties' => json_encode(['General Medicine', 'Surgery', 'Emergency Medicine']),
                'license_number' => 'HM001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Pulau Pinang',
                'description' => 'State hospital in Penang',
                'address' => 'Jalan Residensi, George Town',
                'city' => 'George Town',
                'state' => 'Pulau Pinang',
                'postal_code' => '10990',
                'country' => 'Malaysia',
                'phone' => '+604-2225333',
                'email' => 'info@hpp.gov.my',
                'website' => 'https://www.hpp.gov.my',
                'specialties' => json_encode(['Cardiology', 'Neurology', 'Orthopedics', 'General Surgery']),
                'license_number' => 'HPP001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
        ];

        foreach ($hospitals as $hospital) {
            Hospital::create($hospital);
        }

        $this->command->info('Hospitals seeded successfully!');
    }
}
