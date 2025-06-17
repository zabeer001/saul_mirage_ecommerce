<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'uniq_id',
        'customer_id',
        'product_id',
        'description',
        'type',
        'items',
        'status',
        'shipping_method',
        'shipping_price',
        'order_summary',
        'payment_method',
        'payment_status',
        'promocode_id',
        'promocode_name',
        'total',
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function promocode()
    {
        return $this->belongsTo(PromoCode::class, 'promocode_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
