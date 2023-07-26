<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('plan_id')->unsigned();
            $table->timestamp('deposit_date');
            $table->timestamp('last_pay_day');
            $table->enum('status', array('on', 'off'))->nullable();
            $table->bigInteger('q_pays')->default(0);
            $table->decimal('amount', 20, 10)->default(0.00);
            $table->decimal('actual_amount', 20, 10)->default(0.00);
            $table->integer('pay_id')->default(0);
            $table->decimal('compound', 10, 2)->nullable();
            $table->timestamp('dde')->nullable();
            $table->decimal('unit_amount', 20, 10)->default(1.00);
            $table->tinyInteger('bonus_flag')->default(0);
            $table->decimal('init_amount', 20, 10)->default(0.00);
            $table->string('type', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            //foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposits');
    }
}
