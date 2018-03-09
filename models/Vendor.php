<?php namespace Radit\Catalog\Models;

use Model;

/**
 * Vendor Model
 */
class Vendor extends Model
{

    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'name' => 'required',
        'slug'=>'required|unique:radit_shop_vendors'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_vendors';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $slugs = ['slug' => 'name'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'products' => ['Radit\Catalog\Models\Product', 'order' => 'created_at']
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = ['logo' => ['System\Models\File']];
    public $attachMany = [];

}