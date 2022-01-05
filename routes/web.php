<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', 'https://shanwo.cc');

//用户
Route::prefix('user')->group(function () {
    Route::get('lists', 'UserController@lists');
    Route::get('info', 'UserController@info');
    Route::post('status', 'UserController@status');
});
//链接
Route::prefix('link')->group(function () {
    Route::get('lists', 'LinkController@lists');
    Route::get('info', 'LinkController@info');
    Route::post('add', 'LinkController@add');
    Route::post('edit', 'LinkController@edit');
    Route::post('status', 'LinkController@status');
});
//模板
Route::prefix('template')->group(function () {
    Route::get('lists', 'TemplateController@lists');
    Route::get('info', 'TemplateController@info');
    Route::post('add', 'TemplateController@add');
    Route::post('edit', 'TemplateController@edit');
    Route::post('status', 'TemplateController@status');
});
//页面
Route::prefix('page')->group(function () {
    Route::get('lists', 'PageController@lists');
    Route::get('info', 'PageController@info');
    Route::post('sort', 'PageController@sort');
    Route::post('status', 'PageController@status');
});
//装饰
Route::prefix('decoration')->group(function () {
    Route::get('lists', 'DecorationController@lists');
    Route::get('info', 'DecorationController@info');
    Route::post('add', 'DecorationController@add');
    Route::post('edit', 'DecorationController@edit');
    Route::post('status', 'DecorationController@status');
});
//教程
Route::prefix('tutorials')->group(function () {
    Route::get('lists', 'TutorialsController@lists');
    Route::get('info', 'TutorialsController@info');
    Route::post('add', 'TutorialsController@add');
    Route::post('edit', 'TutorialsController@edit');
    Route::post('status', 'TutorialsController@status');
});
//分类
Route::prefix('sort')->group(function () {
    Route::get('lists', 'SortController@lists');
    Route::get('info', 'SortController@info');
    Route::post('add', 'SortController@add');
    Route::post('edit', 'SortController@edit');
    Route::post('del', 'SortController@del');
});
//订单
Route::prefix('order')->group(function () {
    Route::get('lists', 'OrderController@lists');
    Route::post('refund', 'OrderController@refund');
});
//广告
Route::prefix('ad')->group(function () {
    Route::get('lists', 'AdController@lists');
    Route::get('info', 'AdController@info');
    Route::post('add', 'AdController@add');
    Route::post('edit', 'AdController@edit');
    Route::post('status', 'AdController@status');
});
//上传
Route::prefix('upload')->group(function () {
    Route::get('lists', 'UploadController@lists');
    Route::post('file', 'UploadController@file');
    Route::post('del', 'UploadController@del');
});
//反馈
Route::prefix('feedback')->group(function () {
    Route::get('lists', 'FeedbackController@lists');
    Route::get('info', 'FeedbackController@info');
    Route::post('edit', 'FeedbackController@edit');
});
//配置
Route::prefix('config')->group(function () {
    Route::get('lists', 'ConfigController@lists');
    Route::get('info', 'ConfigController@info');
    Route::post('add', 'ConfigController@add');
    Route::post('edit', 'ConfigController@edit');
    Route::post('status', 'ConfigController@status');
});
//支付
Route::prefix('pay')->group(function () {
    Route::get('lists', 'PayController@lists');
    Route::get('info', 'PayController@info');
    Route::post('add', 'PayController@add');
    Route::post('edit', 'PayController@edit');
    Route::post('status', 'PayController@status');
});
//公众号
Route::prefix('official')->group(function () {
    Route::namespace('Official')->group(function () {
        Route::prefix('url')->group(function () {
            Route::get('shorten', 'UrlController@shorten');
        });
        Route::prefix('menu')->group(function () {
            Route::get('info', 'MenuController@info');
            Route::post('update', 'MenuController@update');
        });
        Route::prefix('material')->group(function () {
            Route::get('lists', 'MaterialController@lists');
            Route::get('info', 'MaterialController@info');
            Route::get('del', 'MaterialController@del');
        });
        Route::prefix('reply')->group(function () {
            Route::get('lists', 'ReplyController@lists');
            Route::get('info', 'ReplyController@info');
            Route::post('add', 'ReplyController@add');
            Route::post('edit', 'ReplyController@edit');
            Route::post('status', 'ReplyController@status');
        });
    });
});
//标签
Route::prefix('tag')->group(function () {
    Route::get('lists', 'TagController@lists');
    Route::get('info', 'TagController@info');
    Route::post('add', 'TagController@add');
    Route::post('edit', 'TagController@edit');
    Route::post('status', 'TagController@status');
});
//测试接口
Route::prefix('test')->group(function () {
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});

