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

# Admin interfaces
Route::get('/admin', 'Admin\DashbaordController@index');
Route::resource('/admin/upload', 'Admin\UploadAppController');
Route::resource('/admin/rules', 'Admin\AnalysisToolController');
Route::patch('/admin/rules/default/{id}', 'Admin\AnalysisToolController@setDefault');
Route::resource('/admin/config', 'Admin\SiteConfigController');
Route::resource('/admin/published', 'Admin\AnalyzedAppsController');

# Web Storage
Route::resource('/', 'HomeController');
Route::resource('/home', 'HomeController');

// Auth::routes();

// Authentication Routes
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Disable as for the mb_decode_numericentity
// The project can be extended to add multiple users later

// Registration Routes
// Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
// Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes
// Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// Route::post('password/reset', 'Auth\ResetPasswordController@reset');
