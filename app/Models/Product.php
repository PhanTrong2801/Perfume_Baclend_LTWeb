<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_name',
        'description',
        'gender', // Male, Female, Unisex
        'brand_id',
        'category_id',
        'thumbnail'
    ];

    // Quan hệ: Sản phẩm thuộc về 1 Thương hiệu
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    // Quan hệ: Sản phẩm thuộc về 1 Danh mục
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Quan hệ: Sản phẩm có nhiều Biến thể (Dung tích)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
    }
}