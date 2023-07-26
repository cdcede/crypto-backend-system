<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->enum('status', array('on', 'off', 'suspended'))->nullable();
            $table->string('secret_key')->nullable();
            $table->string('ip_reg')->nullable();
            $table->timestamp('last_access_time')->nullable();
            $table->string('last_access_ip')->nullable();
            $table->string('activation_code')->nullable();
            $table->tinyInteger('login_attempts')->default(0)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            //$table->rememberToken();
            $table->string('home_phone')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->bigInteger('identification_card')->nullable();
            $table->decimal('max_daily_withdraw', 20, 10)->nullable();
            $table->string('lang')->default('es')->nullable();
            $table->boolean('activated')->default(false);
            $table->integer('verified')->default(0);
            $table->boolean('cp_next_login')->default(false);
            $table->boolean('tour')->default(true);
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
        Schema::dropIfExists('users');
    }
}
