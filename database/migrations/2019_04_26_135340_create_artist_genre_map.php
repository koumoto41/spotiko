<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtistGenreMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artist_genre_map', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('artist_id');
            $table->integer('genre_id');
            $table->timestamps();
            $table->unique('artist_id', 'genre_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artist_genre_map');
    }
}
