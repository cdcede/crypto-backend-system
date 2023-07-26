<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsLoggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets_loggers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('pay_settings_id')->unsigned();
            $table->string('wallet');
            $table->timestamps();
            $table->softDeletes();
            //foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('pay_settings_id')->references('id')->on('pay_settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets_loggers');
    }
}
