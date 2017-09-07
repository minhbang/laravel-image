<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'imageables',
            function (Blueprint $table) {
                $table->integer('image_id')->unsigned();
                $table->integer('imageable_id')->unsigned();
                $table->string('imageable_type');
                $table->integer('position')->unsigned();
                $table->tinyInteger('type')->unsigned()->default(1);
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
        Schema::drop('imageables');
    }
}
