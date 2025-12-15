<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    // 1. Lấy danh sách sản phẩm (kèm thương hiệu, danh mục)
    public function index()
    {
        $products = Product::with(['brand', 'category', 'variants'])->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    // 2. Lấy dữ liệu phụ trợ (Brands & Categories) để hiện trong Form thêm mới
    public function getFormData()
    {
        $brands = Brand::all();
        $categories = Category::all();
        return response()->json(['brands' => $brands, 'categories' => $categories]);
    }

    // 3. Lấy chi tiết 1 sản phẩm (để sửa)
    public function show($id)
    {
        $product = Product::with(['variants', 'brand', 'category'])->findOrFail($id);
        return response()->json($product);
    }

    // 4. THÊM SẢN PHẨM MỚI
    public function store(Request $request)
    {
        // Validate cơ bản
        $request->validate([
            'product_name' => 'required',
            'brand_id' => 'required',
            'category_id' => 'required',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Bắt buộc là ảnh
        ]);

        return DB::transaction(function () use ($request) {
            // A. Xử lý upload ảnh
            $imageUrl = '';
            if ($request->hasFile('thumbnail')) {
                // Lưu vào folder 'products' trong storage/app/public
                $path = $request->file('thumbnail')->store('products', 'public');
                // Tạo đường dẫn http://.../storage/products/abc.jpg
                $imageUrl = asset('storage/' . $path);
            }

            // B. Tạo sản phẩm
            $product = Product::create([
                'product_name' => $request->product_name,
                'description' => $request->description,
                'gender' => $request->gender,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'thumbnail' => $imageUrl
            ]);

            // C. Tạo biến thể (Variants)
            // React sẽ gửi variants dạng chuỗi JSON: '[{"volume":"50ml","price":100},...]'
            if ($request->variants) {
                $variants = json_decode($request->variants, true); 
                foreach ($variants as $v) {
                    ProductVariant::create([
                        'product_id' => $product->product_id,
                        'volume' => $v['volume'],
                        'price' => $v['price'],
                        'stock_quantity' => $v['stock_quantity'],
                        'sku' => $v['sku'] ?? $product->product_id . '-' . $v['volume']
                    ]);
                }
            }

            return response()->json(['message' => 'Thêm sản phẩm thành công!']);
        });
    }

    // 5. CẬP NHẬT SẢN PHẨM
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        return DB::transaction(function () use ($request, $product) {
            // A. Cập nhật thông tin cơ bản
            $dataToUpdate = $request->only(['product_name', 'description', 'gender', 'brand_id', 'category_id']);
            
            // B. Nếu có ảnh mới thì upload và thay thế
            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('products', 'public');
                $dataToUpdate['thumbnail'] = asset('storage/' . $path);
            }

            $product->update($dataToUpdate);

            // C. Cập nhật biến thể (Xóa hết cái cũ tạo lại cho nhanh và an toàn logic)
            if ($request->variants) {
                // Xóa cũ
                ProductVariant::where('product_id', $product->product_id)->delete();
                
                // Tạo mới
                $variants = json_decode($request->variants, true);
                foreach ($variants as $v) {
                    ProductVariant::create([
                        'product_id' => $product->product_id,
                        'volume' => $v['volume'],
                        'price' => $v['price'],
                        'stock_quantity' => $v['stock_quantity'],
                        'sku' => $v['sku'] ?? $product->product_id . '-' . $v['volume']
                    ]);
                }
            }

            return response()->json(['message' => 'Cập nhật thành công!']);
        });
    }

    // 6. XÓA SẢN PHẨM
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        // Xóa các biến thể trước (nếu database không set cascade)
        ProductVariant::where('product_id', $id)->delete();
        $product->delete();
        
        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }
}