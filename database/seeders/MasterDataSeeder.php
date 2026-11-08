<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Project;
use App\Models\Property;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Provinces
        $provincesData = [
            ['name' => 'Jawa Timur', 'slug' => 'jawa-timur'],
            ['name' => 'DKI Jakarta', 'slug' => 'dki-jakarta'],
            ['name' => 'Banten', 'slug' => 'banten'],
            ['name' => 'Jawa Barat', 'slug' => 'jawa-barat'],
            ['name' => 'Bali', 'slug' => 'bali'],
        ];

        $provinces = [];
        foreach ($provincesData as $prov) {
            $provinces[$prov['slug']] = Province::firstOrCreate(['slug' => $prov['slug']], $prov);
        }

        // 2. Cities
        $citiesData = [
            ['province_id' => $provinces['jawa-timur']->id, 'name' => 'Surabaya', 'slug' => 'surabaya'],
            ['province_id' => $provinces['dki-jakarta']->id, 'name' => 'Jakarta Selatan', 'slug' => 'jakarta-selatan'],
            ['province_id' => $provinces['dki-jakarta']->id, 'name' => 'Jakarta Utara', 'slug' => 'jakarta-utara'],
            ['province_id' => $provinces['dki-jakarta']->id, 'name' => 'Jakarta Pusat', 'slug' => 'jakarta-pusat'],
            ['province_id' => $provinces['banten']->id, 'name' => 'Tangerang Selatan', 'slug' => 'tangerang-selatan'],
            ['province_id' => $provinces['banten']->id, 'name' => 'Tangerang', 'slug' => 'tangerang'],
            ['province_id' => $provinces['jawa-barat']->id, 'name' => 'Bandung', 'slug' => 'bandung'],
            ['province_id' => $provinces['jawa-barat']->id, 'name' => 'Bogor', 'slug' => 'bogor'],
            ['province_id' => $provinces['bali']->id, 'name' => 'Badung', 'slug' => 'badung'],
        ];

        $cities = [];
        foreach ($citiesData as $city) {
            $cities[$city['slug']] = City::firstOrCreate(['slug' => $city['slug']], $city);
        }

        // 3. Districts
        $districtsData = [
            // Surabaya
            ['city_id' => $cities['surabaya']->id, 'name' => 'Dukuh Pakis', 'slug' => 'dukuh-pakis'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Tegalsari', 'slug' => 'tegalsari'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Sambikerep', 'slug' => 'sambikerep'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Sukolilo', 'slug' => 'sukolilo'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Wiyung', 'slug' => 'wiyung'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Gubeng', 'slug' => 'gubeng'],
            ['city_id' => $cities['surabaya']->id, 'name' => 'Mulyorejo', 'slug' => 'mulyorejo'],
            // Jakarta Selatan
            ['city_id' => $cities['jakarta-selatan']->id, 'name' => 'Kebayoran Baru', 'slug' => 'kebayoran-baru'],
            ['city_id' => $cities['jakarta-selatan']->id, 'name' => 'Kebayoran Lama', 'slug' => 'kebayoran-lama'],
            ['city_id' => $cities['jakarta-selatan']->id, 'name' => 'Pondok Indah', 'slug' => 'pondok-indah'],
            ['city_id' => $cities['jakarta-selatan']->id, 'name' => 'Tebet', 'slug' => 'tebet'],
            ['city_id' => $cities['jakarta-selatan']->id, 'name' => 'Cilandak', 'slug' => 'cilandak'],
            // Tangerang Selatan
            ['city_id' => $cities['tangerang-selatan']->id, 'name' => 'BSD City', 'slug' => 'bsd-city'],
            ['city_id' => $cities['tangerang-selatan']->id, 'name' => 'Serpong', 'slug' => 'serpong'],
            // Bali
            ['city_id' => $cities['badung']->id, 'name' => 'Canggu', 'slug' => 'canggu'],
            ['city_id' => $cities['badung']->id, 'name' => 'Seminyak', 'slug' => 'seminyak'],
        ];

        $districts = [];
        foreach ($districtsData as $dist) {
            $districts[$dist['slug']] = District::firstOrCreate(['slug' => $dist['slug']], $dist);
        }

        // 4. Projects (Apartemen / Gedung / Cluster)
        $projectsData = [
            // Surabaya Apartemen (Sesuai contoh user: Apartemen A, B, C di Surabaya)
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['dukuh-pakis']->id,
                'name' => 'Pakuwon Mall Mansion (Tower Anderson / Benson)',
                'slug' => 'pakuwon-mall-mansion',
                'type' => 'apartemen',
                'developer' => 'Pakuwon Group',
                'address' => 'Jl. Puncak Indah Lontar No. 2, Dukuh Pakis, Surabaya',
                'total_floors' => 45,
                'year_completed' => 2021,
                'cover_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Direct Access Pakuwon Mall', 'Infinity Pool', 'Gym & Sauna', 'Sky Garden', 'Access Card & 24h Security'],
                'description' => 'Apartemen prestisius di Surabaya Barat tepat di atas Pakuwon Mall, mall terbesar di Indonesia.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['tegalsari']->id,
                'name' => 'One Icon Residence (Tunjungan Plaza 6)',
                'slug' => 'one-icon-residence',
                'type' => 'kondominium',
                'developer' => 'Pakuwon Group',
                'address' => 'Jl. Embong Malang No. 21-31, Tegalsari, Surabaya Pusat',
                'total_floors' => 52,
                'year_completed' => 2020,
                'cover_image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Direct Access Tunjungan Plaza 6', 'Concierge 24 Jam', 'Private Lift', 'Olympic Pool', 'Sky Lounge'],
                'description' => 'Kondominium ultra mewah di pusat kota Surabaya yang terintegrasi dengan Tunjungan Plaza.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['dukuh-pakis']->id,
                'name' => 'Grand Sungkono Lagoon (Tower Caspian)',
                'slug' => 'grand-sungkono-lagoon',
                'type' => 'apartemen',
                'developer' => 'PP Properti',
                'address' => 'Jl. KH Abdul Wahab Siamin Surabaya No. Kav 9-10',
                'total_floors' => 48,
                'year_completed' => 2022,
                'cover_image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Smart Home System', 'Lagoon Swimming Pool', 'Pocket Garden', 'Commercial Area'],
                'description' => 'Superblock hijau pertama di Surabaya Barat dengan konsep ramah lingkungan.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['sukolilo']->id,
                'name' => 'Puncak Kertajaya Apartment',
                'slug' => 'puncak-kertajaya-apartment',
                'type' => 'apartemen',
                'developer' => 'Puncak Group',
                'address' => 'Jl. Kertajaya Indah Regency, Sukolilo, Surabaya Timur',
                'total_floors' => 20,
                'year_completed' => 2018,
                'cover_image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Swimming Pool', 'Food Court', 'Parking Lot', 'Security 24H'],
                'description' => 'Apartemen strategis dekat kampus ITS dan Unair di Surabaya Timur, sangat favorit disewakan.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['mulyorejo']->id,
                'name' => 'Apartemen Puncak Dharmahusada',
                'slug' => 'apartemen-puncak-dharmahusada',
                'type' => 'apartemen',
                'developer' => 'Puncak Group',
                'address' => 'Jl. Dr. Ir. H. Soekarno No. 99 (MERR), Kalijudan, Mulyorejo, Surabaya Timur',
                'total_floors' => 42,
                'year_completed' => 2019,
                'cover_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Olympic Swimming Pool', 'Gym & Fitness Center', 'Ruko & Commercial Area', 'Access Card 24H', 'Food Court & Minimarket', 'Parkir Luas'],
                'description' => 'Apartemen modern di jalur prestisius MERR Surabaya Timur dengan tower A & B dan deretan ruko komersial di lantai dasar.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['sambikerep']->id,
                'name' => 'CitraLand Surabaya',
                'slug' => 'citraland-surabaya',
                'type' => 'residensial',
                'developer' => 'Ciputra Group',
                'address' => 'Jl. CitraLand Utama, Sambikerep, Surabaya Barat',
                'total_floors' => 2,
                'year_completed' => 2015,
                'cover_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Pasar Modern / Fresh Market', 'Sekolah Ciputra', 'Universitas Ciputra', 'Waterpark CitraLand', 'Clubhouse & Golf', 'Keamanan 24 Jam'],
                'description' => 'Kota mandiri modern bertaraf internasional "The Singapore of Surabaya" di Surabaya Barat.',
            ],
            [
                'city_id' => $cities['surabaya']->id,
                'district_id' => $districts['mulyorejo']->id,
                'name' => 'Pakuwon City (Surabaya Timur)',
                'slug' => 'pakuwon-city',
                'type' => 'residensial',
                'developer' => 'Pakuwon Group',
                'address' => 'Jl. Kejawan Putih Tambak, Mulyorejo, Surabaya Timur',
                'total_floors' => 2,
                'year_completed' => 2018,
                'cover_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['East Coast Center Mall', 'Pakuwon City Mall', 'San Antonio Shopping Street', 'Gloria Christian School', 'Cita Hati School', 'Keamanan 24 Jam'],
                'description' => 'Kota mandiri tepi pantai prestisius di Surabaya Timur dengan cluster mewah seperti San Antonio, Sorrento, Florence, dan Grand Island.',
            ],
            // Jakarta Apartemen
            [
                'city_id' => $cities['jakarta-selatan']->id,
                'district_id' => $districts['kebayoran-baru']->id,
                'name' => 'District 8 Senopati SCBD',
                'slug' => 'district-8-senopati-scbd',
                'type' => 'apartemen',
                'developer' => 'Agung Sedayu Group',
                'address' => 'SCBD Lot 28, Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan',
                'total_floors' => 58,
                'year_completed' => 2019,
                'cover_image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Direct Access ASHTA Mall', 'Private Lift', 'Indoor & Outdoor Pool', 'Fitness & Spa Center'],
                'description' => 'Apartemen paling bergengsi di kawasan financial district SCBD Jakarta.',
            ],
            [
                'city_id' => $cities['jakarta-selatan']->id,
                'district_id' => $districts['tebet']->id,
                'name' => 'Casa Grande Residence',
                'slug' => 'casa-grande-residence',
                'type' => 'apartemen',
                'developer' => 'Pakuwon Group',
                'address' => 'Jl. Casablanca Raya Kav. 88, Tebet, Jakarta Selatan',
                'total_floors' => 38,
                'year_completed' => 2018,
                'cover_image' => 'https://images.unsplash.com/photo-1502005229762-ee1b2da97e06?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['Direct Access Kota Kasablanka Mall', 'Resort Pool', 'Tennis Court', 'Gym'],
                'description' => 'Apartemen hunian mewah terintegrasi Mal Kota Kasablanka di segitiga emas Jakarta.',
            ],
        ];

        foreach ($projectsData as $proj) {
            Project::firstOrCreate(['slug' => $proj['slug']], $proj);
        }
    }
}

