<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('deposit_id')->unsigned();
            $table->bigInteger('pay_id')->unsigned();
            $table->decimal('amount',20,10);
            $table->string('type',50);
            $table->text('description');
            $table->decimal('actual_amount',20,10);
            $table->timestamps();
            $table->softDeletes();

            //foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('deposit_id')->references('id')->on('deposits');
            $table->foreign('pay_id')->references('id')->on('pay_settings');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
}
