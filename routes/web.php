<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{HomeController,AuthController,ServiceController,BookingController,WorkerController,AdminController};
Route::get('/',[HomeController::class,'index'])->name('home');
Route::get('/services',[ServiceController::class,'index'])->name('services.index');
Route::get('/services/{service}',[ServiceController::class,'show'])->name('services.show');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'showLogin'])->name('login');Route::post('/login',[AuthController::class,'login'])->name('login.store');Route::get('/register',[AuthController::class,'showRegister'])->name('register');Route::post('/register',[AuthController::class,'register'])->name('register.store');});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function(){
 Route::get('/dashboard',function(){return match(auth()->user()->role){'admin'=>redirect()->route('admin.dashboard'),'worker'=>redirect()->route('worker.dashboard'),default=>redirect()->route('bookings.index')};})->name('dashboard');
 Route::get('/services/{service}/book',[BookingController::class,'create'])->name('bookings.create');Route::post('/services/{service}/book',[BookingController::class,'store'])->name('bookings.store');Route::get('/my-bookings',[BookingController::class,'index'])->name('bookings.index');Route::post('/bookings/{booking}/review',[BookingController::class,'review'])->name('bookings.review');
 Route::get('/become-a-worker',[WorkerController::class,'registerForm'])->middleware('role:customer,worker')->name('worker.register');Route::post('/become-a-worker',[WorkerController::class,'register'])->middleware('role:customer,worker')->name('worker.register.store');
 Route::prefix('worker')->name('worker.')->middleware('role:worker')->group(function(){Route::get('/dashboard',[WorkerController::class,'dashboard'])->name('dashboard');Route::patch('/bookings/{booking}/status',[WorkerController::class,'updateStatus'])->name('booking.status');});
 Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function(){Route::get('/',[AdminController::class,'dashboard'])->name('dashboard');Route::patch('/workers/{user}/verification',[AdminController::class,'approveWorker'])->name('worker.verify');Route::get('/bookings',[AdminController::class,'bookings'])->name('bookings');});
});
