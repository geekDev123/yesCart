<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
		'product_id',
		'butcher_id',
        'customer_id',
		'quantity',
		'price'
	];

}
