<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token', function (Blueprint $table) {
            $table->string('token', 500)->change();
            $table->string('refresh_token', 500)->change();
        });
    }

    public function down(): void
    {
        Schema::table('token', function (Blueprint $table) {
            $table->string('token', 200)->change();
            $table->string('refresh_token', 200)->change();
        });
    }
};
