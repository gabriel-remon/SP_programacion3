<?php

use Slim\Psr7\Response as ResponseMW;

class validarFormato
{

    public static function tipo($req, $handler)
    {
        $res = new ResponseMW();
        $body = $req->getParsedBody();

        if (isset($body['tipo'])) {
            $body['tipo'] = strtolower($body['tipo']);
            if (in_array($body['tipo'], explode(',', $_ENV['TIPOS']))) {
                $newReq = $req->withParsedBody($body);
                $res = $handler->handle($newReq);
            } else {
                $res->getBody()->write('el tipo ingresado no es valido');
                $res = $res->withStatus(404);
            }
        } else {
            $res->getBody()->write("no se encontro el parametro 'tipo' sector en el body");
            $res = $res->withStatus(404);
        }

        return $res;
    }



    public static function arma($req, $handler)
    {

        $res = new ResponseMW();
        $body = $req->getParsedBody();
        $error = null;
        $status = 500;

        if (
            isset($body['precio']) &&
            isset($body['nombre']) &&
            isset($body['nacionalidad'])
        ) {
                $res = $handler->handle($req);
        } else {
            $error = 'no se encontraron los parametros precio,nombre o nacionalidad';
            $status = 404;
        }

        if (isset($error)) {
            $res->getBody()->write($error);
            $res = $res->withStatus($status);
        }

        return $res;
    }

    public static function usuario($req, $handler)
    {

        $res = new ResponseMW();
        $body = $req->getParsedBody();
        $error = null;
        $status = 500;

        if (
            isset($body['email']) &&
            isset($body['password']) &&
            isset($body['nombre']) &&
            isset($body['tipo'])
        ) {
            if ($body['tipo'] == 'comprador' || $body['tipo'] == 'vendedor') {
                $res = $handler->handle($req);
            } else {
                $error = 'el tipo solo puede ser comprador o vendedor';
                $status = 404;
            }
        } else {
            $error = 'no se encontraron los parametros  email,password ,nombre o tipo';
            $status = 404;
        }
        if (isset($error)) {
            $res->getBody()->write($error);
            $res = $res->withStatus($status);
        }

        return $res;
    }
    public static function nacionalidadFecha($req, $handler)
    {

        $res = new ResponseMW();
        $body = $req->getParsedBody();
        $error = null;
        $status = 500;
        if (
            isset($body['nacionalidad']) &&
            isset($body['fecha_inicio']) &&
            isset($body['fecha_fin']) 
        ) {
            if (new DateTime($body['fecha_inicio']) <= new DateTime($body['fecha_fin'])) {
                $res = $handler->handle($req);
            } 
            else {
                $error = 'la fecha de inicio no puede ser antes que la fecha final';
                $status = 404;
            }
            
        } else {
            $error = 'no se encontraron los parametros fecha_inicio,fecha_fin o nacionalidad' ;
            $status = 404;
        }
        if (isset($error)) {
            $res->getBody()->write($error);
            $res = $res->withStatus($status);
        }

        return $res;
    }


    public static function venta($req, $handler)
    {

        $res = new ResponseMW();
        $body = $req->getParsedBody();
        $foto = $req->getUploadedFiles();
        $error = null;
        $status = 500;

        if (
            isset($body['id_arma'])&&
            isset($req->getUploadedFiles()['url_foto'])&&
            isset($body['cantidad'])
            ) {
                
                if (intval($body['cantidad']) > 0){
                $foto = $req->getUploadedFiles()['url_foto'];
                $nombreArchivo = $foto->getClientFilename();
                $infoArchivo = pathinfo($nombreArchivo);
                $extension = $infoArchivo['extension'];
                if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png'){
                    $res = $handler->handle($req);
                }else{
                    $error = 'la foto solo puede ser jpg , jpeg ,png)';
                    $status = 404;
                }
            }else {
                $error = 'la cantidad tiene que ser mayor a 0';
                $status = 404;
            }
        } else {
            $error = 'no se encontraron los parametros';
            $status = 404;
        }

        if(isset($error)){
            $res->getBody()->write($error);
            $res= $res->withStatus($status);
        }

        return $res;
    }
    /*
    public static function altaPedido($req, $handler)
    {
        $res = new ResponseMW();
        $body = $req->getParsedBody();
        
        if(isset($body['numero_mesa'])){
            $comanda = Mesa::obtenerMesa($body['numero_mesa'],true);
            if($comanda){
                
                if(isset($body['id_producto']) && Producto::exist($body['id_producto'])){
                    $res = $handler->handle($req);
                }else{
                     $res= $res->withStatus(404);
                    $res->getBody()->write('el producto no existe');
                    return $res;
                }
            }else{
                 $res= $res->withStatus(404);
                $res->getBody()->write('La mesa ingresada no se encuentra con una comanda activa');
            }
        }else{
             $res= $res->withStatus(404);
            $res->getBody()->write('no se encontro el numero de mesa como parametro "numero_mesa"');
        }

        return $res;
    }

    public static function prepararPedido($req, $handler)
    {
        $res = new ResponseMW();
        $body = $req->getParsedBody();

        if(!isset($body['id_pedido'])){
            $error = 'No se encontro el parametro "id_pedido"';
            $statusError = 404;
            $res->getBody()->write($error);
             $res= $res->withStatus($statusError);
            return $res;
        }

        $pedido = Pedido::obtenerPedido($body['id_pedido']);
            
        if(!isset($pedido)){
            $error = 'El pedido no esta guardado en la lista de pedidos';
            $statusError = 500;
            $res->getBody()->write($error);
             $res= $res->withStatus($statusError);
            return $res;
        }

        if($pedido->estado == 'pendiente' && !isset($body['tiempo_estimado'])){
            $error = 'No se encontro el parametro "tiempo_estimado"';
            $statusError = 404;
            $res->getBody()->write($error);
             $res= $res->withStatus($statusError);
            return $res;
        }
        
        
            
            $res = $handler->handle($req);
        
        return $res;
    }
    */
}
