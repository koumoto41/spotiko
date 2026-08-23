<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spotify_id', 25);
            $table->integer('artist_id');
            $table->string('name', 200);
            $table->string('url', 255);
            $table->date('release_date');
            $table->integer('total_track');
            $table->string('img_640', 255);
            $table->string('img_320', 255);
            $table->string('img_160', 255);
            $table->timestamps();

            $table->unique('spotify_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('album');
    }
}
