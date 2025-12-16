<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    //  Lấy chi tiết 1 sản phẩm 
    public function show($id)
    {
        $product = Product::with(['variants', 'brand', 'category'])->findOrFail($id);
        return response()->json($product);
    }

    //  THÊM SẢN PHẨM MỚI
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
            if ($request->hasFile('thumbnail')) {
               // Upload trực tiếp lên Cloudinary
                $uploadedFileUrl = Cloudinary::upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'products'
            ])->getSecurePath();

            $imageUrl = $uploadedFileUrl;
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


   public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    // 1. Chuẩn bị dữ liệu Update
    $dataToUpdate = $request->only(['product_name', 'description', 'gender', 'brand_id', 'category_id']);

    // 2. Xử lý Upload ảnh (Làm TRƯỚC khi gọi DB Transaction)
    if ($request->hasFile('thumbnail')) {
        try {
            // Upload ảnh mới
            $uploadedFileUrl = Cloudinary::upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'products'
            ])->getSecurePath();
            
            // Gán link mới vào data
            $dataToUpdate['thumbnail'] = $uploadedFileUrl;

            // (Tùy chọn) Xóa ảnh cũ trên Cloudinary nếu muốn tiết kiệm dung lượng
            // Cần lấy public_id từ link cũ để xóa, logic này hơi phức tạp nên có thể bỏ qua nếu không cần thiết ngay.
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi upload ảnh: ' . $e->getMessage()], 500);
        }
    }

    // 3. Bây giờ mới mở Transaction để Update DB (Chạy cực nhanh)
    return DB::transaction(function () use ($request, $product, $dataToUpdate) {
        
        // Update thông tin sản phẩm
        $product->update($dataToUpdate);

        // Cập nhật biến thể (Logic giữ nguyên vì đã ổn)
        if ($request->variants) {
            $variants = json_decode($request->variants, true);
            
            // Kiểm tra nếu giải mã JSON thất bại
            if (!is_array($variants)) {
                 // Xử lý lỗi hoặc bỏ qua
                 $variants = [];
            }

            $existingVariantIds = $product->variants->pluck('variant_id')->toArray();
            $submittedVariantIds = [];

            foreach ($variants as $v) {
                // Tạo SKU tự động nếu thiếu
                $sku = $v['sku'] ?? ($product->product_id . '-' . $v['volume']);

                if (isset($v['variant_id'])) {
                    $submittedVariantIds[] = $v['variant_id'];
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
            
            $idsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
            if (!empty($idsToDelete)) {
                ProductVariant::destroy($idsToDelete);
            }
        }

        return response()->json(['message' => 'Cập nhật thành công!']);
    });
}

    //  XÓA SẢN PHẨM
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        ProductVariant::where('product_id', $id)->delete();
        $product->delete();
        
        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }
}