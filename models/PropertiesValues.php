<?php namespace Radit\Catalog\Models;
use Model;
/**
 * OptionValue Model.
 */
class PropertiesValues extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_properties_values';
    /**
     * @var array Attribute casting
     */
    /*protected $casts = [
        'properties_id' => 'integer',
    ];*/
    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];
    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'product_id',
        'properties_id',
        'value',
    ];
    /**
     * @var array Relations
     */
    public $belongsTo = [
        'properties' => [
            'Radit\Catalog\Models\Properties',
            'table' => 'radit_shop_properties',
            'key' => 'properties_id'
        ],
        'product' => [
            'Radit\Catalog\Models\Product',
            'table' => 'radit_shop_products',
            'key' => 'products_id'
        ],
    ];
    public $belongsToMany = [];
}