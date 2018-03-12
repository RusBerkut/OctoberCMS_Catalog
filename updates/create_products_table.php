<?php namespace Radit\catalog\updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateProductsTable extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('radit_shop_products'))
        {
            Schema::create('radit_shop_products', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('slug')->index()->unique();
                $table->string('model')->nullable();
                $table->string('articul')->nullable();
                $table->text('short_description')->nullable();
                $table->text('description')->nullable();
                $table->json('options'); 
                $table->integer('vendor_id')->nullable();  
                $table->boolean('is_active')->default(true);
                $table->boolean('is_recommended')->default(false);
                $table->boolean('is_digital')->default(false);
                $table->decimal('price', 15, 4);
                $table->string('meta_title')->nullable();
                $table->string('meta_description')->nullable();
                $table->string('meta_keywords')->nullable();
                $table->string('meta_h1')->nullable();
                $table->timestamp('available_at');
                $table->timestamps();
            });    
        }
        
    }

    public function down()
    {
        if (Schema::hasTable('radit_shop_products'))
        {
            Schema::dropIfExists('radit_shop_products');
        }
    }

}
