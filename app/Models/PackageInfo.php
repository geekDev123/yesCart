<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PackageInfo
 * 
 * @property int $id
 * @property int|null $package_id
 * @property string|null $product_id
 * @property string|null $products_quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Package|null $package
 *
 * @package App\Models
 */
class PackageInfo extends Model
{
	protected $table = 'package_infos';

	protected $casts = [
		'package_id' => 'int'
	];

	protected $fillable = [
		'package_id',
		'product_id',
		'products_quantity'
	];

	public function package()
	{
		return $this->belongsTo(Package::class);
	}
}
