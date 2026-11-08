<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Database\Seeder;

class DummyPropertySeeder extends Seeder
{
    /**
     * Run the dummy property seeders.
     * Can be invoked manually via: php artisan db:seed --class=DummyPropertySeeder
     */
    public function run(): void
    {
        // 1. Import Property Listings directly from Excel file (if present)
        if (file_exists(database_path('data/Data_Listing_Properti.xlsx'))) {
            $this->command->call('app:import-excel-properties');
        }

        // 2. Seed Puncak Dharmahusada Listings (Apartemen & Ruko)
        $this->call(PuncakDharmahusadaSeeder::class);

        // 3. Seed Citraland House Listing (Rumah Minimalis Full Marmer)
        $this->call(CitralandHouseSeeder::class);

        // 4. Seed Pakuwon City Land Listing (Kavling San Antonio BU)
        $this->call(PakuwonCityLandSeeder::class);

        // 5. Recalculate agent listings count
        foreach (Agent::all() as $agent) {
            $count = $agent->properties()->count();
            $agent->update([
                'active_listings_count' => $count,
                'listing_quota' => max($agent->listing_quota ?? 3, $count + 3),
            ]);
        }

        // 6. Seed sample inquiry if a property exists
        $sampleProp = Property::first();
        if ($sampleProp && Inquiry::count() === 0) {
            Inquiry::create([
                'property_id' => $sampleProp->id,
                'agent_id' => $sampleProp->agent_id,
                'name' => 'Bambang Soeprapto',
                'email' => 'bambang.s@gmail.com',
                'phone' => '081234567890',
                'message' => 'Halo, saya tertarik dengan listing ini. Apakah bisa dijadwalkan survey lokasi hari Sabtu ini?',
                'schedule_survey_date' => now()->addDays(2),
                'inquiry_type' => 'survey',
                'status' => 'new',
            ]);
        }
    }
}

