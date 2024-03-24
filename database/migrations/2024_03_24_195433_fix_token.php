<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token', function (Blueprint $table) {
            $table->string('token', 255)->change();
            $table->string('refresh_token', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('token', function (Blueprint $table) {
            $table->string('token', 200)->change();
            $table->string('refresh_token', 200)->change();
        });
    }
}
