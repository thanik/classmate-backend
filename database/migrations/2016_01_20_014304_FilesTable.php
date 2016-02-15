<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('material_id')->unsigned()->nullable();
            $table->integer('revision_id')->unsigned();
            $table->string('filename');
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('files');
    }
}
