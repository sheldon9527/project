<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('name', 255);
                $table->string('en_name', 255)->nullable();
                $table->integer('left')->nullable();
                $table->integer('right')->nullable();
                $table->integer('level')->nullable();
                $table->integer('order_number')->unsigned();
                $table->string('icon_url', 255);
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE')->index();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
