<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Thêm vào giỏ hàng
    public function addToCart(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user(); 

        // Tìm giỏ hàng của user, nếu chưa có thì tạo mới
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->user_id]
        );

        // Kiểm tra sản phẩm này đã có trong giỏ chưa
        $cartItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('variant_id', $request->variant_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->cart_id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Đã thêm vào giỏ hàng thành công!']);
    }

    // Lấy danh sách giỏ hàng 
    public function getCart(Request $request)
    {
        $user = $request->user();
        
        $cart = Cart::where('user_id', $user->user_id)
            ->with(['items.variant.product']) 
            ->first();

        if (!$cart) {
            return response()->json(['items' => []]);
        }

        return response()->json($cart);
    }

    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();

        // Tìm item trong giỏ, đảm bảo item đó thuộc về giỏ hàng của user đang đăng nhập
        $cartItem = CartItem::where('cart_item_id', $itemId)
            ->whereHas('cart', function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })
            ->firstOrFail();

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Cập nhật thành công']);
    }

    //  Xóa sản phẩm 
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();

        $cartItem = CartItem::where('cart_item_id', $itemId)
            ->whereHas('cart', function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ']);
    }
}