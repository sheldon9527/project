<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('attachable_type', 255);
                $table->integer('attachable_id')->unsigned();
                $table->string('relative_path', 255);
                $table->string('filename', 255);
                $table->text('description');
                $table->string('tag', 45);
                $table->double('width');
                $table->double('height');
                $table->string('mime_types', 45);
                $table->integer('weight');
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
        Schema::dropIfExists('attachments');
    }
}
