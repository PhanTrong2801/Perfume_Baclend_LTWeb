<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


Route::post('/login', [AuthController::class, 'login']);



Route::get('/install-db', function () {
    try {
        // 1. Chạy Migrate (Tạo bảng)
        Artisan::call('migrate --force');
        $migrateOutput = Artisan::output();

        // 2. Chạy Seed (Đổ dữ liệu mẫu)
        $seedOutput = Artisan::output();

        return "<h1>Cài đặt thành công!</h1>" .
               "<p><strong>Migrate:</strong> <br>" . nl2br($migrateOutput) . "</p>" .
               "<p><strong>Seed:</strong> <br>" . nl2br($seedOutput) . "</p>";

    } catch (\Exception $e) {
        return "<h1>Lỗi rồi!</h1><p>" . $e->getMessage() . "</p>";
    }
});

Route::get('/run-seed', function () {
    try {
        // Chạy lệnh db:seed (Thêm --force để chạy được trên production)
        Artisan::call('db:seed --force');
        
        return "<h1>Đã chạy Seeder thành công!</h1><p>" . nl2br(Artisan::output()) . "</p>";
    } catch (\Exception $e) {
        return "<h1>Lỗi:</h1>" . $e->getMessage();
    }
});