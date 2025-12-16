<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

    //  HÀM THÊM MỚI 
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
                // Upload lên Cloudinary
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

    //  HÀM CẬP NHẬT 
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        return DB::transaction(function () use ($request, $product) {

            $dataToUpdate = $request->only(['product_name', 'description', 'gender', 'brand_id', 'category_id']);
            
            if ($request->hasFile('thumbnail')) {
                $uploadedFileUrl = Cloudinary::upload($request->file('thumbnail')->getRealPath(), [
                    'folder' => 'products'
                ])->getSecurePath();
                $dataToUpdate['thumbnail'] = $uploadedFileUrl;
            }

            $product->update($dataToUpdate);

            // Cập nhật biến thể
            if ($request->variants) {
                $variants = json_decode($request->variants, true);
                $existingVariantIds = $product->variants->pluck('variant_id')->toArray();
                $submittedVariantIds = [];

                foreach ($variants as $v) {
                    if (isset($v['variant_id'])) {
                        $submittedVariantIds[] = $v['variant_id'];
                        ProductVariant::where('variant_id', $v['variant_id'])->update([
                            'volume' => $v['volume'],
                            'price' => $v['price'],
                            'stock_quantity' => $v['stock_quantity'],
                            'sku' => $v['sku']
                        ]);
                    } else {
                        ProductVariant::create([
                            'product_id' => $product->product_id,
                            'volume' => $v['volume'],
                            'price' => $v['price'],
                            'stock_quantity' => $v['stock_quantity'],
                            'sku' => $v['sku'] ?? $product->product_id . '-' . $v['volume']
                        ]);
                    }
                }
                
                $idsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
                if (!empty($idsToDelete)) {
                    try {
                        ProductVariant::destroy($idsToDelete);
                    } catch (\Exception $e) {
                    }
                }
            }

            return response()->json(['message' => 'Cập nhật thành công!']);
        });
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        ProductVariant::where('product_id', $id)->delete();
        $product->delete();
        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }
}