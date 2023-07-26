<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use DB;
use Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Schema::hasTable('parameters')) {
            $coinpayments_query = DB::table('parameters')
            ->select('coinpayments')
            ->first();

            $coinpayments = json_decode($coinpayments_query->coinpayments, true);
            if ($coinpayments) {
                Config::set('coinpayment.public_key', $coinpayments['public_key']);
                Config::set('coinpayment.private_key', $coinpayments['private_key']);
                Config::set('coinpayment.ipn.activate', $coinpayments['ipn_activate']);
                Config::set('coinpayment.ipn.config.coinpayment_merchant_id', $coinpayments['marchant_id']);
                Config::set('coinpayment.ipn.config.coinpayment_ipn_secret', $coinpayments['ipn_secret']);
                Config::set('coinpayment.ipn.config.coinpayment_ipn_debug_email', $coinpayments['ipn_debug_email']);
                Config::set('coinpayment.default_currency', $coinpayments['currency']);
            } 
        }

        //dd(config('coinpayment'));
        
    }
}
