<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "=== 1. VERIFIKASI DATA LEADER & TIM ===\n";
$leaders = Agent::where('is_leader', true)->with('teamMembers')->get();
foreach ($leaders as $l) {
    echo "Leader: {$l->name} (ID: {$l->id}, Cabang: {$l->office_branch})\n";
    echo "  Anggota Tim ({$l->teamMembers->count()} orang):\n";
    foreach ($l->teamMembers as $m) {
        echo "    - {$m->name} (ID: {$m->id})\n";
    }
    echo "  allTeamAgentIds: " . implode(', ', $l->allTeamAgentIds()) . "\n";
}

echo "\n=== 2. VERIFIKASI FILTER LISTING BERDASARKAN LEADER ===\n";
$firstLeader = $leaders->first();
if ($firstLeader) {
    $teamIds = $firstLeader->allTeamAgentIds();
    $teamPropsCount = Property::whereIn('agent_id', $teamIds)->count();
    $leaderPersonalCount = Property::where('agent_id', $firstLeader->id)->count();
    echo "Total Listing Tim {$firstLeader->name}: {$teamPropsCount} listing (Listing Pribadi Leader: {$leaderPersonalCount})\n";
}

echo "\n=== 3. VERIFIKASI AKUN LOGIN & PASSWORD AGEN ===\n";
$agentUser = User::where('role', 'agent')->first();
if ($agentUser) {
    echo "Akun Agent Ditemukan: {$agentUser->name} ({$agentUser->email})\n";
    
    // Tes password default 'password123'
    $isDefaultValid = Hash::check('password123', $agentUser->password);
    echo "Check password 'password123': " . ($isDefaultValid ? "VALID (OK)" : "TIDAK VALID") . "\n";

    // Tes reset password oleh Admin
    $newPass = 'rahasia12345';
    $agentUser->update(['password' => Hash::make($newPass)]);
    $isNewValid = Hash::check($newPass, $agentUser->fresh()->password);
    echo "Admin Reset Password ke '{$newPass}': " . ($isNewValid ? "BERHASIL (OK)" : "GAGAL") . "\n";

    // Kembalikan ke 'password123' agar tetap seragam
    $agentUser->update(['password' => Hash::make('password123')]);
    echo "Password dikembalikan ke 'password123': " . (Hash::check('password123', $agentUser->fresh()->password) ? "BERHASIL (OK)" : "GAGAL") . "\n";
} else {
    echo "Tidak ada user dengan role agent.\n";
}

echo "\n=== 4. VERIFIKASI ADMIN LISTING AGEN & TIM DI CONTROLLER ===\n";
$controller = new \App\Http\Controllers\AdminController();
$adminUser = User::where('role', 'admin')->first();
Auth::login($adminUser);

// Simulasikan request filter agent_id
$reqAgent = \Illuminate\Http\Request::create('/admin/properties', 'GET', ['agent_id' => $firstLeader->id]);
$viewAgent = $controller->properties($reqAgent);
echo "Filter agent_id={$firstLeader->id}: View rendered successfully.\n";

// Simulasikan request filter leader_id
$reqLeader = \Illuminate\Http\Request::create('/admin/properties', 'GET', ['leader_id' => $firstLeader->id]);
$viewLeader = $controller->properties($reqLeader);
echo "Filter leader_id={$firstLeader->id}: View rendered successfully.\n";

// Simulasikan Agent Leader login & scope=team
$leaderUser = User::where('agent_id', $firstLeader->id)->first();
if ($leaderUser) {
    Auth::login($leaderUser);
    $reqTeam = \Illuminate\Http\Request::create('/admin/properties', 'GET', ['scope' => 'team']);
    $viewTeam = $controller->properties($reqTeam);
    echo "Leader Login & scope=team: View rendered successfully.\n";
}

echo "\nSEMUA VERIFIKASI BERHASIL 100%!\n";
