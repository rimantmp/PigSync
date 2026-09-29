<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\BirthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeathController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedTypeController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\PenController;
use App\Http\Controllers\PigBreedController;
use App\Http\Controllers\PigController;
use App\Http\Controllers\PigPhaseController;
use App\Http\Controllers\PopulationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReproductionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WeighingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'branch.scope'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master data
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('areas', AreaController::class)->except('show');
    Route::resource('pens', PenController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::resource('breeds', PigBreedController::class)->except('show');
    Route::resource('phases', PigPhaseController::class)->except('show');
    Route::resource('feed-types', FeedTypeController::class)->except('show');
    Route::resource('medicines', MedicineController::class)->except('show');
    Route::resource('diseases', DiseaseController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');

    // Peternakan
    Route::resource('pigs', PigController::class);
    Route::get('population', [PopulationController::class, 'index'])->name('population.index');
    Route::resource('weighings', WeighingController::class)->only(['index', 'create', 'store']);
    Route::resource('movements', MovementController::class)->only(['index', 'create', 'store']);
    Route::resource('health', HealthController::class)->only(['index', 'create', 'store']);
    Route::post('health/{pig}/recover', [HealthController::class, 'recover'])->name('health.recover');
    Route::resource('reproduction', ReproductionController::class)->only(['index']);
    Route::get('reproduction/mating', [ReproductionController::class, 'createMating'])->name('reproduction.mating');
    Route::post('reproduction/mating', [ReproductionController::class, 'storeMating'])->name('reproduction.mating.store');
    Route::get('reproduction/{breeding}/pregnancy', [ReproductionController::class, 'createPregnancy'])->name('reproduction.pregnancy');
    Route::post('reproduction/{breeding}/pregnancy', [ReproductionController::class, 'storePregnancy'])->name('reproduction.pregnancy.store');
    Route::resource('births', BirthController::class)->only(['index', 'create', 'store']);
    Route::resource('deaths', DeathController::class)->only(['index', 'create', 'store']);

    // Pakan
    Route::get('feeds', [FeedController::class, 'index'])->name('feeds.index');
    Route::post('feeds', [FeedController::class, 'store'])->name('feeds.store');
});

// Profil — cukup auth, tidak perlu scope cabang
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
