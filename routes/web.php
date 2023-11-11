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
    $router->post('/savetrip','Servicio@savetrip');

    //Get Catalogos
    $router->get('/get','GetCatalogos@getCatalogos');
    $router->get('/getElement','GetCatalogos@getElement');
    
    //Pruebas Paypal
    $router->post('/createorder','Servicio@generaOrden');
    $router->post('/confirmar','Servicio@ConfirmarPago');
       
    $router->get('/seepay','Servicio@vistaPaypal');

    $router->get('/boton','GetCatalogos@obtenerElemento');

    $router->get('/test','Servicio@testJSON');
    $router->get('/testq','Servicio@testSelect');
        
});
