<?php namespace Radit\Catalog\Models;
use Model;
/**
 * OptionValue Model.
 */
class PropertiesListValues extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_properties_list_values';

    /**
     * @var array Attribute casting
     */
    protected $casts = [
        'id' => 'integer',
        'properties_id' => 'integer',
    ];
    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];
    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'value',
        'properties_id',
        'sorting',
    ];
    /**
     * @var array Relations
     */
    public $belongsTo = [
        'properties' => Properties::class,
    ];
    public $belongsToMany = [];
}