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

//Cargo la clase del middleware de autenticacion:
use App\Http\Middleware\ApiAuthMiddleware;
//use Symfony\Component\Routing\Route; -> Lo comento el 17/02/2020 porque no se usa mas en la version de laravel.
use Illuminate\Support\Facades\Route;

// RUTAS DE PRUEBA: 
/*
Route::get('/', function () {
    return view('welcome');
});

    

Route::get('/prueba/{nombre?}', function($nombre = null){
    $texto = '<h2>Texto desde una ruta por variable.</h2>';
    $texto .= 'Nombre: '.$nombre;
    return view('prueba',array(
        'texto' => $texto
    )) ;
});


Route::get('/animales', 'pruebaController@index');
Route::get('/animales/test-orm', 'pruebaController@testORM');
*/

// RUTAS DE LA API:
    //RUTAS DE PRUEBA:
/*
    Route::get('/user/pruebas', 'UserController@pruebas');
    Route::get('/category/pruebas', 'CategoryController@pruebas');
    Route::get('/post/pruebas', 'PostController@pruebas');
*/
    //RUTAS DEL UserController:
    Route::post('/api/register', 'UserController@register');
    Route::post('/api/login', 'UserController@login');
    Route::put('/api/user/update', 'UserController@update');
    Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
    Route::get('/api/user/userDetail/{id}', 'UserController@userDetail');

    //RUTAS DEL CategoryController:
    Route::resource('/api/category', 'CategoryController');

    //RUTAS DEL PostController:
    Route::resource('/api/post', 'PostController');
    Route::post('/api/post/upload', 'PostController@upload');
    Route::get('/api/post/image/{filename}', 'PostController@getImage');
    Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');