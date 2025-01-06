<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalaireController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'auth'], function (){
    Route::get('/', function () {
        return view('index');
    })->name('home');
    Route::get('get/{id?}', [SalaireController::class, 'get']);
    Route::post('chercher',[SalaireController::class,'store']);
    Route::get('download/{id}',[SalaireController::class,'dowload_file']);
});

Auth::routes();

Route::post('login',[UserController::class, 'authenticate'])->name('login');
