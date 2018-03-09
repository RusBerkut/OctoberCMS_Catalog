<?php
namespace Radit\Catalog\Models;

use Model;

class Properties extends Model {

    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'title' => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_properties';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $slugs = ['slug' => 'title'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'properties_values' => PropertiesValues::class,
        'properties_list_values' => PropertiesListValues::class
    ];
    public $belongsTo = [
        'product' => Product::class
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getTypeOptions()
    {
        return Array(
            'S' => 'Строка',
            'L' => 'Список'
        );
    }

}
