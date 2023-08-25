<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use App\Helpers\ConfigHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Plugins\WebPublishModule\Controllers\WebController;

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

Route::name('web-publish-module.')->group(function () {
    // admin
    Route::prefix('admin')->name('admin.')->middleware(['panel', 'panelAuth'])->group(function () {
        Route::get('/', [WebController::class, 'index'])->name('index');
        Route::post('update', [WebController::class, 'update'])->name('update');
    });

    // publish
    Route::prefix('publish')->name('publish.')->group(function () {
        $path = ConfigHelper::fresnsConfigByItemKey('web_publish_module_auth_key') ?? Str::random(32);

        Route::get($path, [WebController::class, 'editor'])->name('editor');
        Route::post('web-publish-submit', [WebController::class, 'webSubmit'])->name('web.submit');
    });
});
