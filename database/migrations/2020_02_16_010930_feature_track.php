<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FeatureTrack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_track', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('track_id');
            $table->float('danceability');
            $table->float('energy');
            $table->float('loudness');
            $table->integer('mode');
            $table->float('speechiness');
            $table->float('acousticness');
            $table->float('instrumentalness');
            $table->float('liveness');
            $table->float('valence');
            $table->float('tempo');
            $table->integer('time_signature');
            $table->timestamps();
            $table->unique('track_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
