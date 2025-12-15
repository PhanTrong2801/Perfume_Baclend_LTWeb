<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class AdminOrderController extends Controller
{
    // 1. Lấy danh sách toàn bộ đơn hàng
    public function index()
    {
        $orders = Order::with(['user', 'items.variant.product']) // Load thông tin user và sản phẩm
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // 2. Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Shipped,Delivered,Cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }
}