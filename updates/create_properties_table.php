<?php namespace Radit\catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePropertiesTable extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('radit_shop_properties'))
        {
            Schema::create('radit_shop_properties', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title')->nullable();
                $table->string('code')->nullable();
                $table->string('type')->default('S');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('radit_shop_properties_values'))
        {
            Schema::create('radit_shop_properties_values', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('product_id')->unsigned();
                $table->integer('property_id')->unsigned();
                $table->string('value')->nullable();
                $table->primary(
                    ['id', 'product_id', 'property_id'],
                    'radit_shop_properties_values'
                );
            });
        }

        if (!Schema::hasTable('radit_shop_properties_list_values'))
        {
            Schema::create('radit_shop_properties_list_values', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('property_id')->unsigned();
                $table->string('value')->nullable();
                $table->string('is_default')->default('N');
                $table->integer('sorting')->default(500);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('radit_shop_properties'))
        {
            Schema::drop('radit_shop_properties');
        }

        if (Schema::hasTable('radit_shop_properties_values'))
        {
            Schema::drop('radit_shop_properties_values');
        }

        if (Schema::hasTable('radit_shop_properties_list_values'))
        {
            Schema::drop('radit_shop_properties_list_values');
        }
    }

}
