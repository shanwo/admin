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
    Route::post('del', 'LinkController@del');
});
//模板
Route::prefix('template')->group(function () {
    Route::get('lists', 'TemplateController@lists');
    Route::get('info', 'TemplateController@info');
    Route::post('add', 'TemplateController@add');
    Route::post('edit', 'TemplateController@edit');
    Route::post('del', 'TemplateController@del');
});
//页面
Route::prefix('page')->group(function () {
    Route::get('lists', 'PageController@lists');
    Route::post('sort', 'PageController@sort');
    Route::post('del', 'PageController@del');
});
//装饰
Route::prefix('decoration')->group(function () {
    Route::get('lists', 'DecorationController@lists');
    Route::get('info', 'DecorationController@info');
    Route::post('add', 'DecorationController@add');
    Route::post('edit', 'DecorationController@edit');
    Route::post('del', 'DecorationController@del');
});
//教程
Route::prefix('tutorials')->group(function () {
    Route::get('lists', 'TutorialsController@lists');
    Route::get('info', 'TutorialsController@info');
    Route::post('add', 'TutorialsController@add');
    Route::post('edit', 'TutorialsController@edit');
    Route::post('del', 'TutorialsController@del');
});
//分类
Route::prefix('sort')->group(function () {
    Route::get('lists', 'SortController@lists');
    Route::post('add', 'SortController@add');
    Route::post('del', 'SortController@del');
});
//订单
Route::prefix('order')->group(function () {
    Route::get('lists', 'OrderController@lists');
});
//广告
Route::prefix('ad')->group(function () {
    Route::get('lists', 'AdController@lists');
    Route::get('info', 'AdController@info');
    Route::post('add', 'AdController@add');
    Route::post('edit', 'AdController@edit');
    Route::post('status', 'AdController@status');
    Route::post('del', 'AdController@del');
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
    Route::post('status', 'FeedbackController@status');
});
//关注
Route::prefix('attention')->group(function () {
    Route::get('lists', 'AttentionController@lists');
    Route::post('status', 'AttentionController@status');
});
//浏览
Route::prefix('browse')->group(function () {
    Route::get('lists', 'BrowseController@lists');
    Route::post('status', 'BrowseController@status');
});
//支付
Route::prefix('pay')->group(function () {
    Route::get('lists', 'PayController@lists');
    Route::post('send', 'PayController@send');
    Route::post('result', 'PayController@result');
    Route::post('refund', 'PayController@refund');
});
//测试接口
Route::prefix('test')->group(function () {
    Route::get('check', 'TestController@check');
    Route::get('info', 'TestController@info');
    Route::get('all', 'TestController@all');
    Route::get('image', 'TestController@image');
});

