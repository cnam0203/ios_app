<?php
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

Route::post('auth/login', 'Auth\LoginController@login');
Route::get('auth/getTenantId', 'Auth\LoginController@getTenantId');
Route::get('accountDistributor', 'Account\AccountDistributor@index');

Route::group(['middleware' => 'jwtAuth'], function () {
    Route::get('getMenu', 'AppController@getMenu');
    Route::delete('auth/logout', 'Auth\LogoutController@logout');
    Route::get('dashboardrevenue', 'Dashboard\DashboardGSN@index');
    Route::get('dashboarddetailgroupgame', 'Dashboard\DashboardDetailGroupGame@index');
    Route::get('dashboarddetailgame', 'Dashboard\DashboardDetailGame@index');
    Route::get('dashboarddetailcountry', 'Dashboard\DashboardDetailCountry@index');
    Route::get('dashboardn1', 'Dashboard\DashboardGSNN1@index');
    Route::get('dashboardpu', 'Dashboard\DashboardGSNPU@index');
    Route::get('dashboardccu', 'Dashboard\DashboardGSNCCU@index');
    Route::get('dashboarda1', 'Dashboard\DashboardGSNA1@index');
    Route::get('accountActiveAndNew', 'Account\AccountActiveAndNew@index');
    Route::get('accountDistributorDetail', 'Account\DistributorDetail@index');
    Route::get('accountUserAge', 'Account\UserAge@index');
    Route::get('accountNewUser', 'Account\AccountNewUser@index');
    Route::get('accountPlatform', 'Account\AccountPlatform@index');    
    Route::get('accountType', 'Account\AccountType@index');
    Route::get('accountAppVersion', 'Account\AccountAppVersion@index');
    // Route::get('accountOverlap', 'Account\AccOverlap@index');
    Route::get('accountChurn', 'Account\AccChurn@index');
    Route::get('accountDevCheckAppVersion', 'Account\DevCheckAppVersion@index');
    Route::get('deviceActiveAndNew', 'Device\DeviceActiveAndNew@index');
    Route::get('deviceDistributor', 'Device\DeviceDistributor@index');
    Route::get('devicePlatform', 'Device\DevicePlatform@index');
    Route::get('deviceModel', 'Device\DeviceModel@index');
    Route::get('deviceOSVersion', 'Device\DeviceOSVersion@index');
    Route::get('deviceNetworkName', 'Device\DeviceNetworkName@index');
    Route::get('devicePackageName', 'Device\DevicePackageName@index');
    Route::get('deviceAppVersion', 'Device\DeviceAppVersion@index');
    Route::get('game/tute/action', 'tute\InGameTuteAction@index');
    Route::get('game/tute/gold', 'tute\InGameTuteGold@index');
});
