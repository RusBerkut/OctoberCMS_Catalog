<?php namespace Radit\Catalog\Models;

use Model;
use Radit\catalog\Models\Product as Product;
/**
 * Coupon Model
 */
class Calculator extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [];
    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_calculators';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $jsonable = ['options'];

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