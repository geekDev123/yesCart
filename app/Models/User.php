<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property string|null $phone_number
 * @property string|null $lat
 * @property string|null $long
 * @property string|null $address
 * @property string|null $description
 * @property string|null $image
 * 
 * @property Collection|Cart[] $carts
 * @property Collection|Order[] $orders
 * @property Collection|Package[] $packages
 * @property Collection|Product[] $products
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
	protected $table = 'users';

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
		'type',
		'phone_number',
		'lat',
		'long',
		'address',
		'description',
		'image'
	];

	public function carts()
	{
		return $this->hasMany(Cart::class, 'customer_id');
	}

	public function orders()
	{
		return $this->hasMany(Order::class, 'customer_id');
	}

	public function packages()
	{
		return $this->hasMany(Package::class, 'agent_id');
	}

	public function products()
	{
		return $this->hasMany(Product::class, 'butcher_id');
	}

	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		return [];
	}

	public function getImageAttribute(){
		return env('APP_URL').'/public/'.$this->attributes['image'];
	}
}
