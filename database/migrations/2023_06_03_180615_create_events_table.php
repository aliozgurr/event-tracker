<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('site');
            $table->string('url')->nullable();
            $table->longText('title')->nullable();
            $table->longText('address')->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->string('organizer')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('city_slug')->nullable();
            $table->string('country')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->longText('tags')->nullable();
            $table->integer('attendee_count')->nullable();
            $table->string('organizer_page')->nullable();
            $table->string('price')->nullable();
            $table->longText('image')->nullable();
            $table->dateTime('live_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
