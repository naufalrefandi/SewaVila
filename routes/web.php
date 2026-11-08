<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientRequestController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Frontend Routes
Route::get('/', [PropertyController::class, 'index'])->name('home');
Route::get('/properti', [PropertyController::class, 'search'])->name('properties.index');
Route::get('/dijual', function () {
    return redirect()->route('properties.index', ['listing_type' => 'dijual']);
})->name('properties.for-sale');
Route::get('/disewa', function () {
    return redirect()->route('properties.index', ['listing_type' => 'disewa']);
})->name('properties.for-rent');

Route::get('/proyek-baru', [PropertyController::class, 'newLaunch'])->name('properties.new-launch');
Route::get('/kalkulator-kpr', [PropertyController::class, 'kpr'])->name('kpr');
Route::get('/bandingkan', [PropertyController::class, 'compare'])->name('properties.compare');
Route::get('/favorit', [PropertyController::class, 'favorites'])->name('properties.favorites');

Route::get('/properti/{slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/p/{code}', [PropertyController::class, 'shortRedirect'])->name('properties.short');

// Agent Directory & Join as Agent
Route::get('/agen', [AgentController::class, 'index'])->name('agents.index');
Route::get('/agen/{slug}', [AgentController::class, 'show'])->name('agents.show');
Route::get('/gabung-agen', [AgentController::class, 'joinForm'])->name('agents.join');
Route::post('/gabung-agen', [AgentController::class, 'submitJoin'])->name('agents.join.store');
Route::get('/gabung-agen/pembayaran/{code}', [AgentController::class, 'showPayment'])->name('agents.join.invoice');
Route::post('/gabung-agen/pembayaran/{code}', [AgentController::class, 'submitPaymentProof'])->name('agents.join.payment');

// Owner Property Submission ("Titip Properti")
Route::get('/titip-properti', [SubmissionController::class, 'create'])->name('titip-properti');
Route::post('/titip-properti', [SubmissionController::class, 'store'])->name('titip-properti.store');
Route::get('/titip-properti/pembayaran/{code}', [SubmissionController::class, 'showPayment'])->name('titip-properti.invoice');
Route::post('/titip-properti/pembayaran/{code}', [SubmissionController::class, 'submitPaymentProof'])->name('titip-properti.payment');

// Lead & Inquiry Form Submission
Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');

// Dynamic Ajax/JSON API for Cascading Dropdowns
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/provinces', [ApiController::class, 'provinces'])->name('provinces');
    Route::get('/cities', [ApiController::class, 'cities'])->name('cities');
    Route::get('/districts', [ApiController::class, 'districts'])->name('districts');
    Route::get('/projects', [ApiController::class, 'projects'])->name('projects');
});

// Protected Admin Panel (Requires Login)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/properties', [AdminController::class, 'properties'])->name('properties');
    Route::get('/properties/create', [AdminController::class, 'createProperty'])->name('properties.create');
    Route::get('/properties/generate-code', [AdminController::class, 'generatePropertyCode'])->name('properties.generate-code');
    Route::post('/properties', [AdminController::class, 'storeProperty'])->name('properties.store');
    Route::get('/properties/{property}/edit', [AdminController::class, 'editProperty'])->name('properties.edit');
    Route::put('/properties/{property}', [AdminController::class, 'updateProperty'])->name('properties.update');
    Route::post('/properties/{property}/duplicate', [AdminController::class, 'duplicateProperty'])->name('properties.duplicate');
    Route::delete('/properties/{property}', [AdminController::class, 'destroyProperty'])->name('properties.destroy');

    // Owner Property Submission ("Titip Properti")
    // Tenant & Lease Management (Pencatatan Data Penyewa & Deposit)
    Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/create', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/rentals/{rental}', [RentalController::class, 'show'])->name('rentals.show');
    Route::get('/rentals/{rental}/edit', [RentalController::class, 'edit'])->name('rentals.edit');
    Route::put('/rentals/{rental}', [RentalController::class, 'update'])->name('rentals.update');
    Route::post('/rentals/{rental}/complete', [RentalController::class, 'complete'])->name('rentals.complete');
    Route::post('/rentals/{rental}/extend', [RentalController::class, 'extend'])->name('rentals.extend');
    Route::delete('/rentals/{rental}', [RentalController::class, 'destroy'])->name('rentals.destroy');

    // Quick Apartment Stock Sheet & WhatsApp Quick Share
    Route::get('/apartment-stock', [AdminController::class, 'apartmentStock'])->name('apartment-stock');
    Route::post('/apartment-stock', [AdminController::class, 'storeQuickApartmentUnit'])->name('apartment-stock.store');
    Route::put('/apartment-stock/{property}/quick-update', [AdminController::class, 'updateQuickApartmentUnit'])->name('apartment-stock.quick-update');

    // Property Matcher & Client Requests CRM (Carikan Properti & Database Kebutuhan Klien)
    Route::get('/client-requests', [ClientRequestController::class, 'index'])->name('client-requests.index');
    Route::post('/client-requests', [ClientRequestController::class, 'store'])->name('client-requests.store');
    Route::get('/client-requests/{clientRequest}', [ClientRequestController::class, 'show'])->name('client-requests.show');
    Route::put('/client-requests/{clientRequest}', [ClientRequestController::class, 'update'])->name('client-requests.update');
    Route::delete('/client-requests/{clientRequest}', [ClientRequestController::class, 'destroy'])->name('client-requests.destroy');
    Route::post('/client-requests/{clientRequest}/follow-up', [ClientRequestController::class, 'markFollowedUp'])->name('client-requests.follow-up');

    // Master Data Wilayah (Provinsi, Kota, Kecamatan)
    Route::get('/master/locations', [AdminController::class, 'masterLocations'])->name('master.locations');
    Route::post('/master/provinces', [AdminController::class, 'storeProvince'])->name('master.provinces.store');
    Route::delete('/master/provinces/{province}', [AdminController::class, 'destroyProvince'])->name('master.provinces.destroy');
    Route::post('/master/cities', [AdminController::class, 'storeCity'])->name('master.cities.store');
    Route::delete('/master/cities/{city}', [AdminController::class, 'destroyCity'])->name('master.cities.destroy');
    Route::post('/master/districts', [AdminController::class, 'storeDistrict'])->name('master.districts.store');
    Route::delete('/master/districts/{district}', [AdminController::class, 'destroyDistrict'])->name('master.districts.destroy');

    // Master Data Proyek / Gedung / Apartemen
    Route::get('/master/projects', [AdminController::class, 'masterProjects'])->name('master.projects');
    Route::post('/master/projects', [AdminController::class, 'storeProject'])->name('master.projects.store');
    Route::delete('/master/projects/{project}', [AdminController::class, 'destroyProject'])->name('master.projects.destroy');

    // Agent Registrations / Applications
    Route::get('/agent-applications', [AdminController::class, 'agentApplications'])->name('agent-applications');
    Route::post('/agent-applications/{application}/approve', [AdminController::class, 'approveAgentApplication'])->name('agent-applications.approve');
    Route::post('/agent-applications/{application}/reject', [AdminController::class, 'rejectAgentApplication'])->name('agent-applications.reject');

    // Leads & Inquiries
    Route::get('/inquiries', [AdminController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{inquiry}/status', [AdminController::class, 'updateInquiryStatus'])->name('inquiries.status');

    // Owner Submissions (Titip Properti)
    Route::get('/submissions', [AdminController::class, 'submissions'])->name('submissions');
    Route::post('/submissions/{submission}/status', [AdminController::class, 'updateSubmissionStatus'])->name('submissions.status');
    Route::post('/submissions/{submission}/verify-payment', [AdminController::class, 'verifySubmissionPayment'])->name('submissions.verify-payment');

    // Agents & Leader Teams Management
    Route::get('/agents', [AdminController::class, 'agentsIndex'])->name('agents.index');
    Route::put('/agents/{agent}', [AdminController::class, 'updateAgent'])->name('agents.update');
    Route::post('/agents/{agent}/reset-password', [AdminController::class, 'resetAgentPassword'])->name('agents.reset-password');

    // Agent Quota & Featured Listing Transactions
    Route::get('/agent/topup', [AdminController::class, 'agentTopup'])->name('agent.topup');
    Route::post('/agent/topup-quota', [AdminController::class, 'requestQuotaTopup'])->name('agent.topup-quota');
    Route::post('/properties/{property}/request-featured', [AdminController::class, 'requestFeaturedListing'])->name('properties.request-featured');
    Route::get('/agent-transactions', [AdminController::class, 'agentTransactions'])->name('agent-transactions');
    Route::post('/agent-transactions/{transaction}/approve', [AdminController::class, 'approveAgentTransaction'])->name('agent-transactions.approve');
    Route::post('/agent-transactions/{transaction}/reject', [AdminController::class, 'rejectAgentTransaction'])->name('agent-transactions.reject');

    // Global Settings (Pricing, QRIS, Quotas)
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Profile & Password Management
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::match(['put', 'post'], '/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
});
