<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_organizations', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('organization_id')->unsigned();
            $table->integer('role')->unsigned();
            $table->string('student_id');
            $table->string('faculty_field');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_organizations');
    }
}
