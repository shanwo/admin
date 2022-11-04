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

Route::prefix('area')->group(function () {
    Route::get('lists', 'AreaController@lists');
});
//用户
Route::prefix('user')->group(function () {
    Route::get('lists', 'UserController@lists');
    Route::get('info', 'UserController@info');
    Route::get('attention', 'UserController@attention');
    Route::get('browse', 'UserController@browse');
    Route::post('status', 'UserController@status');
});
//标签
Route::prefix('tag')->group(function () {
    Route::get('lists', 'TagController@lists');
    Route::get('info', 'TagController@info');
    Route::post('add', 'TagController@add');
    Route::post('edit', 'TagController@edit');
    Route::post('status', 'TagController@status');
});
//配置
Route::prefix('config')->group(function () {
    Route::get('lists', 'ConfigController@lists');
    Route::get('info', 'ConfigController@info');
    Route::post('add', 'ConfigController@add');
    Route::post('edit', 'ConfigController@edit');
    Route::post('status', 'ConfigController@status');
});
//上传
Route::prefix('upload')->group(function () {
    Route::get('lists', 'UploadController@lists');
    Route::get('secret', 'UploadController@secret');
    Route::post('file', 'UploadController@file');
    Route::post('del', 'UploadController@del');
});
//反馈
Route::prefix('feedback')->group(function () {
    Route::get('lists', 'FeedbackController@lists');
    Route::get('info', 'FeedbackController@info');
    Route::post('edit', 'FeedbackController@edit');
});
//推送
Route::prefix('push')->group(function () {
    Route::get('lists', 'PushController@lists');
    Route::get('info', 'PushController@info');
    Route::post('add', 'PushController@add');
    Route::post('edit', 'PushController@edit');
    Route::post('status', 'PushController@status');
});
//公众号
Route::prefix('official')->group(function () {
    Route::namespace('Official')->group(function () {
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
Route::prefix('sw')->group(function () {
    Route::namespace('Sw')->group(function () {
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
            Route::get('attention', 'PageController@attention');
            Route::get('browse', 'PageController@browse');
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
//支付
        Route::prefix('pay')->group(function () {
            Route::get('lists', 'PayController@lists');
            Route::get('info', 'PayController@info');
            Route::post('add', 'PayController@add');
            Route::post('edit', 'PayController@edit');
            Route::post('status', 'PayController@status');
        });
//订单
        Route::prefix('order')->group(function () {
            Route::get('lists', 'OrderController@lists');
            Route::post('refund', 'OrderController@refund');
        });
    });
});
Route::prefix('hy')->group(function () {
    Route::namespace('Hy')->group(function () {
//场地
        Route::prefix('venue')->group(function () {
            Route::get('lists', 'VenueController@lists');
            Route::get('info', 'VenueController@info');
            Route::post('add', 'VenueController@add');
            Route::post('edit', 'VenueController@edit');
            Route::post('status', 'VenueController@status');
            Route::post('cache', 'VenueController@cache');
        });
//订单
        Route::prefix('order')->group(function () {
            Route::get('lists', 'OrderController@lists');
            Route::post('refund', 'OrderController@refund');
        });
//上传
        Route::prefix('upload')->group(function () {
            Route::get('lists', 'UploadController@lists');
            Route::post('file', 'UploadController@file');
            Route::post('del', 'UploadController@del');
        });
    });
});

