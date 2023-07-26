<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('level');
            $table->string('name')->nullable();
            $table->decimal('from_value', 20, 10)->nullable();
            $table->decimal('to_value', 20, 10)->nullable();
            $table->decimal('percent', 20, 10)->nullable();
            $table->decimal('percent_daily', 20, 10)->nullable();
            $table->decimal('percent_weekly', 20, 10)->nullable();
            $table->decimal('percent_monthly', 20, 10)->nullable();
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
        Schema::dropIfExists('referal');
    }
}
