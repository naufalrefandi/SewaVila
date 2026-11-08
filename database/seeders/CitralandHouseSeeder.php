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

class CitralandHouseSeeder extends Seeder
{
    public function run(): void
    {
        $jatim = Province::where('slug', 'jawa-timur')->first();
        $surabaya = City::where('slug', 'surabaya')->first();
        $sambikerep = District::where('slug', 'sambikerep')->first();
        $project = Project::where('slug', 'citraland-surabaya')->first();
        $agent = Agent::where('office_branch', 'like', '%Surabaya%')->first() ?? Agent::first();

        $images = [
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1600585152220-90363fe7e115?auto=format&fit=crop&w=1200&q=80',
        ];

        Property::updateOrCreate(
            ['property_code' => 'CTL-RUM-001-J'],
            [
                'title' => 'Rumah Minimalis Citraland Utama Dekat Pasar Modern - Full Marmer Hook Hadap Barat Selatan',
                'slug' => Str::slug('Rumah Minimalis Citraland Utama Dekat Pasar Modern Full Marmer Hook') . '-' . Str::random(5),
                'agent_id' => $agent?->id,
                'project_id' => $project?->id,
                'province_id' => $jatim?->id,
                'city_id' => $surabaya?->id,
                'district_id' => $sambikerep?->id,
                'province' => 'Jawa Timur',
                'city' => 'Surabaya',
                'district' => 'Sambikerep',
                'address' => 'Citraland Utama (Dekat Pasar Modern / Fresh Market), Sambikerep, Surabaya Barat',
                'property_type' => 'rumah',
                'listing_type' => 'dijual',
                'dimension' => '11 x 15 m',
                'land_area' => 162,
                'building_area' => 205,
                'floors' => 2,
                'bedrooms' => 3,
                'maid_bedrooms' => 1,
                'bathrooms' => 3,
                'maid_bathrooms' => 1,
                'carports' => 2,
                'facing' => 'Barat + Selatan (Hook)',
                'flooring_material' => 'Full Marmer',
                'furnished_status' => 'fully-furnished',
                'certificate' => 'SHM',
                'electricity' => 4400,
                'price' => 3850000000,
                'price_prefix' => 'Nego',
                'description' => "DIJUAL CEPAT!\nRumah minimalis mewah siap huni di Citraland Utama, lokasi sangat strategis dekat Pasar Modern (Fresh Market) CitraLand Surabaya Barat.\n\nSpesifikasi Properti:\n- Lokasi: Citraland Utama Dekat Pasar Modern\n- Luas Tanah: 162 m² (Dimensi 11 x 15 m Hook)\n- Luas Bangunan: 205 m²\n- Jumlah Lantai: 2 Lantai\n- Kamar Tidur: 3+1 (3 KT Utama + 1 KT ART)\n- Kamar Mandi: 3+1 (3 KM Utama + 1 KM ART)\n- Arah Hadap: Barat + Selatan (Posisi Hook, Sirkulasi Udara & Cahaya Sangat Bagus)\n- Sertifikat: SHM (Sertifikat Hak Milik - On Hand)\n- Listrik: PLN 4.400 Watt\n- Kondisi: Furnished Siap Huni\n- Finishing Lantai: Full Marmer Mewah\n- Carport: 2 Mobil + Canopy\n\nKeunggulan Lokasi:\n- 2 menit ke Fresh Market / Pasar Modern CitraLand\n- Dekat Sekolah Ciputra & Universitas Ciputra (UC)\n- Lingkungan tenang, aman dengan one gate system & security 24 jam\n- Row jalan lebar bisa papasan 3 mobil",
                'images' => $images,
                'facilities' => [
                    'Full Lantai Marmer',
                    'Posisi Hook (2 Muka)',
                    'Kitchen Set & Kompor Tanam',
                    'Water Heater',
                    'Carport 2 Mobil',
                    'One Gate 24H Security',
                    'Dekat Pasar Modern',
                ],
                'is_featured' => true,
                'is_verified' => true,
                'status' => 'active',
            ]
        );
    }
}

