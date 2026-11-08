<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\PropertySubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@yproperti.com'],
            [
                'name' => 'Administrator YProperti',
                'password' => bcrypt('password123'),
            ]
        );

        // 1. Seed Agents
        $agentsData = [
            [
                'name' => 'Hendra Wijaya',
                'slug' => 'hendra-wijaya',
                'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=600&q=80',
                'email' => 'hendra.wijaya@yproperti.com',
                'phone' => '081298765432',
                'whatsapp' => '081298765432',
                'agency_name' => 'PropNex Indonesia',
                'office_branch' => 'PropNex Grand CBD Jakarta',
                'position' => 'Senior Vice President',
                'experience_years' => 12,
                'rating' => 4.9,
                'active_listings_count' => 18,
                'bio' => 'Berpengalaman lebih dari 12 tahun dalam transaksi luxury real estate di area premium Jakarta Selatan dan Pusat. Penghargaan Top Producer PropNex Indonesia.',
                'is_verified' => true,
                'is_top_producer' => true,
            ],
            [
                'name' => 'Maria Fransiska',
                'slug' => 'maria-fransiska',
                'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80',
                'email' => 'maria.fransiska@yproperti.com',
                'phone' => '08118889922',
                'whatsapp' => '08118889922',
                'agency_name' => 'PropNex Indonesia',
                'office_branch' => 'PropNex BSD & Gading Serpong',
                'position' => 'Executive Associate',
                'experience_years' => 7,
                'rating' => 4.9,
                'active_listings_count' => 24,
                'bio' => 'Spesialis properti primary launch dan secondary market di kawasan BSD City, Alam Sutera, dan Gading Serpong. Melayani konsultasi KPR gratis.',
                'is_verified' => true,
                'is_top_producer' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'slug' => 'budi-santoso',
                'photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=600&q=80',
                'email' => 'budi.santoso@yproperti.com',
                'phone' => '081333444555',
                'whatsapp' => '081333444555',
                'agency_name' => 'PropNex Indonesia',
                'office_branch' => 'PropNex Surabaya West',
                'position' => 'Branch Director',
                'experience_years' => 10,
                'rating' => 4.8,
                'active_listings_count' => 15,
                'bio' => 'Fokus pada residensial dan komersial premium di Surabaya Barat (CitraLand, Pakuwon Indah, Graha Famili).',
                'is_verified' => true,
                'is_top_producer' => false,
            ],
            [
                'name' => 'Putu Arya Wirawan',
                'slug' => 'putu-arya-wirawan',
                'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
                'email' => 'putu.arya@yproperti.com',
                'phone' => '081999887766',
                'whatsapp' => '081999887766',
                'agency_name' => 'PropNex Indonesia',
                'office_branch' => 'PropNex Bali Sunset Road',
                'position' => 'Luxury Villa Consultant',
                'experience_years' => 8,
                'rating' => 5.0,
                'active_listings_count' => 12,
                'bio' => 'Spesialis investasi luxury villa, resort, dan beachfront properties di Canggu, Seminyak, Uluwatu, dan Ubud Bali.',
                'is_verified' => true,
                'is_top_producer' => true,
            ],
        ];

        $createdAgents = [];
        foreach ($agentsData as $agentInfo) {
            $createdAgents[] = Agent::updateOrCreate(['slug' => $agentInfo['slug']], $agentInfo);
        }

        // 2. Seed Master Data: Provinces, Cities, Districts, Apartment Projects
        $this->call(MasterDataSeeder::class);

        // Note: Property listings are decoupled from default seeding as requested.
        // If in testing environment, seed properties so test suites have fixtures.
        // In local/production, properties can be seeded manually with:
        // php artisan db:seed --class=DummyPropertySeeder
        if (app()->environment('testing')) {
            $this->call(DummyPropertySeeder::class);
        }

        // 3. Update actual active listing count on agents, setup leaders & create agent login accounts
        $hendra = Agent::where('email', 'hendra.wijaya@yproperti.com')->first();
        $maria = Agent::where('email', 'maria.fransiska@yproperti.com')->first();
        $budi = Agent::where('email', 'budi.santoso@yproperti.com')->first();
        $putu = Agent::where('email', 'putu.arya@yproperti.com')->first();

        if ($hendra) {
            $hendra->update(['is_leader' => true, 'position' => 'Team Leader / Senior Business Partner']);
        }
        if ($maria) {
            $maria->update(['is_leader' => true, 'position' => 'Division Leader / Principal Consultant']);
        }
        if ($budi && $hendra) {
            $budi->update(['is_leader' => false, 'leader_id' => $hendra->id]);
        }
        if ($putu && $hendra) {
            $putu->update(['is_leader' => false, 'leader_id' => $hendra->id]);
        }

        foreach (Agent::all() as $agent) {
            $agent->update([
                'active_listings_count' => $agent->properties()->count(),
                'listing_quota' => max(3, $agent->properties()->count() + 3),
            ]);

            User::updateOrCreate(
                ['email' => $agent->email],
                [
                    'name' => $agent->name,
                    'password' => bcrypt('password123'),
                    'role' => 'agent',
                    'agent_id' => $agent->id,
                ]
            );
        }

        // 5. Seed Sample Inquiries & Submissions
        $sampleProp = Property::first();
        if ($sampleProp && Inquiry::count() === 0) {
            Inquiry::create([
                'property_id' => $sampleProp->id,
                'agent_id' => $sampleProp->agent_id ?? ($createdAgents[0]->id ?? null),
                'name' => 'Bambang Soeprapto',
                'email' => 'bambang.s@gmail.com',
                'phone' => '081234567890',
                'message' => 'Halo, saya tertarik dengan listing ini. Apakah bisa dijadwalkan survey lokasi hari Sabtu ini?',
                'schedule_survey_date' => now()->addDays(2),
                'inquiry_type' => 'survey',
                'status' => 'new',
            ]);
        }

        if (PropertySubmission::count() === 0) {
            PropertySubmission::create([
                'owner_name' => 'Dr. Rahmat Hidayat',
                'owner_phone' => '081809988776',
                'owner_email' => 'rahmat.hidayat@yahoo.com',
                'property_title' => 'Rumah 2 Lantai Siap Huni di Surabaya',
                'property_type' => 'rumah',
                'listing_type' => 'dijual',
                'expected_price' => 2800000000,
                'city' => 'Surabaya',
                'address' => 'Pakuwon City, Surabaya Timur',
                'land_area' => 160,
                'building_area' => 190,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'certificate' => 'SHM',
                'notes' => 'Mohon dibantu pasarkan oleh agen PropNex, rumah baru selesai renovasi.',
                'status' => 'pending',
            ]);
        }
    }
}
