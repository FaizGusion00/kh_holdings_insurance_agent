<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            [
                'name' => 'Klinik Kesihatan Petaling Jaya',
                'description' => 'Government health clinic serving Petaling Jaya area',
                'address' => 'Jalan SS1/1, Petaling Jaya',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'postal_code' => '47300',
                'country' => 'Malaysia',
                'phone' => '+603-79562222',
                'email' => 'kkpjp@moh.gov.my',
                'website' => 'https://www.moh.gov.my',
                'specialties' => json_encode(['General Practice', 'Family Medicine', 'Preventive Care']),
                'operating_hours' => json_encode([
                    ['day' => 'Monday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Tuesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Thursday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Friday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Saturday', 'open_time' => '08:00', 'close_time' => '12:00'],
                ]),
                'license_number' => 'KKPJP001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Desa Setapak',
                'description' => 'Rural health clinic in Setapak area',
                'address' => 'Jalan Setapak, Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '53000',
                'country' => 'Malaysia',
                'phone' => '+603-40211111',
                'email' => 'kds@moh.gov.my',
                'website' => 'https://www.moh.gov.my',
                'specialties' => json_encode(['General Practice', 'Rural Health', 'Maternal Care']),
                'operating_hours' => json_encode([
                    ['day' => 'Monday', 'open_time' => '08:00', 'close_time' => '16:00'],
                    ['day' => 'Tuesday', 'open_time' => '08:00', 'close_time' => '16:00'],
                    ['day' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '16:00'],
                    ['day' => 'Thursday', 'open_time' => '08:00', 'close_time' => '16:00'],
                    ['day' => 'Friday', 'open_time' => '08:00', 'close_time' => '16:00'],
                ]),
                'license_number' => 'KDS001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Bangi',
                'description' => 'Health clinic in Bangi, Kajang',
                'address' => 'Jalan Bangi, Kajang',
                'city' => 'Kajang',
                'state' => 'Selangor',
                'postal_code' => '43000',
                'country' => 'Malaysia',
                'phone' => '+603-89261111',
                'email' => 'kkb@moh.gov.my',
                'website' => 'https://www.moh.gov.my',
                'specialties' => json_encode(['General Practice', 'Child Health', 'Immunization']),
                'operating_hours' => json_encode([
                    ['day' => 'Monday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Tuesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Thursday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Friday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Saturday', 'open_time' => '08:00', 'close_time' => '12:00'],
                ]),
                'license_number' => 'KKB001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Melaka Tengah',
                'description' => 'Central health clinic in Melaka',
                'address' => 'Jalan Tun Ali, Melaka',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'postal_code' => '75000',
                'country' => 'Malaysia',
                'phone' => '+606-2821111',
                'email' => 'kkmt@moh.gov.my',
                'website' => 'https://www.moh.gov.my',
                'specialties' => json_encode(['General Practice', 'Chronic Disease Management', 'Health Screening']),
                'operating_hours' => json_encode([
                    ['day' => 'Monday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Tuesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Thursday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Friday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Saturday', 'open_time' => '08:00', 'close_time' => '12:00'],
                ]),
                'license_number' => 'KKMT001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Bukit Mertajam',
                'description' => 'Health clinic in Bukit Mertajam, Penang',
                'address' => 'Jalan Perda, Bukit Mertajam',
                'city' => 'Bukit Mertajam',
                'state' => 'Pulau Pinang',
                'postal_code' => '14000',
                'country' => 'Malaysia',
                'phone' => '+604-5391111',
                'email' => 'kkbm@moh.gov.my',
                'website' => 'https://www.moh.gov.my',
                'specialties' => json_encode(['General Practice', 'Women Health', 'Family Planning']),
                'operating_hours' => json_encode([
                    ['day' => 'Monday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Tuesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Wednesday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Thursday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Friday', 'open_time' => '08:00', 'close_time' => '17:00'],
                    ['day' => 'Saturday', 'open_time' => '08:00', 'close_time' => '12:00'],
                ]),
                'license_number' => 'KKBM001',
                'license_expiry' => '2025-12-31',
                'is_active' => true,
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::create($clinic);
        }

        $this->command->info('Clinics seeded successfully!');
    }
}
