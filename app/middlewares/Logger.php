<?php

include_once __DIR__ . '/../utils/jwtController.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseMW;
use Firebase\JWT\JWT;

class Logger
{
    public static function validarJWTUsuario($request, $handler)
    {

        $cookies = $request->getCookieParams();
        $token = isset($cookies['jwt']) ? $cookies['jwt'] : null;

        $error = null;
        $statusError = 500;
        $response = new ResponseMW();

        if (!$token) {
            $error = 'no hay un jwt guardado';
            $statusError = 401;
        }else{
            try {
                $tokenVerificado =  ControlerJWT::VerificarToken($token);
                $request = $request->withAttribute('jwt', $tokenVerificado);
                $response = $handler->handle($request);

            } catch (\Exception $e) {
    
                $error = $e->getMessage();
                $statusError = 401;
            }
        }


        if (isset($error)) {
            $response->getBody()->write($error);
            $response->withStatus($statusError);
        }

        return $response;
    }
    /*
    public static function validarJWTUsuario ($request, $handler) {
        
        $cookies = $request->getCookieParams();
        $token = $cookies['jwt'] ?? null;
        $error = null;
        $statusError = 500;
        //var_dump($jwt);
        //var_dump('hola');
        //$authHeader = $request->getHeaderLine('Authorization');
    
        if (!$token) {
            $response = new ResponseMW();
            $response->getBody()->write('no hay un jwt guardado');
            return $response->withStatus(401);
        }
    
        //list($token) = sscanf($authHeader, 'Bearer %s');
        try{
            $tokenVerificado =  ControlerJWT::VerificarToken($token);
            $request = $request->withAttribute('jwt', $tokenVerificado);
            $response = $handler->handle($request);
        }catch (\Exception $e) {
            
            
            $response = new ResponseMW();
            $response->getBody()->write('Invalid token');
            return $response->withStatus(401);
        }
    
        if(isset($error)){
            $view = $req->getAttribute('view');
            $res->getBody()->write($view->render('error.twig',['data'=>$error]));
            $res->withStatus($statusError);
        }

        return $response;
    }*/
    public static function validarMesero($request, $handler)
    {
        $dataJwt = $request->getAttribute('jwt');

        if (!$dataJwt) {
            $response = new ResponseMW();
            $response->getBody()->write('no hay un jwt guardado');
            return $response->withStatus(401);
        }

        if ($dataJwt->sector !== 'mesero' && $dataJwt->sector !== 'admin') {
            $response = new ResponseMW();
            $response->getBody()->write('no cuenta con permisos para ingresar');
            return $response->withStatus(404);
        }

        return $handler->handle($request);
    }

    public static function validarAdmin($request, $handler)
    {
        $dataJwt = $request->getAttribute('jwt');

        if (!$dataJwt) {
            $response = new ResponseMW();
            $response->getBody()->write('no hay un jwt guardado');
            return $response->withStatus(401);
        }

        if ($dataJwt->sector !== 'admin') {
            $response = new ResponseMW();
            $response->getBody()->write('no cuenta con permisos para ingresar');
            return $response->withStatus(404);
        }

        return $handler->handle($request);
    }

    public static function validarRoles($rolesPermitidos)
    {
        return function ($request, $handler) use ($rolesPermitidos) {
            $dataJwt = $request->getAttribute('jwt');
            $error = null;
            $statusError = 500;
            $response = new ResponseMW();

            if (!$dataJwt) {
                $error = 'no hay un jwt guardado';
                $statusError = 401;
            }

            if (!in_array($dataJwt->sector, $rolesPermitidos)) {
                $error = 'no cuenta con permisos para ingresar';
                $statusError = 404;
            }

            if (isset($error)) {
                $response->getBody()->write($error);
                $response->withStatus($statusError);
            } else {
                $response = $handler->handle($request);
            }


            return $response;
        };
    }
}
