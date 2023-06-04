<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawl_reports', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->integer('updated_count');
            $table->integer('created_count');
            $table->dateTime('runtime');
            $table->string('result');
            $table->text('fail_reason')->nullable();
            $table->integer('without_image')->nullable();
            $table->integer('without_address')->nullable();
            $table->integer('without_organizer')->nullable();
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
        Schema::dropIfExists('crawl_reports');
    }
}
