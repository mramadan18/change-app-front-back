<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserApiController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\VolunteerWorkController;
use App\Http\Controllers\api\CarriedOutController;
use App\Http\Controllers\api\FavouriteController;
use App\Http\Controllers\api\CategoryOfUserController;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\CompanyController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post('register',[UserApiController::class,'register']);
Route::post('login',[UserApiController::class,'login']);
Route::post('logout',[UserApiController::class,'logout'])->middleware('auth:sanctum');




/**______   USER__________________ */



Route::get('/Volunteer_display', [VolunteerWorkController::class, 'index']);
Route::post('/Volunteer_store', [VolunteerWorkController::class, 'store'])->middleware('auth:sanctum');
Route::put('/Volunteer_update/{id}', [VolunteerWorkController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/Volunteer_delete/{id}', [VolunteerWorkController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('volunteer-works/search', [VolunteerWorkController::class, 'search']);
Route::get('/Volunteer_show/{id}', [VolunteerWorkController::class, 'showVolunteerWorkById'])->middleware('auth:sanctum');

/**################################################## */


Route::post('/Volunteer_store_worker', [CarriedOutController::class, 'store'])->middleware('auth:sanctum');
Route::post('/current_worker_count/{id}', [CarriedOutController::class, 'worker_count'])->middleware('auth:sanctum');




Route::get('/Profile_recipient_work', [UserApiController::class, 'recipient_work'])->middleware('auth:sanctum');
Route::get('/Profile_created_work', [UserApiController::class, 'created_work'])->middleware('auth:sanctum');
Route::get('/Profile_favourite_work', [UserApiController::class, 'favourite'])->middleware('auth:sanctum');
Route::get('/info_user_get', [UserApiController::class, 'get_info'])->middleware('auth:sanctum');
Route::post('/info_user_store', [UserApiController::class, 'store_info'])->middleware('auth:sanctum');
Route::put('/info_user_update', [UserApiController::class, 'update_info'])->middleware('auth:sanctum');
Route::get('/best_oppertinity', [UserApiController::class, 'get_opportunities'])->middleware('auth:sanctum');
Route::post('/change_status_of_worker/{id}', [UserApiController::class, 'change_status'])->middleware('auth:sanctum');



Route::post('/add_to_favourite/{id}', [FavouriteController::class, 'add'])->middleware('auth:sanctum');
Route::post('/delete_favourite/{id}', [FavouriteController::class, 'delete'])->middleware('auth:sanctum');


Route::post('/category_user_store', [CategoryOfUserController::class, 'store'])->middleware('auth:sanctum');



/**___________________________ADMIN */

Route::group(['prefix' => 'admin' , 'middleware' => ['auth:sanctum', 'checkAdmiType']], function () {

    Route::get('/categories_display', [CategoryController::class, 'index']);
    Route::post('/category_store', [CategoryController::class, 'store']);
    Route::put('/category_update/{id}', [CategoryController::class, 'update']);
    Route::delete('/category_delete/{id}', [CategoryController::class, 'destroy']);

    Route::get('/dashboard', [AdminController::class, 'dash']);
    Route::get('/all_volunteer', [AdminController::class, 'all_volunteer']);
    Route::delete('/remove_volunteer/{id}', [AdminController::class, 'destory_volunteer']);
    Route::get('volunteer-works_filtter/search', [AdminController::class, 'search']);
    Route::get('/Volunteer_show/{id}', [AdminController::class, 'showVolunteerWorkById']);

    Route::get('/all_user', [AdminController::class, 'all_user']);
    Route::put('/cange_type_user/{id}', [AdminController::class, 'change_type']);
    Route::put('/cange_status_user/{id}', [AdminController::class, 'change_status']);
    Route::get('/user_info/{id}', [AdminController::class, 'get_info']);

    Route::get('/user_work_volunteer/{id}', [AdminController::class, 'history_volunteer']);
    Route::get('/user_work_carried_out/{id}', [AdminController::class, 'carried_out']);
    Route::post('/find_User_Email', [AdminController::class, 'findUserByEmail']);
    Route::post('/Add_Point__User', [AdminController::class, 'addPoints']);
    Route::post('/Decrese_Point__User', [AdminController::class, 'DecresePoints']);

    Route::post('/current_worker_count/{id}', [AdminController::class, 'worker_count']);
});



/**################### ______________company*/

Route::group(['prefix' => 'company' , 'middleware' => ['auth:sanctum', 'checkComType']], function () {

    Route::get('volunteer-works/search', [CompanyController::class, 'search']);
    Route::get('/all_volunteer', [CompanyController::class, 'index']);
    Route::post('/Volunteer_store', [CompanyController::class, 'store']);
    Route::get('/Volunteer_show/{id}', [CompanyController::class, 'showVolunteerWorkById']);
    Route::put('/Volunteer_update/{id}', [CompanyController::class, 'update']);
    Route::delete('/Volunteer_delete/{id}', [CompanyController::class, 'destroy']);
    Route::post('/current_worker_count/{id}', [CompanyController::class, 'worker_count']);
    Route::get('/user_info/{id}', [CompanyController::class, 'get_info']);
    Route::get('/user_work_volunteer/{id}', [CompanyController::class, 'history_volunteer']);
    Route::get('/user_work_carried_out/{id}', [CompanyController::class, 'carried_out']);
    Route::post('/status_of_worker/{id}', [CompanyController::class, 'change_status']);
});
