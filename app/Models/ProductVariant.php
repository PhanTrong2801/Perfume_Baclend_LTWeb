<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_variants'; // Chỉ định tên bảng rõ ràng
    protected $primaryKey = 'variant_id';

    protected $fillable = [
        'product_id',
        'volume', // 50ml, 100ml...
        'price',
        'stock_quantity',
        'sku'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}