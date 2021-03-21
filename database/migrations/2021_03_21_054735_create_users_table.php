<?php

use Illuminate\Support\Facades\Schema;
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
        //Create users tables
        Schema::create('users', function(Blueprint $table){
            $table->increments('id');
            $table->string('firstname', 255);
            $table->string('lastname', 255);
            $table->string('mobile', 10);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->tinyInteger('age', false, false);
            $table->enum('gender', array('M', 'F', 'O'));
            $table->string('city', 255);

            $table->engine = 'InnoDB';
            $table->unique('mobile');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Drop users table
        Schema::dropIfExists('users');
    }
}
