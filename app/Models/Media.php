<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'product_id']; // Add fillable fields

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
