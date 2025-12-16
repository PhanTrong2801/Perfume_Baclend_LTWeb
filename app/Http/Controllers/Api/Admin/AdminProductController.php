<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Dùng cái này thay cho Cloudinary

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['brand', 'category', 'variants'])->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }

    public function getFormData()
    {
        $brands = Brand::all();
        $categories = Category::all();
        return response()->json(['brands' => $brands, 'categories' => $categories]);
    }

    public function show($id)
    {
        $product = Product::with(['variants', 'brand', 'category'])->findOrFail($id);
        return response()->json($product);
    }

    // --- HÀM THÊM MỚI (Lưu Local) ---
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'brand_id' => 'required',
            'category_id' => 'required',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        return DB::transaction(function () use ($request) {
            $imageUrl = '';
            
            // Xử lý lưu ảnh vào thư mục public/products
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                // Lưu vào storage/app/public/products
                $path = $file->store('products', 'public'); 
                // Tạo đường dẫn truy cập (VD: http://localhost:8000/storage/products/abc.jpg)
                $imageUrl = asset('storage/' . $path);
            }

            $product = Product::create([
                'product_name' => $request->product_name,
                'description' => $request->description,
                'gender' => $request->gender,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'thumbnail' => $imageUrl
            ]);

            if ($request->variants) {
                $this->saveVariants($product, $request->variants);
            }

            return response()->json(['message' => 'Thêm sản phẩm thành công!']);
        });
    }

    // --- HÀM CẬP NHẬT (Lưu Local) ---
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        return DB::transaction(function () use ($request, $product) {
            $dataToUpdate = $request->only(['product_name', 'description', 'gender', 'brand_id', 'category_id']);
            
            if ($request->hasFile('thumbnail')) {
                // 1. Xóa ảnh cũ nếu có (để dọn rác)
                if ($product->thumbnail) {
                    // Cắt lấy phần path sau chữ storage/
                    $oldPath = str_replace(asset('storage/'), '', $product->thumbnail);
                    Storage::disk('public')->delete($oldPath);
                }

                // 2. Lưu ảnh mới
                $file = $request->file('thumbnail');
                $path = $file->store('products', 'public');
                $dataToUpdate['thumbnail'] = asset('storage/' . $path);
            }

            $product->update($dataToUpdate);

            if ($request->variants) {
                $this->saveVariants($product, $request->variants, true);
            }

            return response()->json(['message' => 'Cập nhật thành công!']);
        });
    }

    // --- HÀM HỖ TRỢ LƯU BIẾN THỂ (Giữ nguyên logic cũ) ---
    private function saveVariants($product, $variantsJson, $isUpdate = false)
    {
        $variants = json_decode($variantsJson, true);
        if (!is_array($variants)) return;

        $existingIds = $isUpdate ? $product->variants->pluck('variant_id')->toArray() : [];
        $submittedIds = [];

        foreach ($variants as $v) {
            $sku = $v['sku'] ?? $product->product_id . '-' . $v['volume'];
            
            if ($isUpdate && isset($v['variant_id'])) {
                $submittedIds[] = $v['variant_id'];
                ProductVariant::where('variant_id', $v['variant_id'])->update([
                    'volume' => $v['volume'], 
                    'price' => $v['price'], 
                    'stock_quantity' => $v['stock_quantity'], 
                    'sku' => $sku
                ]);
            } else {
                ProductVariant::create([
                    'product_id' => $product->product_id,
                    'volume' => $v['volume'], 
                    'price' => $v['price'], 
                    'stock_quantity' => $v['stock_quantity'], 
                    'sku' => $sku
                ]);
            }
        }

        if ($isUpdate) {
            $idsToDelete = array_diff($existingIds, $submittedIds);
            if (!empty($idsToDelete)) {
                ProductVariant::destroy($idsToDelete);
            }
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Xóa ảnh khi xóa sản phẩm
        if ($product->thumbnail) {
             $oldPath = str_replace(asset('storage/'), '', $product->thumbnail);
             Storage::disk('public')->delete($oldPath);
        }

        $product->variants()->delete();
        $product->delete();
        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }
}