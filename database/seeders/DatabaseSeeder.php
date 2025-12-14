<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       DB::table('users')->insert([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
        ]);

        // 2. Tạo Thương hiệu (Brands)
        $diorId = DB::table('brands')->insertGetId([
            'brand_name' => 'Dior',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a8/Dior_Logo.svg',
            'description' => 'Thương hiệu xa xỉ từ Pháp'
        ]);
        
        $chanelId = DB::table('brands')->insertGetId([
            'brand_name' => 'Chanel', 
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/en/9/92/Chanel_logo_interlocking_cs.svg',
            'description' => 'Biểu tượng thời trang thế giới'
        ]);

        // 3. Tạo Danh mục (Categories)
        $woodyId = DB::table('categories')->insertGetId(['category_name' => 'Hương Gỗ (Woody)']);
        $floralId = DB::table('categories')->insertGetId(['category_name' => 'Hương Hoa (Floral)']);

        // 4. Tạo Sản phẩm 1: Dior Sauvage
        $productId1 = DB::table('products')->insertGetId([
            'product_name' => 'Dior Sauvage Eau de Parfum',
            'description' => 'Mùi hương nam tính, mạnh mẽ và phóng khoáng.',
            'gender' => 'Male',
            'brand_id' => $diorId,
            'category_id' => $woodyId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/sauvage-100ml-edp_3377f47814cc46f2b6a467292480284a_master.jpg',
        ]);

        // Tạo biến thể cho Dior Sauvage (60ml và 100ml)
        DB::table('product_variants')->insert([
            ['product_id' => $productId1, 'volume' => '60ml', 'price' => 2500000, 'stock_quantity' => 50, 'sku' => 'DS-60'],
            ['product_id' => $productId1, 'volume' => '100ml', 'price' => 3200000, 'stock_quantity' => 30, 'sku' => 'DS-100'],
        ]);

        // 5. Tạo Sản phẩm 2: Chanel No.5
        $productId2 = DB::table('products')->insertGetId([
            'product_name' => 'Chanel No.5',
            'description' => 'Huyền thoại nước hoa nữ, quyến rũ và sang trọng.',
            'gender' => 'Female',
            'brand_id' => $chanelId,
            'category_id' => $floralId,
            'thumbnail' => 'https://orchard.vn/wp-content/uploads/2014/06/chanel-no5-edp_7.jpg',
        ]);

        // Tạo biến thể cho Chanel No.5
        DB::table('product_variants')->insert([
            ['product_id' => $productId2, 'volume' => '50ml', 'price' => 2800000, 'stock_quantity' => 20, 'sku' => 'C5-50'],
            ['product_id' => $productId2, 'volume' => '100ml', 'price' => 4100000, 'stock_quantity' => 15, 'sku' => 'C5-100'],
        ]);
    }
}
