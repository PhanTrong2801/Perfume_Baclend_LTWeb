<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo User Admin
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

        $gucciId = DB::table('brands')->insertGetId([
            'brand_name' => 'Gucci',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/7/79/Gucci_Logo.svg',
            'description' => 'Đẳng cấp thời trang Ý'
        ]);

        $versaceId = DB::table('brands')->insertGetId([
            'brand_name' => 'Versace',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/b/b1/Versace_Logo.svg',
            'description' => 'Mạnh mẽ và quyến rũ'
        ]);

        $yslId = DB::table('brands')->insertGetId([
            'brand_name' => 'Yves Saint Laurent',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/1/18/YSL_Logo.svg',
            'description' => 'Tự do và phá cách'
        ]);

        $tomFordId = DB::table('brands')->insertGetId([
            'brand_name' => 'Tom Ford',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/6/64/Tom_Ford_logo.svg',
            'description' => 'Sang trọng và bí ẩn'
        ]);

        // 3. Tạo Danh mục (Categories)
        $woodyId = DB::table('categories')->insertGetId(['category_name' => 'Hương Gỗ (Woody)']);
        $floralId = DB::table('categories')->insertGetId(['category_name' => 'Hương Hoa (Floral)']);
        $citrusId = DB::table('categories')->insertGetId(['category_name' => 'Hương Cam Chanh (Citrus)']);
        $orientalId = DB::table('categories')->insertGetId(['category_name' => 'Hương Phương Đông (Oriental)']);
        $freshId = DB::table('categories')->insertGetId(['category_name' => 'Hương Tươi Mát (Fresh)']);

        // ---------------- SẢN PHẨM ----------------

        // SP 1: Dior Sauvage (Giữ nguyên)
        $p1 = DB::table('products')->insertGetId([
            'product_name' => 'Dior Sauvage Eau de Parfum',
            'description' => 'Mùi hương nam tính, mạnh mẽ và phóng khoáng.',
            'gender' => 'Male',
            'brand_id' => $diorId,
            'category_id' => $woodyId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/sauvage-100ml-edp_3377f47814cc46f2b6a467292480284a_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p1, 'volume' => '60ml', 'price' => 2500000, 'stock_quantity' => 50, 'sku' => 'DS-60'],
            ['product_id' => $p1, 'volume' => '100ml', 'price' => 3200000, 'stock_quantity' => 30, 'sku' => 'DS-100'],
        ]);

        // SP 2: Chanel No.5 (Giữ nguyên)
        $p2 = DB::table('products')->insertGetId([
            'product_name' => 'Chanel No.5',
            'description' => 'Huyền thoại nước hoa nữ, quyến rũ và sang trọng.',
            'gender' => 'Female',
            'brand_id' => $chanelId,
            'category_id' => $floralId,
            'thumbnail' => 'https://orchard.vn/wp-content/uploads/2014/06/chanel-no5-edp_7.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p2, 'volume' => '50ml', 'price' => 2800000, 'stock_quantity' => 20, 'sku' => 'C5-50'],
            ['product_id' => $p2, 'volume' => '100ml', 'price' => 4100000, 'stock_quantity' => 15, 'sku' => 'C5-100'],
        ]);

        // SP 3: Gucci Bloom (Nữ - Floral)
        $p3 = DB::table('products')->insertGetId([
            'product_name' => 'Gucci Bloom',
            'description' => 'Một khu vườn đầy hoa, mang lại vẻ đẹp tự nhiên và thanh thoát.',
            'gender' => 'Female',
            'brand_id' => $gucciId,
            'category_id' => $floralId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/gucci-bloom-100ml_07b55f191b264e1c9e821104323675a3_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p3, 'volume' => '30ml', 'price' => 1800000, 'stock_quantity' => 40, 'sku' => 'GB-30'],
            ['product_id' => $p3, 'volume' => '100ml', 'price' => 3100000, 'stock_quantity' => 25, 'sku' => 'GB-100'],
        ]);

        // SP 4: Versace Eros (Nam - Fresh/Woody)
        $p4 = DB::table('products')->insertGetId([
            'product_name' => 'Versace Eros',
            'description' => 'Thần tình yêu, mạnh mẽ, đam mê và đầy ham muốn.',
            'gender' => 'Male',
            'brand_id' => $versaceId,
            'category_id' => $freshId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/versace-eros-men-edt-100ml_3345d83669f64c1889073748238612f0_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p4, 'volume' => '50ml', 'price' => 1600000, 'stock_quantity' => 60, 'sku' => 'VE-50'],
            ['product_id' => $p4, 'volume' => '100ml', 'price' => 2200000, 'stock_quantity' => 45, 'sku' => 'VE-100'],
        ]);

        // SP 5: YSL Libre (Nữ - Oriental)
        $p5 = DB::table('products')->insertGetId([
            'product_name' => 'YSL Libre Eau de Parfum',
            'description' => 'Mùi hương của sự tự do, sang trọng và cá tính.',
            'gender' => 'Female',
            'brand_id' => $yslId,
            'category_id' => $orientalId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/ysl-libre-edp-90ml_1b73489278784d089163625f3c9e6d03_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p5, 'volume' => '50ml', 'price' => 2900000, 'stock_quantity' => 30, 'sku' => 'YSL-50'],
            ['product_id' => $p5, 'volume' => '90ml', 'price' => 3800000, 'stock_quantity' => 20, 'sku' => 'YSL-90'],
        ]);

        // SP 6: Tom Ford Tobacco Vanille (Unisex - Oriental/Woody)
        $p6 = DB::table('products')->insertGetId([
            'product_name' => 'Tom Ford Tobacco Vanille',
            'description' => 'Hương thuốc lá và vani ngọt ngào, ấm áp và đẳng cấp.',
            'gender' => 'Unisex',
            'brand_id' => $tomFordId,
            'category_id' => $orientalId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/tom-ford-tobacco-vanille-100ml_61c0d7575971485698502395642646c8_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p6, 'volume' => '50ml', 'price' => 5500000, 'stock_quantity' => 10, 'sku' => 'TF-50'],
            ['product_id' => $p6, 'volume' => '100ml', 'price' => 8200000, 'stock_quantity' => 5, 'sku' => 'TF-100'],
        ]);

        // SP 7: Dior Homme Sport (Nam - Citrus)
        $p7 = DB::table('products')->insertGetId([
            'product_name' => 'Dior Homme Sport',
            'description' => 'Năng động, thể thao với hương cam chanh tươi mát.',
            'gender' => 'Male',
            'brand_id' => $diorId,
            'category_id' => $citrusId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/dior-homme-sport-2021-125ml_4b341646270b43538663884852928509_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p7, 'volume' => '75ml', 'price' => 2400000, 'stock_quantity' => 35, 'sku' => 'DHS-75'],
            ['product_id' => $p7, 'volume' => '125ml', 'price' => 3100000, 'stock_quantity' => 25, 'sku' => 'DHS-125'],
        ]);

        // SP 8: Chanel Bleu de Chanel (Nam - Woody)
        $p8 = DB::table('products')->insertGetId([
            'product_name' => 'Bleu de Chanel Parfum',
            'description' => 'Mùi hương kinh điển, lịch lãm và đầy cuốn hút cho phái mạnh.',
            'gender' => 'Male',
            'brand_id' => $chanelId,
            'category_id' => $woodyId,
            'thumbnail' => 'https://product.hstatic.net/1000340570/product/bleu-de-chanel-parfum-100ml_f8b444733682496ba458055667794144_master.jpg',
        ]);
        DB::table('product_variants')->insert([
            ['product_id' => $p8, 'volume' => '50ml', 'price' => 3100000, 'stock_quantity' => 40, 'sku' => 'BDC-50'],
            ['product_id' => $p8, 'volume' => '100ml', 'price' => 4200000, 'stock_quantity' => 30, 'sku' => 'BDC-100'],
        ]);
    }
}