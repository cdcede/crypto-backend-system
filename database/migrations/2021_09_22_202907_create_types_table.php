<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('q_days')->nullable();
            $table->decimal('min_deposit', 20, 10)->nullable();
            $table->decimal('max_deposit', 20, 10)->nullable();
            $table->string('period')->nullable();
            $table->enum('status', array('on', 'off', 'suspended'))->nullable();
            $table->enum('return_profit', array('0', '1'))->nullable();
            $table->decimal('return_profit_percent', 20, 10)->nullable();
            $table->decimal('percent', 20, 10)->nullable();
            $table->integer('pay_to_egold_directly')->default('0');
            $table->integer('use_compound')->default('0');
            $table->integer('work_week')->default('0');
            $table->integer('parent')->default('0');
            $table->tinyInteger('withdraw_principal')->unsigned()->default('0');
            $table->decimal('withdraw_principal_percent', 20, 10)->default('0.00');
            $table->integer('withdraw_principal_duration')->unsigned()->default('0');
            $table->decimal('compound_min_deposit', 20, 10)->nullable();
            $table->decimal('compound_max_deposit', 20, 10)->nullable();
            $table->tinyInteger('compound_percents_type')->nullable();
            $table->decimal('compound_min_percent', 20, 10)->nullable();
            $table->decimal('compound_max_percent', 20, 10)->nullable();
            $table->text('compound_percents')->nullable();
            $table->tinyInteger('closed')->unsigned()->default('0');
            $table->integer('withdraw_principal_duration_max')->unsigned()->default('0');
            $table->text('dsc')->nullable();
            $table->integer('hold')->default('0');
            $table->integer('delay')->default('0');
            $table->integer('ordering')->default('0');
            $table->integer('deposits_limit_num')->nullable();
            $table->decimal('rpcp', 15, 2)->default('0.00')->nullable();
            $table->decimal('ouma', 15, 2)->default('0.00')->nullable();
            $table->integer('pax_utype')->default('0');
            $table->integer('dawifi')->default('0');
            $table->bigInteger('pae')->default('0');
            $table->decimal('amount_mult', 20, 10)->default('1.0000000000');
            $table->text('data')->nullable();
            $table->decimal('rc', 6, 2)->nullable();
            $table->integer('allow_internal_deps')->default('1');
            $table->integer('allow_external_deps')->default('1');
            $table->integer('s')->default('0');
            $table->integer('move_to_plan')->unsigned()->default('0');
            $table->decimal('move_to_plan_perc', 10, 4)->default('100.0000');
            $table->tinyInteger('compound_return')->default('0');
            $table->string('power_unit')->nullable();
            $table->decimal('power_rate', 20, 8)->default('1.00000000');
            $table->integer('order')->nullable();
            $table->boolean('currency')->default(false);
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('custom_amount')->nullable();
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
        Schema::dropIfExists('types');
    }
}
