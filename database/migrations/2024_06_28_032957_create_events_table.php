<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // この行が既に存在する場合、追加のマイグレーションは不要
            $table->string('name');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('events');
    }
};