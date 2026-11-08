<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\City;
use App\Models\District;
use App\Models\Project;
use App\Models\Property;
use App\Models\Province;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZipArchive;

class ImportExcelProperties extends Command
{
    protected $signature = 'app:import-excel-properties {--file=database/data/Data_Listing_Properti.xlsx}';
    protected $description = 'Import property listings from Data_Listing_Properti.xlsx';

    public function handle(): int
    {
        // 0. Clean up any property listings that do not have property_code (not from Excel)
        $deletedOld = Property::whereNull('property_code')->delete();
        if ($deletedOld > 0) {
            $this->info("Menghapus {$deletedOld} listing bawaan seeder lama yang bukan dari Excel.");
        }

        $filePath = $this->option('file');
        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return 1;
        }

        $this->info("Membaca file Excel: {$filePath}...");

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            $this->error("Gagal membuka file Excel.");
            return 1;
        }

        // 1. Shared Strings
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string)$si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        // 2. Sheet 1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $this->error("Tidak menemukan data sheet1.");
            $zip->close();
            return 1;
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowCells = [];
            foreach ($row->c as $c) {
                $attr = $c->attributes();
                $cellRef = (string)$attr['r'];
                $col = preg_replace('/[0-9]/', '', $cellRef);
                $type = (string)$attr['t'];
                $val = isset($c->v) ? (string)$c->v : '';
                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                }
                $rowCells[$col] = $val;
            }
            if (!empty($rowCells['E']) && $rowCells['E'] !== 'Judul Iklan') {
                $rows[] = $rowCells;
            }
        }
        $zip->close();

        $this->info("Menemukan " . count($rows) . " listing dari Excel. Memproses data...");

        // Ensure Province Jawa Timur & City Surabaya exist
        $jatim = Province::firstOrCreate(
            ['slug' => 'jawa-timur'],
            ['name' => 'Jawa Timur']
        );

        $surabaya = City::firstOrCreate(
            ['slug' => 'surabaya'],
            ['province_id' => $jatim->id, 'name' => 'Surabaya']
        );

        // Ensure sample agent in Surabaya exists
        $agent = Agent::where('office_branch', 'like', '%Surabaya%')->first() ?? Agent::first();
        if (!$agent) {
            $agent = Agent::firstOrCreate(
                ['slug' => 'budi-santoso-surabaya'],
                [
                    'name' => 'Budi Santoso',
                    'email' => 'budi.santoso@yproperti.com',
                    'phone' => '081234567891',
                    'whatsapp' => '6281234567891',
                    'agency_name' => 'YProperti',
                    'office_branch' => 'Surabaya West Office',
                    'position' => 'Senior Property Advisor',
                    'experience_years' => 7,
                    'rating' => 4.9,
                    'active_listings_count' => 19,
                    'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                    'bio' => 'Spesialis properti residensial, apartemen, dan tanah komersial di kawasan Surabaya Barat dan Surabaya Timur.',
                    'is_verified' => true,
                    'is_top_producer' => true,
                ]
            );
        }

        // Projects in Surabaya
        $pakuwonMansion = Project::firstOrCreate(
            ['slug' => 'pakuwon-mall-mansion'],
            [
                'city_id' => $surabaya->id,
                'name' => 'Pakuwon Mall Mansion (Tower Anderson / Benson)',
                'type' => 'apartemen',
                'developer' => 'Pakuwon Group',
                'address' => 'Jl. Puncak Indah Lontar No. 2, Dukuh Pakis, Surabaya',
                'total_floors' => 45,
                'year_completed' => 2020,
                'cover_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                'facilities' => ['Direct Access Pakuwon Mall', 'Infinity Pool', 'Gym & Sauna', 'Sky Garden', 'Access Card 24H'],
                'description' => 'Apartemen prestisius tepat di atas Pakuwon Mall, mall terbesar di Indonesia.',
            ]
        );

        $baleHinggil = Project::firstOrCreate(
            ['slug' => 'apartemen-bale-hinggil'],
            [
                'city_id' => $surabaya->id,
                'name' => 'Apartemen Bale Hinggil MERR',
                'type' => 'apartemen',
                'developer' => 'Tata Bumi Raya',
                'address' => 'Jl. Dr. Ir. H. Soekarno No. 32, Sukolilo, Surabaya Timur',
                'total_floors' => 30,
                'year_completed' => 2018,
                'cover_image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80',
                'facilities' => ['Swimming Pool', 'Gym', 'Commercial Area', 'Access Card', 'Parkir Luas'],
                'description' => 'Apartemen strategis di jalur MERR dekat kampus ternama Surabaya.',
            ]
        );

        $avenue88 = Project::firstOrCreate(
            ['slug' => '88-avenue-surabaya'],
            [
                'city_id' => $surabaya->id,
                'name' => '88 Avenue Superblock Surabaya',
                'type' => 'apartemen',
                'developer' => 'Waskita Karya & Megabuilder Prima',
                'address' => 'Jl. Raya Darmo Permai III, Sukomanunggal, Surabaya',
                'total_floors' => 32,
                'year_completed' => 2021,
                'cover_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
                'facilities' => ['Grand Lobby', 'Sky Garden', 'Infinity Pool', 'Gym', 'Smart Home System'],
                'description' => 'Superblock mewah rancangan arsitek dunia Tange Associates di Surabaya Barat.',
            ]
        );

        // Dummy curated gallery sets
        $apartmentGalleries = [
            [
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1502005229762-ee1b2b81b249?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'https://images.unsplash.com/photo-1502672023488-70e25813eb80?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $houseGalleries = [
            [
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1613977257363-707ba9348227?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $landGalleries = [
            [
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1500076656116-558758c991c1?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $importedCount = 0;

        foreach ($rows as $idx => $r) {
            $code = trim($r['B'] ?? '');
            $kategoriRaw = strtolower(trim($r['C'] ?? 'apartemen'));
            $tipeIklanRaw = strtolower(trim($r['D'] ?? 'jual'));
            $rawTitle = trim($r['E'] ?? '');
            $hargaJual = (float)($r['F'] ?? 0);
            $hargaSewa = (float)($r['G'] ?? 0);
            $statusRaw = strtolower(trim($r['H'] ?? 'tersedia'));
            $ownerName = trim($r['I'] ?? '');
            $ownerPhone = trim($r['J'] ?? '');

            // Clean title (remove shouting prefixes)
            $cleanTitle = preg_replace('/^(BUYER ONLY\s*!*|RENTER ONLY\s*!*)\s*/i', '', $rawTitle);
            $cleanTitle = trim($cleanTitle);

            // Skip listing Jual Sewa (sesuai instruksi: opsi jual sewa dihapus, hanya ada Dijual atau Disewa)
            if ((str_contains($tipeIklanRaw, 'sewa') && str_contains($tipeIklanRaw, 'jual')) || str_ends_with(strtoupper($code), 'JS')) {
                continue;
            }

            // Determine property type
            $propertyType = match ($kategoriRaw) {
                'tanah' => 'tanah',
                'rumah' => 'rumah',
                'gudang' => 'gudang',
                'ruko' => 'ruko',
                'villa' => 'villa',
                default => 'apartemen',
            };

            // Determine listing type & price
            if (str_contains($tipeIklanRaw, 'sewa') && !str_contains($tipeIklanRaw, 'jual')) {
                $listingType = 'disewa';
                $price = $hargaSewa > 0 ? (int)$hargaSewa : 25000000;
                $rentPeriod = 'tahun';
            } elseif (str_contains($tipeIklanRaw, 'jual')) {
                $listingType = 'dijual';
                $price = $hargaJual > 0 ? (int)$hargaJual : 500000000;
                $rentPeriod = null;
            } else {
                $listingType = $hargaJual > 0 ? 'dijual' : 'disewa';
                $price = $hargaJual > 0 ? (int)$hargaJual : ($hargaSewa > 0 ? (int)$hargaSewa : 500000000);
                $rentPeriod = $listingType === 'disewa' ? 'tahun' : null;
            }

            // Status
            $status = ($statusRaw === 'disewa' || $statusRaw === 'tersewa') ? 'rented' : 'active';

            // Gallery selection
            if ($propertyType === 'tanah') {
                $gallery = $landGalleries[0];
            } elseif ($propertyType === 'rumah') {
                $gallery = $houseGalleries[$idx % count($houseGalleries)];
            } else {
                $gallery = $apartmentGalleries[$idx % count($apartmentGalleries)];
            }

            // Project link detection
            $projectId = null;
            if (str_contains(strtolower($cleanTitle), 'pakuwon mall') ||
                str_contains(strtolower($cleanTitle), 'anderson') ||
                str_contains(strtolower($cleanTitle), 'tanglin') ||
                str_contains(strtolower($cleanTitle), 'orchard') ||
                str_contains(strtolower($cleanTitle), 'la riz')) {
                $projectId = $pakuwonMansion->id;
            } elseif (str_contains(strtolower($cleanTitle), 'bale hinggil')) {
                $projectId = $baleHinggil->id;
            } elseif (str_contains(strtolower($cleanTitle), '88 avenue')) {
                $projectId = $avenue88->id;
            }

            // District heuristic
            $district = 'Dukuh Pakis';
            if (str_contains(strtolower($cleanTitle), 'pakuwon city') || str_contains(strtolower($cleanTitle), 'bale hinggil')) {
                $district = 'Sukolilo';
            } elseif (str_contains(strtolower($cleanTitle), 'grand pakuwon')) {
                $district = 'Tandes';
            } elseif (str_contains(strtolower($cleanTitle), '88 avenue')) {
                $district = 'Sukomanunggal';
            } elseif (str_contains(strtolower($cleanTitle), 'puncak permai')) {
                $district = 'Sukomanunggal';
            }

            // Address
            $address = $cleanTitle . ', ' . $district . ', Surabaya';

            // Facilities
            $facilities = match ($propertyType) {
                'tanah' => ['Row Jalan Lebar 4 Mobil', 'Bebas Banjir', 'Akses Dekat Komersial Pakuwon City', 'Keamanan 24 Jam'],
                'rumah' => ['Full Furnished Siap Pakai', 'AC & Kitchen Set', 'Carport 2 Mobil', 'One Gate System 24 Jam', 'Club House'],
                default => ['Akses Mall Terhubung Langsung', 'Swimming Pool & Gym', 'Full Furnished Siap Huni', 'Access Card 24 Jam', 'View Bagus'],
            };

            // Description
            $desc = "{$cleanTitle}\n\n"
                  . "Kode Listing: {$code}\n"
                  . "Lokasi: {$district}, Kota Surabaya, Jawa Timur\n"
                  . "Status: " . ucfirst($status) . " (" . strtoupper($listingType) . ")\n"
                  . ($ownerName ? "Kontak Referensi Owner: {$ownerName} " . ($ownerPhone ? "({$ownerPhone})" : "") . "\n\n" : "\n")
                  . "Spesifikasi & Keunggulan:\n"
                  . "- Lokasi sangat strategis di Surabaya dengan akses mudah ke pusat perbelanjaan, tol, rumah sakit, dan sekolah internasional.\n"
                  . "- Lingkungan aman, nyaman, dan terawat dengan sistem keamanan 24 jam.\n"
                  . "- Siap huni / siap pakai untuk kebutuhan pribadi maupun investasi bernilai tinggi.\n\n"
                  . "Untuk jadwal survey lokasi & penawaran harga terbaik, hubungi konsultan resmi kami.";

            // Generate unique slug
            $existingProp = Property::where('property_code', $code)->first();
            if ($existingProp) {
                $slug = $existingProp->slug;
            } else {
                $slugBase = Str::slug($cleanTitle);
                if (empty($slugBase)) {
                    $slugBase = 'properti-surabaya-' . strtolower($code);
                }
                $slug = $slugBase;
                $c = 1;
                while (Property::where('slug', $slug)->exists()) {
                    $slug = "{$slugBase}-{$c}";
                    $c++;
                }
            }

            Property::updateOrCreate(
                ['property_code' => $code],
                [
                    'owner_name' => $ownerName ?: null,
                    'owner_phone' => $ownerPhone ?: null,
                    'agent_id' => $agent->id,
                    'project_id' => $projectId,
                    'province_id' => $jatim->id,
                    'city_id' => $surabaya->id,
                    'title' => $cleanTitle,
                    'slug' => $slug,
                    'property_type' => $propertyType,
                    'listing_type' => $listingType,
                    'price' => $price,
                    'price_prefix' => 'Nego',
                    'rent_period' => $rentPeriod,
                    'bedrooms' => $propertyType === 'tanah' ? null : ($propertyType === 'rumah' ? 3 : 2),
                    'bathrooms' => $propertyType === 'tanah' ? null : ($propertyType === 'rumah' ? 2 : 1),
                    'land_area' => $propertyType === 'tanah' ? 216 : ($propertyType === 'rumah' ? 120 : null),
                    'building_area' => $propertyType === 'tanah' ? null : ($propertyType === 'rumah' ? 140 : 45),
                    'carports' => $propertyType === 'tanah' ? null : 1,
                    'floors' => $propertyType === 'rumah' ? 2 : 1,
                    'certificate' => $propertyType === 'tanah' ? 'PPJB Siap AJB' : ($propertyType === 'rumah' ? 'SHM' : 'Strata Title'),
                    'electricity' => $propertyType === 'tanah' ? null : 2200,
                    'facing' => str_contains(strtolower($cleanTitle), 'utara') ? 'Utara' : (str_contains(strtolower($cleanTitle), 'selatan') ? 'Selatan' : 'Timur'),
                    'furnished_status' => str_contains(strtolower($cleanTitle), 'full furnish') ? 'fully-furnished' : 'semi-furnished',
                    'province' => 'Jawa Timur',
                    'city' => 'Surabaya',
                    'district' => $district,
                    'address' => $address,
                    'description' => $desc,
                    'images' => $gallery,
                    'facilities' => $facilities,
                    'nearby_points' => [
                        '5 Menit ke Pakuwon Mall / Tunjungan Plaza',
                        '7 Menit ke Akses Jalan Tol Satelit Surabaya',
                        'Dekat Sekolah Internasional & Rumah Sakit',
                    ],
                    'is_featured' => $idx < 4,
                    'is_new_launch' => false,
                    'is_verified' => true,
                    'status' => $status,
                    'views_count' => rand(150, 950),
                ]
            );

            $importedCount++;
            $this->line(" [{$importedCount}/" . count($rows) . "] Berhasil import: {$code} - {$cleanTitle}");
        }

        $this->info("SELESAI! Berhasil mengimpor {$importedCount} listing dari Excel ke database.");
        return 0;
    }
}

