<?php

use App\Models\User;
use App\Notifications\ResetPassword;
use Facade\FlareClient\Api;
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

// AuthController
Route::post('auth/refresh', 'AuthController@refresh');
Route::post('forgot-password', 'Api\ResetPasswordController@forgotPassword');
Route::post('sso-authenticated', 'AuthController@ssoAuthentication');
Route::middleware(['active'])->group(function () {
    Route::post('auth/login', 'AuthController@login');
    Route::put('reset-password/{token}', 'Api\ResetPasswordController@resetPassword');
});

Route::middleware(['auth:api', 'active'])->group(function () {
    // AuthController
    Route::prefix('auth')->group(function () {
        Route::post('logout', 'AuthController@logout');
        Route::get('me', 'AuthController@me');
    });

    Route::get('/users', 'Api\UserController@index');
    Route::get('/service-users', 'Api\ServiceUserController@index');
    Route::get('/service-users/search', 'Api\ServiceUserController@search');
    Route::post('/service-users', 'Api\ServiceUserController@store');
    Route::get('/service-users/{id}', 'Api\ServiceUserController@show');
    Route::get('/mail-templates', 'Api\MailTemplateController@index');
    Route::get('/list-users', 'Api\UserController@getAll');
    Route::get('/tree-folder', 'Api\FolderController@getTreeFolder');
    // Route::get('/document-types', 'Api\DocumentTypeController@index');

    // PositionController
    Route::get('/positions', 'Api\PositionController@index');

    // UserController
    Route::prefix('users')->middleware(['admin'])->group(function () {
        Route::post('/', 'Api\UserController@store');
        Route::delete('/delete', 'Api\UserController@bulkDelete');
        Route::get('/{id}', 'Api\UserController@show');
        Route::put('/{id}', 'Api\UserController@update');
        Route::post('/restore', 'Api\UserController@restore');
        Route::delete('/{id}', 'Api\UserController@destroy');
        Route::post('{id}/change-status', 'Api\UserController@changeStatus');
        Route::post('/setting-role', 'Api\UserController@changeRole');
        Route::post('/change-office', 'Ap@changeOffice');
        Route::post('/change-position', 'Api\UserController@changePosition');
        Route::post('/change-password', 'Api\UserController@changePassword');
    });

    // BranchController
    Route::prefix('branches')->group(function () {
        Route::get('/', 'Api\BranchController@index');
        Route::get('/{id}', 'Api\BranchController@show');
        Route::put('/{id}', 'Api\BranchController@update');
        Route::delete('/{id}', 'Api\BranchController@destroy');
    });

    // DivisionController
    Route::prefix('divisions')->group(function () {
        Route::get('/', 'Api\DivisionController@index');
        Route::get('/{id}', 'Api\DivisionController@show');
        Route::put('/{id}', 'Api\DivisionController@update');
        Route::delete('/{id}', 'Api\DivisionController@destroy');
    });

    // OfficeController
    Route::prefix('offices')->group(function () {
        Route::get('/', 'Api\OfficeController@index');
        Route::get('/{id}', 'Api\OfficeController@show');
    });

    // StoreController
    Route::prefix('stores')->group(function () {
        Route::get('/', 'Api\StoreController@index');
        Route::get('/{id}', 'Api\StoreController@show');
    });

    // Setting profile
    Route::prefix('profile')->group(function () {
        Route::get('/', 'Api\UserSettingController@index');
        Route::put('/', 'Api\UserSettingController@update');
    });

    Route::post('/change-password', 'Api\UserSettingController@changePassword');

    // TagController
    Route::prefix('tags')->group(function () {
        Route::get('/', 'Api\TagController@index');
        Route::post('/', 'Api\TagController@store');
        Route::get('/{id}', 'Api\TagController@show');
        Route::put('/{id}', 'Api\TagController@update');
        Route::delete('/{id}', 'Api\TagController@destroy');
    });

    // ServiceUserController

    Route::prefix('service-users')->middleware(['admin'])->group(function () {
        Route::delete('/delete', 'Api\ServiceUserController@bulkDelete');
        Route::put('/{id}', 'Api\ServiceUserController@update');
        Route::delete('/{id}', 'Api\ServiceUserController@destroy');
        Route::post('/{id}/setting-file-set-access', 'Api\ServiceUserController@setFileSetAccess');
        Route::post('import', 'Api\ServiceUserController@import');
    });

    // MailTemplateController

    Route::prefix('mail-templates')->middleware(['admin'])->group(function () {
        Route::post('/', 'Api\MailTemplateController@store');
        Route::delete('/delete', 'Api\MailTemplateController@bulkDelete');
        Route::get('/{id}', 'Api\MailTemplateController@show');
        Route::put('/{id}', 'Api\MailTemplateController@update');
        Route::delete('/{id}', 'Api\MailTemplateController@destroy');
    });

    // ImportManagementSystemController
    Route::post('imports', 'Api\ImportManagementSystemController@import')->middleware(['admin']);

    // FolderController
    Route::prefix('folders')->group(function () {
        Route::get('/', 'Api\FolderController@index');
        Route::post('/', 'Api\FolderController@store');
        Route::get('/{id}', 'Api\FolderController@show');
        Route::put('/{id}', 'Api\FolderController@update');
        Route::delete('/{id}', 'Api\FolderController@destroy');
        Route::get('/get-with-branch/{office_id}', 'Api\FolderController@getListWithBranch');
    });

    // DocumentTypeController
    Route::prefix('/document-types')->group(function () {
        Route::get('/', 'Api\DocumentTypeController@index'); // show doc da co okay 4
        Route::post('/', 'Api\DocumentTypeController@store'); // add okay
        Route::get('/{id}', 'Api\DocumentTypeController@show'); //show doc theo id okay
        Route::put('/{id}', 'Api\DocumentTypeController@update'); //update okay
        Route::delete('/{id}', 'Api\DocumentTypeController@destroy');
        // Route::delete('/delete', 'Api\DocumentTypeController@bulkDelete');
    });

    //DocumentObjectController
    Route::prefix('/document-objects')->group(function () {
        Route::get('/', 'Api\DocumentObjectController@index');
    });

    // DocumentController
    Route::prefix('documents')->group(function () {
        Route::get('/', 'Api\DocumentController@index');
        Route::post('/', 'Api\DocumentController@store');
        Route::get('/deleted-files', 'Api\DocumentController@searchDeletedFiles')->middleware(['is.not.staff']);
        Route::get('/{id}', 'Api\DocumentController@show')->middleware('document.permission');
        Route::put('/{id}', 'Api\DocumentController@update')->middleware('document.permission');
        Route::delete('/{id}', 'Api\DocumentController@destroy')->middleware('document.permission');
        Route::post('/{id}/setting-document-access', 'Api\DocumentController@setDocumentAccess')->middleware('document.permission');
        Route::post('/{id}/setting-alert-mail', 'Api\DocumentController@settingAlertMail')->middleware('document.permission');
        Route::post('/search', 'Api\DocumentController@search');
        Route::post('/create-file-with-doc', 'Api\DocumentController@createFileWithDoc');
    });

    // FileController
    Route::prefix('files')->group(function () {
        Route::get('/{id}/versions', 'Api\FileController@fileVersions');
        Route::get('/{id}/preview', 'Api\FileController@preview');
        Route::get('/{id}/download', 'Api\FileController@download');
        Route::delete('/{id}', 'Api\FileController@destroy');
        Route::post('/{id}/restore', 'Api\FileController@restore')->middleware(['is.not.staff']);
        Route::delete('/{id}/delete-permanently', 'Api\FileController@deletePermanently')->middleware(['is.not.staff']);
    });

    // FileHistoryController
    Route::get('/file-histories/{id}/download', 'Api\FileHistoryController@downloadOldVersion');
    Route::get('/file-histories/{id}/preview', 'Api\FileHistoryController@previewOldVersion');

    // AttributeController
    Route::get('/attributes', 'Api\AttributeController@index');

    // GeneralSettingController
    Route::prefix('general-setting')->middleware(['admin'])->group(function () {
        Route::get('/', 'Api\GeneralSettingController@view');
        Route::put('/', 'Api\GeneralSettingController@update');
    });
});
