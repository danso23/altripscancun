<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router){

    //Servicio
    $router->post('/createtrip','Servicio@getInfoTrip');    
    $router->post('/confirmar','Servicio@ConfirmarPago');
    $router->post('/create','Servicio@savetrip');

    //Get Catalogos
    $router->get('/get','GetCatalogos@getCatalogos');
    $router->get('/getElement','GetCatalogos@getElement');
    
    //Servicios Paypal
    $router->post('/createorder','Servicio@generaOrden');
    
    //Rutas de prueba   
    $router->get('/seepay','Servicio@vistaPaypal');
    $router->get('/boton','GetCatalogos@obtenerElemento');
    $router->get('/test','Servicio@testJSON');
    $router->get('/testq','Servicio@testSelect');


    //Admin
    $router->post('/setuser','AdministradorController@CreateUser');
    $router->post('/login','AdministradorController@login');
    $router->post('/logout','AdministradorController@logout');
    

});


$router->group(['prefix' => 'admin','middleware' => [/*'auth',*/'check']],function () use ($router){
    
    $router->get('/get_hotels', 'GetCatalogos@GetHotelEdit');    
    $router->post('/delete','AdministradorController@login');
    $router->post('/d_hotel','GetCatalogos@DeleteItemHotel');
    $router->post('/e_hotel','GetCatalogos@UpdateItemHotel');

    $router->get('/g_zn', 'GetCatalogos@GetZonas');
    $router->post('/e_zone','GetCatalogos@DeleteItemZones');

    $router->get('/g_r', 'GetCatalogos@GetReservation');

    //Área de Reservaciones
    $router->post('/u_trip', 'GetCatalogos@update_travel');


});