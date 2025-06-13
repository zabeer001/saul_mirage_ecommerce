<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'uniq_id',
        'full_name',
        'last_name',
        'email',
        'phone',
        'full_address',
        'city',
        'state',
        'postal_code',
        'country',
        'product_id',
        'description',
        'type',
        'status',
        'shipping_method',
        'shipping_price',
        'order_summary',
        'payment_method',
        'payment_status',
        'promocode_id',
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
    return $this->belongsTo(PromoCode::class);
}
}
