<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artist_tables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spotify_id', 25);
            $table->string('name', 100);
            $table->string('url', 255);
            $table->integer('popularity');
            $table->string('img_640', 255);
            $table->string('img_320', 255);
            $table->string('img_160', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artist_tables');
    }
}
