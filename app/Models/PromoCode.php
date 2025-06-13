<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'usage_limit',
        'amount'

    ];

  public function orders()
    {
        return $this->hasMany(Order::class, 'promocode_id', 'id');
    }
}
