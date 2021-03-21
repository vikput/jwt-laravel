<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Create table books
        Schema::create('books', function(Blueprint $table){
            $table->increments('id');
            $table->string('book_name', 255);
            $table->string('author', 255);
            $table->string('cover_image', 255)->nullable();

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Drop table books
        Schema::dropIfExists('books');
    }
}
