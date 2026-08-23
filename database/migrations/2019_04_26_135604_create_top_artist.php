<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopArtist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_artist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('target_date');
            $table->integer('artist_id');
            $table->integer('point');
            $table->integer('popularity');
            $table->timestamps();
            $table->unique('target_date', 'artist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('top_artist');
    }
}
