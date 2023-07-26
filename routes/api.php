<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('get-images-plan', 'App\Http\Controllers\PlanController@getImagesPlan');
/* Test crom */
Route::get('testDayCron', 'App\Http\Controllers\testController@testDayCron');
Route::get('testMondlyCron', 'App\Http\Controllers\testController@testMondlyCron');
Route::get('testSession', 'App\Http\Controllers\testController@testSession');
Route::post('excel-report', 'App\Http\Controllers\ReportsController@excelReport');


/*  */
Route::post('getQr', 'App\Http\Controllers\DepositsController@getQr');
Route::post('users/export/', 'App\Http\Controllers\UserController@export');
Route::get('verified-access', 'App\Http\Controllers\AuthController@verifiedAuth');

Route::middleware(['verified', 'activity'])->group(function(){

    Route::post('login', 'App\Http\Controllers\AuthController@login');
    
    Route::group(['prefix'=>'user'], function(){
        Route::post('/reset-password-token','App\Http\Controllers\AuthController@resetPassword')->name('api-reset-password-token');
        Route::post('/forgot-password','App\Http\Controllers\AuthController@sendPasswordResetToken')->name('api-reset-password');
    });
});

Route::middleware(['activity'])->group(function(){

    Route::post('register', 'App\Http\Controllers\AuthController@register');

    //Route::post('tracks', 'App\Http\Controllers\TrackController@track');

    Route::post('/resend-register-email','App\Http\Controllers\AuthController@resendRegister');

    Route::post('country-phone-code', 'App\Http\Controllers\GeographyController@countryPhoneCode');

    Route::post('states', 'App\Http\Controllers\GeographyController@states');

    Route::post('create-support', 'App\Http\Controllers\SupportController@createSupport');

    Route::post('plans-landing', 'App\Http\Controllers\TypeController@getPlans');
    /* PLANS */
    Route::post('plans', 'App\Http\Controllers\PlanController@index');

    Route::post('blog-news', 'App\Http\Controllers\BlogController@getBlogNews');

    Route::post('get-blogs', 'App\Http\Controllers\BlogController@index');

    Route::post('categories', 'App\Http\Controllers\CategoryController@getCategories');

    Route::post('validate-username', 'App\Http\Controllers\AuthController@validateUsername');
    
    Route::post('validate-referal', 'App\Http\Controllers\AuthController@validateReferal');

    Route::post('validate-email', 'App\Http\Controllers\AuthController@validateEmail');

    Route::post('activate-account', 'App\Http\Controllers\AuthController@activateAccount');

    Route::post('validate-secret-key', 'App\Http\Controllers\AuthController@validateSecretKey');

});

Route::post('get-banners-no-paginate', 'App\Http\Controllers\BannerController@banners_noPaginate');

//Routes without an api control





Route::middleware(['EnsureTokenIsValid'])->group(function(){
//Protected routes by api auth
Route::middleware(['auth:api', 'activity'])->group(function(){

   
    
    //Protected by admin role
    /* scope admin */
    Route::middleware(['scope:admin'])->group(function(){
        Route::post('validate-payments', 'App\Http\Controllers\DepositsController@validatePayments');
        //parameters
        Route::post('create-level', 'App\Http\Controllers\ParametersController@createLevel');
        Route::post('coinpayments-config', 'App\Http\Controllers\ParametersController@coinpaymentsConfig');
        Route::post('coinpayments-show', 'App\Http\Controllers\ParametersController@getCoinpayments');
        Route::post('reinvention-config', 'App\Http\Controllers\ParametersController@reinventionConfig');
        Route::post('reinvention-show', 'App\Http\Controllers\ParametersController@getReinvention');
        
        Route::post('history-filter-admin', 'App\Http\Controllers\HistoryController@historyTableFilterAdmin');
        Route::post('deposits-admin', 'App\Http\Controllers\DepositsController@depositsAdmin');
        Route::post('show-history', 'App\Http\Controllers\LoginSecurityController@showHistory');

        //Route::resource('user', 'App\Http\Controllers\UserController');
        Route::post('reports/withdraw-pending', 'App\Http\Controllers\ReportsController@withdrawPending');
        Route::post('reports/pdf', 'App\Http\Controllers\ReportsController@pdf');
        //Route::resource('plans', 'App\Http\Controllers\PlanController');
        //Route::post('history-filter-admin', 'App\Http\Controllers\HistoryController@historyTableFilterAdmin');
      /*   Route::post('deposits-admin', 'App\Http\Controllers\DepositsController@depositsAdmin'); */

        /* PLAN */
        Route::post('list-plan', 'App\Http\Controllers\PlanController@listplan');
        Route::post('create-plan', 'App\Http\Controllers\PlanController@store');
        Route::post('update-plan', 'App\Http\Controllers\PlanController@update');
        Route::delete('delete-plan/{id}', 'App\Http\Controllers\PlanController@destroy');
       /*  Route::post('get-images-plan', 'App\Http\Controllers\PlanController@getImagesPlan'); */
        
        /* VIDEO-TUTORIAL */
        Route::post('create-video-tutorial', 'App\Http\Controllers\VideoTutorialController@store');
        Route::post('update-video-tutorial', 'App\Http\Controllers\VideoTutorialController@update');
        Route::post('delete-video-tutorial', 'App\Http\Controllers\VideoTutorialController@destroy');
        

        
        Route::resource('holidays', 'App\Http\Controllers\HolidayController');
        Route::resource('referal', 'App\Http\Controllers\ReferalController');
        Route::resource('types', 'App\Http\Controllers\TypeController');


        /* BLOG */
        Route::post('read-blogs', 'App\Http\Controllers\BlogController@getBlogNews');
        Route::post('create-blog', 'App\Http\Controllers\BlogController@store');
        Route::post('update-blog', 'App\Http\Controllers\BlogController@update');
        Route::post('delete-blog', 'App\Http\Controllers\BlogController@destroy');
        
        Route::post('get_events', 'App\Http\Controllers\NotificationController@get_events');
        
        /* NOTES */
        Route::post('read-notes', 'App\Http\Controllers\NoteController@index');
        Route::post('create-note', 'App\Http\Controllers\NoteController@store');
        Route::post('update-note', 'App\Http\Controllers\NoteController@update');
        Route::post('delete-note', 'App\Http\Controllers\NoteController@destroy');

        /* BANNER */
        Route::post('get-banners', 'App\Http\Controllers\BannerController@index');
        Route::post('get-banner', 'App\Http\Controllers\BannerController@show');
        Route::post('create-banner', 'App\Http\Controllers\BannerController@store');
        Route::post('update-banner', 'App\Http\Controllers\BannerController@update');
        Route::post('delete-banner', 'App\Http\Controllers\BannerController@destroy');

       
        Route::resource('category', 'App\Http\Controllers\CategoryController');
        /* Pay setting */
        Route::post('list-pay-settings', 'App\Http\Controllers\PaySettingsController@index');
        Route::post('insert-pay-settings', 'App\Http\Controllers\PaySettingsController@store');
        Route::post('update-pay-settings', 'App\Http\Controllers\PaySettingsController@update');
        Route::delete('delete-pay-settings/{id}', 'App\Http\Controllers\PaySettingsController@destroy');

        /* Groups */
        Route::get('groups', 'App\Http\Controllers\GroupController@groups');
        Route::post('list-groups', 'App\Http\Controllers\GroupController@index');
        Route::post('insert-group', 'App\Http\Controllers\GroupController@store');
        Route::post('update-group', 'App\Http\Controllers\GroupController@update');
        Route::delete('delete-group/{id}', 'App\Http\Controllers\GroupController@destroy');


    

        Route::resource('user-groups', 'App\Http\Controllers\UserGroupsController');
 
        /* Notifications */
        Route::resource('notifications', 'App\Http\Controllers\NotificationController');

        Route::post('get-notifications', 'App\Http\Controllers\NotificationController@index');
        

        Route::post('coinbase', 'App\Http\Controllers\DepositsController@coinbase');

        Route::post('getKYC', 'App\Http\Controllers\UserController@getKYC'); 

        Route::post('users-filter', 'App\Http\Controllers\UserController@usersTableFilter');

        Route::post('verify-kyc', 'App\Http\Controllers\UserController@verifyKYC');
        
        Route::post('update-user-by-admin', 'App\Http\Controllers\UserController@userUpdateByAdmin');
        
        /* -------------------------- */
        /* |                        | */
        /* |        charts          | */
        /* |                        | */
        /* -------------------------- */

        /*          line           */
        
        Route::post('line-deposit', 'App\Http\Controllers\ReportsController@line_deposit');

        /*          column           */
        
        Route::post('column-register', 'App\Http\Controllers\ReportsController@column_register');
        Route::post('column-deposit', 'App\Http\Controllers\ReportsController@column_deposit');
        /*          Heatmap           */
        
        Route::post('heatmap-register', 'App\Http\Controllers\ReportsController@heatmap_register');
        Route::post('heatmap-deposit', 'App\Http\Controllers\ReportsController@heatmap_deposit');
        
        /*          Mapamundi         */
 
        Route::post('world-map', 'App\Http\Controllers\ReportsController@worldMap');
        Route::post('world-map-more-investment', 'App\Http\Controllers\ReportsController@worldMapMoreInvestment');
        Route::post('world-map-amount-investment', 'App\Http\Controllers\ReportsController@worldMapAmountInvestment');
        Route::post('world-cities', 'App\Http\Controllers\ReportsController@worldCities');
        Route::post('world-cities-more-investment', 'App\Http\Controllers\ReportsController@worldCitiesMoreInvestment');
        Route::post('world-cities-amount-investment', 'App\Http\Controllers\ReportsController@worldCitiesAmountInvestment');

        
    });
 //Protected by nodeserver role for nodejs
 Route::middleware(['scope:leader'])->group(function () {
    Route::post('referal-tree', 'App\Http\Controllers\ReferalStatsController@referalTree');
    Route::middleware(['validate.password'])->group(function(){
        Route::post('add-referrals', 'App\Http\Controllers\UserController@addReferrals');
    });

   
});
    Route::middleware(['scope:superadmin,admin,basic,leader'])->group(function(){

        
       

        Route::middleware(['validate.password'])->group(function(){
            
            Route::post('user-update', 'App\Http\Controllers\UserController@userUpdate');

            Route::post('upload-avatar', 'App\Http\Controllers\UserController@uploadAvatar');
            
            Route::post('uploadKYC', 'App\Http\Controllers\UserController@uploadKYC');

            Route::post('user-wallets', 'App\Http\Controllers\UserWalletController@createAndUpdateWallet');
            
         

        });

            
        Route::post('get_notifications', 'App\Http\Controllers\NotificationController@get_events');
        /* NOTES */
        Route::post('read-notes', 'App\Http\Controllers\NoteController@index');
        Route::post('create-note', 'App\Http\Controllers\NoteController@store');
        Route::post('update-note', 'App\Http\Controllers\NoteController@update');
        Route::post('delete-note', 'App\Http\Controllers\NoteController@destroy');
        Route::post('read-note', 'App\Http\Controllers\NoteController@read_note');
        
        Route::post('verified-auth', 'App\Http\Controllers\AuthController@verifiedAuth');
        
        Route::post('video-tutorials', 'App\Http\Controllers\VideoTutorialController@index');
        Route::post('deposit-fee', 'App\Http\Controllers\ParametersController@depositFee');

        Route::post('validate-phone', 'App\Http\Controllers\UserController@validatePhoneNumber');
        Route::post('send-sms-validation-code', 'App\Http\Controllers\UserController@sendSmsActivationCode');

        Route::post('create-message', 'App\Http\Controllers\UserMessagesController@createMessage');

        Route::post('query-bot', 'App\Http\Controllers\ChatController@queryBot');
        /* IP */
        Route::post('show-ips', 'App\Http\Controllers\IPBlockerController@index');
        Route::post('add-ip', 'App\Http\Controllers\IPBlockerController@store');
        Route::post('update-ip', 'App\Http\Controllers\IPBlockerController@update');
        Route::delete('delete-ip/{id}', 'App\Http\Controllers\IPBlockerController@destroy');

        Route::post('verify-wallets', 'App\Http\Controllers\UserWalletController@verifyWallets');

        Route::post('get-tables', 'App\Http\Controllers\TableReservationController@index');

        Route::post('create-reservation', 'App\Http\Controllers\ReservationController@createReservation');

        Route::post('update-reservation', 'App\Http\Controllers\ReservationController@updateReservation');
        
        Route::post('show-reservation', 'App\Http\Controllers\ReservationController@index');

        Route::post('delete-reservation', 'App\Http\Controllers\ReservationController@destroy');

        Route::post('show-level', 'App\Http\Controllers\ParametersController@index');

        Route::resource('user', 'App\Http\Controllers\UserController');

        Route::post('logout', 'App\Http\Controllers\AuthController@logout');
        
        //Route::resource('user-wallets', 'App\Http\Controllers\UserWalletController');
        Route::get('user-wallets', 'App\Http\Controllers\UserWalletController@index');

        Route::post('wallets-logger', 'App\Http\Controllers\WalletsLoggerController@walletsLogger');
        
        /* Plan */
        Route::post('plans-user', 'App\Http\Controllers\PlanController@plans_user');


        /*  */
        Route::post('pay', 'App\Http\Controllers\PaySettingsController@pay');

        Route::post('types-plans', 'App\Http\Controllers\TypeController@typesAndPlans');

        Route::post('coinpayments', 'App\Http\Controllers\DepositsController@coinpayments');

        Route::post('wagepayments', 'App\Http\Controllers\DepositsController@wagePayments');
        
        Route::post('user-deposits', 'App\Http\Controllers\DepositsController@index');

        Route::post('user-balances', 'App\Http\Controllers\UserBalancesController@showUserBalances');
        
        Route::get('validToken', 'App\Http\Controllers\UserController@validToken');

        Route::post('users-bygroup', 'App\Http\Controllers\UserGroupsController@getUsersGroup');

        Route::post('withdraw-list', 'App\Http\Controllers\WithdrawController@index');

        Route::post('withdraw-payment-list', 'App\Http\Controllers\WithdrawController@payment_list');
         
        Route::post('withdraw-confirmation', 'App\Http\Controllers\WithdrawController@update');

        Route::post('get-amount-withdraw', 'App\Http\Controllers\WithdrawController@getAmountPay');
        
        Route::post('make-withdrawal', 'App\Http\Controllers\WithdrawController@makeWithdrawal');
        //operation history
        Route::post('history-filter', 'App\Http\Controllers\HistoryController@historyTableFilter');

        Route::post('user-crypto-amount', 'App\Http\Controllers\DepositsController@validateCryptoAmount');

        Route::post('make-conversion', 'App\Http\Controllers\HistoryController@makeConversion');
        
        Route::post('cancel-withdraw', 'App\Http\Controllers\HistoryController@cancelWithdraw');
        
        Route::post('get-avatar', 'App\Http\Controllers\UserController@getAvatar');

        Route::group(['prefix'=>'user'], function(){
            
            Route::post('language', 'App\Http\Controllers\AuthController@updateLanguage');
            Route::post('change-password', 'App\Http\Controllers\UserController@changePassword');           

        });
        //Google 2fa
        Route::group(['prefix'=>'2fa'], function(){

            Route::get('/','App\Http\Controllers\LoginSecurityController@show2faForm');
            Route::post('/generateSecret','App\Http\Controllers\LoginSecurityController@generate2faSecret')->name('generate2faSecret');
            Route::post('/enable2fa','App\Http\Controllers\LoginSecurityController@enable2fa')->name('enable2fa');
            Route::post('/disable2fa','App\Http\Controllers\LoginSecurityController@disable2fa')->name('disable2fa');
        
            // 2fa middleware
            Route::post('/2faVerify', function () {
                return true;
                //return redirect(URL()->previous());
            })->name('2faVerify')->middleware('2fa');

        });

    });


 
});


   // test middleware
    Route::get('/test_middleware', function () {
        return "2FA middleware work!";
    })->middleware(['auth', '2fa']);

});