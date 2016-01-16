<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'images',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 255)->nullable();
                $table->string('filename', 100);
                $table->integer('width')->unsigned();
                $table->integer('height')->unsigned();
                $table->string('mime', 100);
                $table->integer('size')->unsigned();
                $table->integer('used')->default(0);
                $table->integer('user_id')->unsigned();
                $table->nullableTimestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
    }
}
