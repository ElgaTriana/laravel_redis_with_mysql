<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $table="checkout";

    protected $fillable = [
        'no_pesanan',
        'id_user',
        'id_produk',
        'qty',
        'total_harga',
        'tanggal_pemesanan',
        'status_pembayaran'
    ];

}
