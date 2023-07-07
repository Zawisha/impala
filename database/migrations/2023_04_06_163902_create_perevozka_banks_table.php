<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perevozka_banks', function (Blueprint $table) {
            $table->id();
            $table->integer('perevozka_id')->nullable();
            $table->text('BIK')->nullable();
            $table->text('raschetnyi')->nullable();
            $table->text('korschet')->nullable();
            $table->text('kommentarii')->nullable();
            $table->text('bank')->nullable();
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
        Schema::dropIfExists('perevozka_banks');
    }
};
