<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeachAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('teach_addresses')) {
            Schema::create('teach_addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id')->unsigned()->index();
                $table->string('name', 45)->index();
                $table->string('address', 255);
                $table->string('telephone', 32);
                $table->string('latitude', 64);
                $table->string('longitude', 64);
                $table->string('geohash', 255)->index();
                $table->enum('status', ['NO_APPROVAL','APPROVALED','ACTIVE', 'INACTIVE'])->default('NO_APPROVAL')->index();
                $table->enum('type', ['IN', 'OUT'])->index();
                $table->text('description');
                $table->text('special');
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
        Schema::dropIfExists('teach_addresses');
    }
}
