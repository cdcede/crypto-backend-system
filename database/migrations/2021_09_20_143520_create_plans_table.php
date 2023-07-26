<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('q_days')->nullable();
            $table->decimal('min_deposit', 20, 10)->nullable();
            $table->decimal('max_deposit', 20, 10)->nullable();
            $table->decimal('percent', 10, 2)->nullable();
            $table->string('period')->nullable();
            $table->json('days')->nullable();
            $table->decimal('deposit_fee', 10, 2)->nullable();
            $table->decimal('withdraw_fee', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->json('custom_amount')->nullable();
            $table->enum('status', array('on', 'off','suspended'))->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
