<?php

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

/*
 * 我们为 get 方法传递了两个参数，第一个参数指明了 URL，第二个参数指明了处理该 URL 的控制器动作。
 * get 表明这个路由将会响应 GET 请求，并将请求映射到指定的控制器动作上。
 * 比方说，我们向 http://sample.test/ 发出了一个请求，则该请求将会由 StaticPagesController 的 home 方法进行处理。
 * 我们将在下节创建 StaticPagesController，为你讲解控制器在收到请求后如何进行相关操作。*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/','StaticPagesController@home') -> name('home');
Route::get('/help','StaticPagesController@help') -> name('help');
Route::get('/about','StaticPagesController@about') -> name('about');
Route::get('/sign','UserController@create') -> name('signup');
/*
 * Laravel 为我们提供了 resource 方法来定义用户资源路由
 */
Route::resource('users','UserController');
#显示登录页面
Route::get('login', 'SessionsController@create')->name('login');
#创建新会话（登录）
Route::post('login', 'SessionsController@store')->name('login');
#	销毁会话（退出登录）
Route::delete('logout', 'SessionsController@destroy')->name('logout');
# 邮件激活路径
Route::get('signup/confirm/{token}', 'UserController@confirmEmail')->name('confirm_email');
/*
 * 密码重设
 */
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');