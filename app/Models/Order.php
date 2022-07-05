<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $quantity
 * @property string|null $status
 * @property string|null $amount
 * @property int $product_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Product $product
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';

	protected $casts = [
		'product_id' => 'int',
		'customer_id' => 'int'
	];

	protected $fillable = [
		'name',
		'quantity',
		'status',
		'amount',
		'product_id',
		'customer_id',
		'transaction_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'customer_id');
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
