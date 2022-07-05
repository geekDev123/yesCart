<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Package
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $delivery_type
 * @property string|null $delivery_day
 * @property int $agent_id
 * @property string|null $status
 * @property string|null $amount
 * @property string|null $transaction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Collection|PackageInfo[] $package_infos
 *
 * @package App\Models
 */
class Package extends Model
{
	protected $table = 'packages';

	protected $casts = [
		'agent_id' => 'int'
	];

	protected $fillable = [
		'name',
		'delivery_type',
		'delivery_day',
		'agent_id',
		'status',
		'amount',
		'transaction_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'agent_id');
	}

	public function package_infos()
	{
		return $this->hasMany(PackageInfo::class);
	}
}
