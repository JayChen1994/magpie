<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'web'], function () use ($router) {
    $router->get('/order/get-detail', ['uses' => 'OrderController@getOrderDetail']); // 订单详情
    $router->post('/pay/toPay', ['uses' => 'PayController@toPay']); // 去支付
    $router->get('/order/list', ['uses' => 'OrderController@list']); // 订单列表
    $router->get('/order/use-list', ['uses' => 'OrderController@useList']); // 历史记录
    $router->get('/order/to-use', ['uses' => 'OrderController@toUse']); // 同使用
    $router->get('/order/admin-use-log', ['uses' => 'OrderController@adminUseLog']); // 同使用
});

$router->post('/pay/notify', ['uses' => 'PayController@notify']); // 微信回调
