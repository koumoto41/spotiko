<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('track', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spotify_id', 25);
            $table->integer('artist_id');
            $table->integer('album_id');
            $table->string('name', 200);
            $table->string('url', 255);
            $table->integer('popularity');
            $table->integer('track_number');
            $table->string('preview_url', 200);
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
        Schema::dropIfExists('track');
    }
}
