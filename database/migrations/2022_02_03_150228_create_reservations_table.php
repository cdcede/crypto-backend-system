<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('note');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('table_id')->unsigned();
            $table->timestamp('from_date');
            $table->timestamp('to_date');
            $table->boolean('status');
            $table->timestamps();
            $table->softDeletes();
            
            //foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('table_id')->references('id')->on('table_reservations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
}
