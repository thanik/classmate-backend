<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('facebook_id');
            $table->string('facebook_token');
            $table->string('name');
            $table->integer('default_organization_id')->unsigned();
            $table->integer('default_role')->unsigned();
            $table->string('email')->unique();
            $table->string('password', 60)->nullable();
            $table->string('avatar_filename');
            $table->timestamps();
            $table->foreign('default_organization_id')->references('id')->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
