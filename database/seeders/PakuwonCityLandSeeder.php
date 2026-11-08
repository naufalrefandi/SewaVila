<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\City;
use App\Models\District;
use App\Models\Project;
use App\Models\Property;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PakuwonCityLandSeeder extends Seeder
{
    public function run(): void
    {
        $jatim = Province::where('slug', 'jawa-timur')->first();
        $surabaya = City::where('slug', 'surabaya')->first();
        $mulyorejo = District::where('slug', 'mulyorejo')->first();
        $project = Project::where('slug', 'pakuwon-city')->first();
        $agent = Agent::where('office_branch', 'like', '%Surabaya%')->first() ?? Agent::first();

        $images = [
            'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1524813686514-a57563d77d66?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1200&q=80',
        ];

        Property::updateOrCreate(
            ['property_code' => 'PKW-TNH-001-J'],
            [
                'title' => 'Dijual Kavling San Antonio Pakuwon City Surabaya - LT 240m² (12x20) Row Jalan Lebar 3 Mobil BU',
                'slug' => Str::slug('Dijual Kavling San Antonio Pakuwon City Surabaya 240m') . '-' . Str::random(5),
                'agent_id' => $agent?->id,
                'project_id' => $project?->id,
                'province_id' => $jatim?->id,
                'city_id' => $surabaya?->id,
                'district_id' => $mulyorejo?->id,
                'province' => 'Jawa Timur',
                'city' => 'Surabaya',
                'district' => 'Mulyorejo',
                'address' => 'Cluster San Antonio, Pakuwon City, Mulyorejo, Surabaya Timur',
                'property_type' => 'tanah',
                'listing_type' => 'dijual',
                'dimension' => '12 x 20 m',
                'land_area' => 240,
                'building_area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'carports' => 3,
                'facing' => 'Utara',
                'road_width' => 'Row 3 Mobil',
                'certificate' => 'PPJB',
                'price' => 3360000000,
                'original_price' => 3600000000,
                'price_prefix' => 'Nego',
                'is_urgent' => true,
                'is_featured' => true,
                'is_verified' => true,
                'status' => 'active',
                'description' => "BUTUH LAKU CEPAT‼️ (B.U)\nDijual tanah kavling siap bangun di cluster favorit San Antonio, Pakuwon City Surabaya Timur.\n\nSpesifikasi Kavling:\n- Cluster: San Antonio, Pakuwon City\n- Luas Tanah: 240 m²\n- Dimensi: 12 x 20 meter (Bentuk Kotak Ideal)\n- Legalitas: PPJB (Siap Proses AJB / Naik SHM)\n- Arah Hadap: Utara (Hadap Favorit, Adem & Teduh)\n- Akses Jalan: Row Jalan Lebar 3 Mobil\n- Kondisi: Tanah Matang Siap Bangun Rumah Impian\n\nKeunggulan Lokasi:\n- Lingkungan cluster prestisius dengan keamanan 24 jam & one gate system\n- 3 menit ke Pakuwon City Mall & East Coast Center\n- Dekat sekolah ternama: Cita Hati School, Gloria Christian School, Xin Zhong School\n- Investasi sangat menjanjikan dengan harga tanah yang terus meningkat",
                'images' => $images,
                'facilities' => [
                    'Tanah Matang Siap Bangun',
                    'Row Jalan Lebar 3 Mobil',
                    'One Gate 24H Security',
                    'Dekat Pakuwon City Mall',
                    'Dekat San Antonio Shopping Street',
                ],
            ]
        );
    }
}

