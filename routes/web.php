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
    return '喜鹊到家享受生活...功能开发中...';
});

$router->get('/api/index', ['uses' => 'IndexController@index']);

$router->get('/api/packages', ['uses' => 'IndexController@packages']);

$router->get('/api/addpackage', [['middleware' => 'auth'], 'uses' => 'IndexController@addPackage']);

$router->get('/api/user/authorize', ['uses' => 'IndexController@authorizeUser']);

$router->get('/api/user/jspackage', [['middleware' => 'auth'], 'uses' => 'IndexController@getJsPackage']);

$router->get('/api/user/islogin', [['middleware' => 'auth'], 'uses' => 'IndexController@isLogin']);

$router->group(['middleware' => ['web', 'auth']], function () use ($router) {
    $router->get('/api/order/get-detail', ['uses' => 'OrderController@getOrderDetail']); // 订单详情
    $router->post('/api/pay/toPay', ['uses' => 'PayController@toPay']); // 去支付
    $router->get('/api/order/list', ['uses' => 'OrderController@list']); // 订单列表
    $router->get('/api/order/use-list', ['uses' => 'OrderController@useList']); // 历史记录
    $router->get('/api/order/to-use', ['uses' => 'OrderController@toUse']); // 同使用
    $router->get('/api/order/admin-use-log', ['uses' => 'OrderController@adminUseLog']); // 同使用
    $router->get('/api/order/admin-paid-list', ['uses' => 'OrderController@adminPaidList']);
});

$router->post('/api/pay/notify', ['uses' => 'PayController@notify']); // 微信回调
