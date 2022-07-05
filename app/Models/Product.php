<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $amount
 * @property string|null $description
 * @property string|null $image
 * @property string|null $quantity
 * @property int $butcher_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Product extends Model
{
	protected $table = 'products';

	protected $casts = [
		'butcher_id' => 'int'
	];

	protected $fillable = [
		'name',
		'amount',
		'description',
		'image',
		'quantity',
		'category_id',
		'status',
		'butcher_id',
		'delivery_type',
		'delivery_day'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'butcher_id');
	}

	public function getImageAttribute(){
		return env('APP_URL').'/public/'.$this->attributes['image'];
	}
}
