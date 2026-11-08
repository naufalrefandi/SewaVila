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

class PuncakDharmahusadaSeeder extends Seeder
{
    public function run(): void
    {
        $jatim = Province::where('slug', 'jawa-timur')->first();
        $surabaya = City::where('slug', 'surabaya')->first();
        $mulyorejo = District::where('slug', 'mulyorejo')->first()
            ?? District::where('name', 'Sukolilo')->first();

        $project = Project::firstOrCreate(
            ['slug' => 'apartemen-puncak-dharmahusada'],
            [
                'city_id' => $surabaya->id,
                'district_id' => $mulyorejo?->id,
                'name' => 'Apartemen Puncak Dharmahusada',
                'type' => 'apartemen',
                'developer' => 'Puncak Group',
                'address' => 'Jl. Dr. Ir. H. Soekarno No. 99 (MERR), Kalijudan, Mulyorejo, Surabaya Timur',
                'total_floors' => 42,
                'year_completed' => 2019,
                'cover_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
                'facilities' => [
                    'Olympic Swimming Pool',
                    'Gym & Fitness Center',
                    'Ruko Komersial di Lantai Dasar',
                    'Access Card & Keamanan 24 Jam',
                    'Food Court & Minimarket',
                    'Parkir Luas',
                ],
                'description' => 'Superblock modern di jalur prestisius MERR Surabaya Timur dengan tower apartemen dan deretan ruko komersial siap pakai.',
            ]
        );

        $agent = Agent::where('office_branch', 'like', '%Surabaya%')->first() ?? Agent::first();

        // Apartment Images Curated Sets
        $apartmentImages = [
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1200&q=80',
        ];

        // Ruko Images Curated Sets
        $rukoImages = [
            'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80',
        ];

        $listings = [
            // 1
            [
                'property_code' => 'PDH-APT-001-S',
                'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 20 - 1 BR City View Full Furnish',
                'property_type' => 'apartemen',
                'unit_type' => '1 BR',
                'unit_tower' => 'Tower B',
                'unit_floor' => 'Lantai 20',
                'unit_view' => 'City View',
                'listing_type' => 'disewa',
                'price' => 25000000,
                'rent_price' => 25000000,
                'rent_period' => 'tahun',
                'building_area' => 21,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'furnished_status' => 'fully-furnished',
                'certificate' => 'Strata Title',
                'description' => "Disewakan unit nyaman di Apartemen Puncak Dharmahusada.\n\nSpesifikasi Unit:\n- Tipe: 1 BR\n- Tower: B, Lantai 20\n- View: City View Lepas\n- Kondisi: Furnished Siap Huni\n- Luas Bangunan: 21 m²\n- Harga Sewa: Rp 25.000.000 / tahun\n\nLokasi strategis di jalur MERR Surabaya Timur, dekat kampus UNAIR, ITS, RS Haji, dan pusat perbelanjaan.",
                'images' => $apartmentImages,
            ],
            // 2
            [
                'property_code' => 'PDH-APT-002-J',
                'title' => 'Dijual Apartemen Puncak Dharmahusada Tower A Lt 23 - 3 BR 76m² City View',
                'property_type' => 'apartemen',
                'unit_type' => '3 BR',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 23',
                'unit_view' => 'City View',
                'listing_type' => 'dijual',
                'price' => 325000000,
                'sale_price' => 325000000,
                'transfer_fee_info' => 'Non Peralihan',
                'building_area' => 76,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'furnished_status' => 'unfurnished',
                'certificate' => 'Strata Title',
                'description' => "Unit keluarga langka ukuran luas 76 m² 3 Kamar Tidur di Puncak Dharmahusada Tower A lantai 23.\n\nSpesifikasi:\n- Tipe: 3 BR Corner\n- Tower: A, Lantai 23\n- View: City View\n- Kondisi: Kosongan (Unfurnished), siap didekorasi sesuai selera\n- Luas: 76 m²\n- Harga Sewa: Rp 25.000.000 / tahun\n- Harga Jual: Rp 325.000.000 (Non peralihan)\n\nCocok untuk hunian keluarga besar maupun investasi sewa mahasiswa/dokter.",
                'images' => $apartmentImages,
            ],
            // 3
            [
                'property_code' => 'PDH-APT-003-S',
                'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 9 - 2 BR Pool View Full Furnished',
                'property_type' => 'apartemen',
                'unit_type' => '2 BR',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 9',
                'unit_view' => 'Pool View',
                'listing_type' => 'disewa',
                'price' => 40000000,
                'rent_price' => 40000000,
                'monthly_rent_price' => 4000000,
                'rent_period' => 'tahun',
                'ipl_info' => 'Non IPL',
                'building_area' => 56,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'furnished_status' => 'fully-furnished',
                'certificate' => 'Strata Title',
                'description' => "Unit 2 BR favorit menghadap langsung ke kolam renang (Pool View) di lantai 9 Tower A.\n\nSpesifikasi:\n- Tipe: 2 BR\n- Tower: A, Lantai 9\n- View: Pool View Cantik\n- Luas: 56 m²\n- Kondisi: Full Furnished lengkap dengan interior, AC, kitchen, kasur, TV\n- Harga Sewa Bulanan: Rp 4.000.000 / bulan (non IPL)\n- Harga Sewa Tahunan: Rp 40.000.000 / tahun",
                'images' => $apartmentImages,
            ],
            // 4
            [
                'property_code' => 'PDH-RUKO-004-J',
                'title' => 'Dijual Ruko Apartemen Puncak Dharmahusada 3 Lantai (4x15) - Turun Harga dari 3M ke 2M PPJB',
                'property_type' => 'ruko',
                'unit_type' => 'Ruko Apartemen 3 Lantai',
                'dimension' => '4 x 15 m',
                'land_area' => 60,
                'building_area' => 160,
                'floors' => 3,
                'bathrooms' => 3,
                'unit_view' => 'City View / Hadap Jalan',
                'listing_type' => 'dijual',
                'price' => 2000000000,
                'sale_price' => 2000000000,
                'original_price' => 3000000000,
                'certificate' => 'PPJB',
                'furnished_status' => 'unfurnished',
                'description' => "TURUN HARGA DRASTIS! Dari ~Rp 3 Miliar~ kini dijual cepat HANYA Rp 2 Miliar!\n\nRuko komersial di area komplek Apartemen Puncak Dharmahusada dengan captive market ribuan penghuni apartemen setiap hari.\n\nSpesifikasi Ruko:\n- Ukuran Tanah: 4 x 15 m (LT: 60 m²)\n- Jumlah Lantai: 3 Lantai\n- Kondisi: Kosongan siap renovasi usaha\n- View: City View / Akses Jalan Aktif\n- Legalitas: PPJB Siap AJB\n- Harga Awal: Rp 3.000.000.000\n- Harga Saat Ini: Rp 2.000.000.000 (HEMAT 1 MILIAR)\n\nSangat ideal untuk kantor, cafe/resto, laundry, apotek, klinik, atau minimarket.",
                'images' => $rukoImages,
            ],
            // 5
            [
                'property_code' => 'PDH-APT-005-J',
                'title' => 'Dijual Apartemen Puncak Dharmahusada Studio Tower A Lt 23 Full Furnished City View',
                'property_type' => 'apartemen',
                'unit_type' => 'Studio',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 23',
                'unit_view' => 'City View',
                'listing_type' => 'dijual',
                'price' => 225000000,
                'sale_price' => 225000000,
                'building_area' => 24,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'furnished_status' => 'fully-furnished',
                'certificate' => 'Strata Title',
                'description' => "Unit Studio lantai 23 Tower A dengan view kota Surabaya yang mempesona.\n\nSpesifikasi:\n- Tipe: Studio\n- Tower: A, Lantai 23\n- Luas: 24 m²\n- View: City View\n- Kondisi: Full Furnished rapi tinggal bawa koper\n- Harga Sewa: Rp 23.000.000 / tahun\n- Harga Jual: Rp 225.000.000",
                'images' => $apartmentImages,
            ],
            // 6
            [
                'property_code' => 'PDH-RUKO-006-S',
                'title' => 'Disewakan Ruko Apartemen Puncak Dharmahusada 4 Lantai (5x15) LB 292.5m² Hadap Utara KM Tiap Lantai',
                'property_type' => 'ruko',
                'unit_type' => 'Ruko Apartemen 4 Lantai',
                'dimension' => '5 x 15 m',
                'land_area' => 75,
                'building_area' => 292.5,
                'floors' => 4,
                'bathrooms' => 4,
                'facing' => 'Utara',
                'listing_type' => 'disewa',
                'price' => 60000000,
                'rent_price' => 60000000,
                'rent_period' => 'tahun',
                'certificate' => 'HGB / PPJB',
                'furnished_status' => 'unfurnished',
                'description' => "Disewakan Ruko Megah 4 Lantai di kompleks Apartemen Puncak Dharmahusada.\n\nSpesifikasi Utama:\n- Ukuran Tanah (T): 5 x 15 m (LT: 75 m²)\n- Luas Bangunan (LB): 292,5 m²\n- Jumlah Lantai: 4 Lantai\n- Kamar Mandi: 4 (Tersedia KM di tiap lantai!)\n- Arah Hadap: Utara (Adem tidak silau)\n- Kondisi: Terawat, siap pakai untuk kantor cabang, bimbel, perbankan, atau resto\n- Harga Sewa: Rp 60.000.000 / tahun (Sangat Murah untuk 4 Lantai)",
                'images' => $rukoImages,
            ],
            // 7
            [
                'property_code' => 'PDH-APT-007-S',
                'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 39 - 1 BR City View Kosongan',
                'property_type' => 'apartemen',
                'unit_type' => '1 BR',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 39',
                'unit_view' => 'City View',
                'listing_type' => 'disewa',
                'price' => 12000000,
                'rent_price' => 12000000,
                'rent_period' => 'tahun',
                'building_area' => 21,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'furnished_status' => 'unfurnished',
                'certificate' => 'Strata Title',
                'description' => "Unit hemat harga miring di lantai tinggi (Lantai 39 Tower A).\n\nSpesifikasi:\n- Tipe: 1 BR\n- Tower: A, Lantai 39\n- View: City View Spektakuler\n- Kondisi: Kosongan (Unfurnished)\n- Luas: 21 m²\n- Harga Sewa: HANYA Rp 12.000.000 / tahun (Rp 1 Jt/bln)",
                'images' => $apartmentImages,
            ],
            // 8
            [
                'property_code' => 'PDH-APT-008-S',
                'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 37 - 2 BR 36m² City View Kosongan',
                'property_type' => 'apartemen',
                'unit_type' => '2 BR',
                'unit_tower' => 'Tower B',
                'unit_floor' => 'Lantai 37',
                'unit_view' => 'City View',
                'listing_type' => 'disewa',
                'price' => 15000000,
                'rent_price' => 15000000,
                'rent_period' => 'tahun',
                'building_area' => 36,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'furnished_status' => 'unfurnished',
                'certificate' => 'Strata Title',
                'description' => "Sewa unit 2 Kamar Tidur super hemat di Tower B Lantai 37.\n\nSpesifikasi:\n- Tipe: 2 BR\n- Tower: B, Lantai 37\n- View: City View\n- Kondisi: Kosongan\n- Luas: 36 m²\n- Harga Sewa: Rp 15.000.000 / tahun",
                'images' => $apartmentImages,
            ],
            // 9
            [
                'property_code' => 'PDH-APT-009-J',
                'title' => 'Dijual Apartemen Puncak Dharmahusada Tower A Lt 23 - City View Furnished',
                'property_type' => 'apartemen',
                'unit_type' => '3 BR',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 23',
                'unit_view' => 'City View',
                'listing_type' => 'dijual',
                'price' => 225000000,
                'sale_price' => 225000000,
                'building_area' => 24,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'furnished_status' => 'fully-furnished',
                'certificate' => 'Strata Title',
                'description' => "Unit furnished lantai 23 Tower A dengan pilihan beli maupun sewa.\n\nSpesifikasi:\n- Tower: A, Lantai 23\n- View: City View\n- Kondisi: Furnished\n- Luas: 24 m²\n- Harga Sewa: Rp 23.000.000 / tahun\n- Harga Jual: Rp 225.000.000",
                'images' => $apartmentImages,
            ],
            // 10
            [
                'property_code' => 'PDH-APT-010-S',
                'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 12 - 2 BR Pool View Semi Furnished',
                'property_type' => 'apartemen',
                'unit_type' => '2 BR',
                'unit_tower' => 'Tower A',
                'unit_floor' => 'Lantai 12',
                'unit_view' => 'Pool View',
                'listing_type' => 'disewa',
                'price' => 25000000,
                'rent_price' => 25000000,
                'rent_period' => 'tahun',
                'building_area' => 56,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'furnished_status' => 'semi-furnished',
                'certificate' => 'Strata Title',
                'description' => "Unit 2 BR luas 56 m² dengan pemandangan kolam renang yang sejuk.\n\nSpesifikasi:\n- Tipe: 2 BR\n- Tower: A, Lantai 12\n- View: Pool View\n- Luas: 56 m²\n- Kondisi: Semi Furnished\n- Harga Sewa: Rp 25.000.000 / tahun",
                'images' => $apartmentImages,
            ],
        ];

        foreach ($listings as $idx => $item) {
            $slugBase = Str::slug($item['title']);
            $existing = Property::where('property_code', $item['property_code'])->first();
            $slug = $existing ? $existing->slug : $slugBase;
            $c = 1;
            while (Property::where('slug', $slug)->where('property_code', '!=', $item['property_code'])->exists()) {
                $slug = "{$slugBase}-{$c}";
                $c++;
            }

            Property::updateOrCreate(
                ['property_code' => $item['property_code']],
                array_merge($item, [
                    'agent_id' => $agent->id,
                    'project_id' => $project->id,
                    'province_id' => $jatim->id,
                    'city_id' => $surabaya->id,
                    'district_id' => $mulyorejo?->id,
                    'slug' => $slug,
                    'province' => 'Jawa Timur',
                    'city' => 'Surabaya',
                    'district' => 'Mulyorejo',
                    'address' => 'Apartemen Puncak Dharmahusada, Jl. Dr. Ir. H. Soekarno No. 99 (MERR), Kalijudan, Surabaya',
                    'facilities' => [
                        'Akses Langsung Kolam Renang & Gym',
                        'Ruko Komersial Lantai Dasar',
                        'Access Card 24 Jam',
                        'Food Court & Cafe',
                        'Keamanan 24 Jam & CCTV',
                    ],
                    'nearby_points' => [
                        '3 Menit ke Kampus C UNAIR',
                        '5 Menit ke Kampus ITS Sukolilo',
                        '5 Menit ke Galaxy Mall Surabaya',
                        '7 Menit ke RS Haji & RS Onkologi',
                    ],
                    'is_featured' => $idx < 3,
                    'is_new_launch' => false,
                    'is_verified' => true,
                    'status' => 'active',
                    'views_count' => rand(200, 1100),
                ])
            );
        }
    }
}

