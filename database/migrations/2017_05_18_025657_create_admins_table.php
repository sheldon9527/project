<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('cellphone', 45);
                $table->string('email', 128);
                $table->string('username', 45)->nullable();
                $table->string('password', 64)->nullable();
                $table->string('first_name', 64);
                $table->string('last_name', 64);
                $table->text('extra');
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
        Schema::dropIfExists('admins');
    }
}
