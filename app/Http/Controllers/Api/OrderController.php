<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB; // Dùng transaction cho an toàn

class OrderController extends Controller
{
    // 1. ĐẶT HÀNG (CHECKOUT)
    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string' // COD hoặc Banking
        ]);

        $user = $request->user();

        // Lấy giỏ hàng của user
        $cart = Cart::where('user_id', $user->user_id)->with('items.variant')->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json(['message' => 'Giỏ hàng trống!'], 400);
        }

        return DB::transaction(function () use ($request, $user, $cart) {
            // A. Tính tổng tiền
            $totalAmount = 0;
            foreach ($cart->items as $item) {
                $totalAmount += $item->quantity * $item->variant->price;
            }

            // B. Tạo Đơn hàng (Order)
            $order = Order::create([
                'user_id' => $user->user_id,
                'total_amount' => $totalAmount,
                'status' => 'Pending', // Mặc định là Chờ xử lý
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'order_date' => now()
            ]);

            // C. Tạo Chi tiết đơn hàng (Order Items) & Trừ kho (nếu cần)
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->variant->price // Lưu giá tại thời điểm mua
                ]);
            }

            // D. Xóa sạch giỏ hàng sau khi đặt thành công
            CartItem::where('cart_id', $cart->cart_id)->delete();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $order->order_id
            ]);
        });
    }

    // 2. XEM LỊCH SỬ ĐƠN HÀNG
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Lấy danh sách đơn hàng, sắp xếp mới nhất lên đầu
        $orders = Order::where('user_id', $user->user_id)
            ->with(['items.variant.product']) // Load chi tiết để hiển thị
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}