


<?php

include_once __DIR__ . '/../models/Ventas.php';
include_once __DIR__ . '/../utils/jwtController.php';

class routerVentas
{

    public function Nueva($req, $res, $args)
    {
        $dataJwt = $req->getAttribute('jwt');
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;
        try {
            $newVenta = new Ventas($body['id_arma'], isset($body['fecha_venta'])?$body['fecha_venta']:null);
            $newVenta->cantidad = $body['cantidad'];

            $foto = $req->getUploadedFiles()['url_foto'];
            $nombreArchivo = $foto->getClientFilename();
            $infoArchivo = pathinfo($nombreArchivo);
            $extension = $infoArchivo['extension'];
            //$foto = isset($foto['url_foto'])?$foto['url_foto']:null;
            
            $arma = Armas::obtenerProducto($body['id_arma']);
            $usuario = Usuarios::obtenerUsuario($dataJwt->email);

            if ( $foto && $foto->getError() === UPLOAD_ERR_OK) {
                $directorioDestino = 'FotosArma2023/' .  $arma->nombre .'_'.$usuario->nombre. '_' . str_replace([':', ' '], ['.', '_'], $newVenta->fecha_venta). '.'.$extension;
                //$foto->moveTo(__DIR__ . '/../' . $directorioDestino);

                $newVenta->url_foto = $directorioDestino;
                
                $newid = $newVenta->altaVenta($dataJwt->id,$directorioDestino);
                if ($newid > -1) {
                    $foto->moveTo(__DIR__ . '/../' . $directorioDestino);
                    $message = 'arma creada con exito id: ' . $newid;
                    $status = 200;
                } else {
                    $message = 'no se creo la venta';
                    $status = 500;
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }

        $res->getBody()->write($message);
        $res = $res->withStatus($status);
        return $res;
    }


    public function TraerTodas($req, $res, $args)
    {

        $armas = Ventas::obtenerTodos();
    
        //$res->getBody()->write(json_encode($productos));
        $res->getBody()->write(json_encode($armas));

        return $res;
    }
    
    public function TraerPorId($req, $res, $args)
    {
        $message = null;
        $status = 500;

        try {
                $message = Ventas::obtenerPedido($args['id']);
                $status = 200;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }
    public function TraerNacionalidadFecha($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;

        try {
                $message = Ventas::pedidosNacionalidadFecha($body['nacionalidad'],$body['fecha_inicio'],$body['fecha_fin']);
                $status = 200;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }
   
}
