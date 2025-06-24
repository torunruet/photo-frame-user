<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Frame;
use App\Events\PhotoUploaded;
use App\Http\Controllers\ImageUpload\ImageUploadController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FrameController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\Payment\SslCommerzPaymentController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\DeviceAuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.dashboard');

    // Frame Management Routes
    Route::resource('admin/frames', App\Http\Controllers\Admin\FrameController::class)->names([
        'index' => 'admin.frames.index',
        'create' => 'admin.frames.create',
        'store' => 'admin.frames.store',
        'edit' => 'admin.frames.edit',
        'update' => 'admin.frames.update',
        'destroy' => 'admin.frames.destroy',
    ]);
});
// Route::get('/', function () {
//     return view('main-view');
// });

// Device Authentication Routes
Route::get('/device/login', [DeviceAuthController::class, 'showLoginForm'])->name('device.login');
Route::post('/device/authenticate', [DeviceAuthController::class, 'authenticate'])->name('device.authenticate');
Route::post('/device/logout', [DeviceAuthController::class, 'logout'])->name('device.logout');

// Protect the front view route
Route::middleware(['device.auth'])->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('front.view');
});

Route::get('/start-session', [ImageUploadController::class, 'QrView'])->name('start.session');
Route::get('/upload-page/{session}', function ($session) {
    return view('QrCodeSection.uploadForm', ['sessionId' => $session]);
})->name('upload.page');

Route::post('/upload/{session}', [ImageUploadController::class, 'uploadMultiple'])->name('upload.mobile');

// Broadcast event for uploaded images
Broadcast::routes();

Route::get('/uploaded-images', function () {
    $images = Storage::disk('public')->files('uploads');
    return view('front.view', ['images' => $images]);
})->name('uploaded.images');

Route::post('/broadcast-refresh', [ImageUploadController::class, 'broadcastRefresh'])->name('broadcast.refresh');

Route::get('/rendering-image/{session}', [\App\Http\Controllers\ImageModifyController::class, 'ShowImage'])->name('rendering.image');
Route::post('/merge-frames', [\App\Http\Controllers\ImageModifyController::class, 'mergeFrames'])->name('merge.frames');




require __DIR__.'/auth.php';


Route::get('/take-photo/{session}', function ($session) {
    return view('take-photo', compact('session'));
})->name('take.photo');

Route::post('/upload-photos', [\App\Http\Controllers\ImageModifyController::class, 'uploadPhotos'])->name('upload.photos');



Route::get('/print', function () {
    return view('print');
});

Route::get('/take-photo', function () {
    $session = Str::uuid(); // or use time() for timestamp
    return view('take-photo', compact('session'));
})->name('take.photo');

// payment getway start
Route::post('/store-payment-data', [SslCommerzPaymentController::class, 'storePaymentData']);
Route::get('/start-payment', [SslCommerzPaymentController::class, 'startPayment']);
Route::get('/billing', [SslCommerzPaymentController::class, 'showBillingForm'])->name('billing.form');
Route::post('/checkout', [SslCommerzPaymentController::class, 'pay'])->name('sslcommerz.checkout');
Route::get('/print-page', [PrintController::class, 'show'])->name('print.page');
// SSLCOMMERZ Start
Route::get('/payment-page', [SslCommerzPaymentController::class, 'exampleEasyCheckout']);
Route::get('/example2', [SslCommerzPaymentController::class, 'exampleHostedCheckout']);

Route::post('/pay', [SslCommerzPaymentController::class, 'index']);
Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);

Route::post('/success', [SslCommerzPaymentController::class, 'success'])->name('payment.success');
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);

Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END

Route::get('/payment/failure', function () {
    return view('payment.failure');
})->name('payment.failure');

Route::post('/print/store', [PrintController::class, 'store'])->name('print.store');
Route::get('/thank-you', function () {
    return view('thankyou'); // Make sure this Blade view exists
})->name('thankyou');
