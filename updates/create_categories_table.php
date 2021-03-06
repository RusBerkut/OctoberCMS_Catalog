<?php namespace Radit\catalog\updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('radit_shop_categories'))
        {
            Schema::create('radit_shop_categories', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('parent_id')->unsigned()->index()->nullable();
                $table->string('title')->nullable();
                $table->string('slug')->index()->unique();
                $table->string('description')->nullable();
                $table->integer('nest_left')->nullable();
                $table->integer('nest_right')->nullable();
                $table->integer('nest_depth')->nullable();
                $table->integer('count_products')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('radit_shop_products_categories'))
        {
            Schema::create('radit_shop_products_categories', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('product_id')->unsigned();
                $table->integer('category_id')->unsigned();
                $table->primary(
                    ['product_id', 'category_id'],
                    'radit_shop_products_categories_primary'
                );
            });
        }        
    }

    public function down()
    {
        if (Schema::hasTable('radit_shop_categories'))
        {
            Schema::drop('radit_shop_categories');
        }

		if (Schema::hasTable('radit_shop_products_categories'))
		{
			Schema::drop('radit_shop_products_categories');
		}
    }

}
