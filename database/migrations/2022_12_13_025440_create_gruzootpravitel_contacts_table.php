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
        Schema::create('gruzootpravitel_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('gruzootpravitel_id')->nullable();
            $table->text('dolznost')->nullable();
            $table->text('FIO')->nullable();
            $table->text('telefon')->nullable();
            $table->text('email')->nullable();
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
        Schema::dropIfExists('gruzootpravitel_contacts');
    }
};
