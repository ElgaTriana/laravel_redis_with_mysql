<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table="products";

    protected $fillable = [
        'nama_produk',
        'harga_produk',
        'qty_stok',
        'insert_user',
        'update_user'
    ];
}
