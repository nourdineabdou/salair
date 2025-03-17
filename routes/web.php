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
Route::get('refrech_copmptes',[SalaireController::class,'snimBanque']);
Route::group(['middleware' => 'auth'], function (){


   
    Route::get('/', [SalaireController::class, 'index'])->name('home');
    Route::get('/refrech/delta', [SalaireController::class, 'refrech_data'])->name('refrech');
    Route::get('get/{id?}', [SalaireController::class, 'get']);
    Route::post('chercher',[SalaireController::class,'store']);
    Route::get('download/{id}',[SalaireController::class,'dowload_file'])->name('download');
    Route::get('thisDay',[SalaireController::class,'historiques'])->name('historique');
    Route::get('historique',[SalaireController::class,'tous'])->name('tous');
    
    //Route::get('req/{id?}',[SalaireController::class,'get_formation_compte']);
});

Auth::routes();

Route::post('login',[UserController::class, 'authenticate'])->name('login');
