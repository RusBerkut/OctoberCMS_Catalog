<?php namespace Radit\catalog\updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCurrenciesTable extends Migration
{

    public function up()
    {
        
        if (!Schema::hasTable('radit_shop_currencies'))
        {
            Schema::create('radit_shop_currencies', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('sign')->nullable();
                $table->decimal('value', 15, 4);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }        
    }

    public function down()
    {
        if (Schema::hasTable('radit_shop_currencies'))
        {
            Schema::drop('radit_shop_currencies');
        }
    }

}
