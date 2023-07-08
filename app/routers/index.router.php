<?php

include_once __DIR__.'/armas.router.php';
include_once __DIR__.'/usuarios.router.php';
include_once __DIR__.'/ventas.router.php';

include_once __DIR__.'/../models/Armas.php';
include_once __DIR__.'/../models/Usuarios.php';
include_once __DIR__.'/../models/Ventas.php';

include_once __DIR__.'/../middlewares/validarFormato.php';
include_once __DIR__.'/../middlewares/Logger.php';

class indexRouter{
    function __invoke($app) {

    $app->group('[/]', function ($group){
        $group->get('',\routerArmas::class . ':TraerTodas');
    });

    $app->group('/venta', function ($group){
        $group->post('[/]',\routerVentas::class . ':nueva')
        ->add(\validarFormato::class . ':venta')
        ->add(\Logger::class.':validarJWTUsuario');

        $group->get('[/]',\routerVentas::class . ':TraerTodas')
        ->add(\Logger::validarRoles(['admin']))
        ->add(\Logger::class.':validarJWTUsuario');

        $group->get('/usuarios',\routerUsuarios::class . ':traerUsuariosVenta')
        ->add(\Logger::validarRoles(['admin']))
        ->add(\Logger::class.':validarJWTUsuario');

        $group->get('/nacionalidad',\routerVentas::class . ':TraerNacionalidadFecha')
        ->add(\validarFormato::class . ':nacionalidadFecha')
        ->add(\Logger::validarRoles(['admin']))
        ->add(\Logger::class.':validarJWTUsuario');
    });

    $app->group('/usuarios', function ($group){
       $group->post('/login',\routerUsuarios::class . ':login');

       $group->post('/singup',\routerUsuarios::class . ':SingUp')
       ->add(\validarFormato::class . ':usuario');
    });
    $app->group('/ventas', function ($group){
       $group->post('/login',\routerUsuarios::class . ':login');

       $group->post('/singup',\routerUsuarios::class . ':SingUp')
       ->add(\validarFormato::class . ':usuario');
    });
    
    $app->group('/armas', function ($group){
        $group->get('/descargar',\routerArmas::class . ':descargarCsv');
       $group->post('[/]',\routerArmas::class . ':Nueva')
       ->add(\validarFormato::class . ':arma')
       ->add(\Logger::validarRoles(['admin']))
       ->add(\Logger::class.':validarJWTUsuario');
       
       $group->post('/{id}',\routerArmas::class . ':ModificarUno')
       ->add(\Logger::validarRoles(['admin']))
       ->add(\Logger::class.':validarJWTUsuario');
       
       $group->get('[/]',\routerArmas::class . ':TraerTodas');
       $group->get('/nacionalidad/{nacionalidad}',\routerArmas::class . ':TraerPorNacionalidadParams');
       
       $group->get('/{id}',\routerArmas::class . ':TraerPorId')
       ->add(\Logger::class.':validarJWTUsuario');
       
       $group->delete('/borrar',\routerArmas::class . ':borrarUna')
       ->add(\validarFormato::class . ':registroEliminar')
       ->add(\Logger::validarRoles(['admin']))
       ->add(\Logger::class.':validarJWTUsuario');
       
      

    });
    
}
}