<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['variants', 'brand'])->get();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['variants', 'brand'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

    return response()->json($product);
    }
}
