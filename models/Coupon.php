<?php namespace Radit\Catalog\Models;

use Model;
use Radit\catalog\Models\Product as Product;
/**
 * Coupon Model
 */
class Coupon extends Model
{
	use \October\Rain\Database\Traits\Validation;	
	
	public $rules = [
        'value' => 'required',
		'product_id' => 'required|numeric',
		'discount' => 'required|numeric|min:0|max:100',
    ];
    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_coupons';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
	
	public function getProductIdOptions()
	{
		return Product::lists('name', 'id');
	}
}