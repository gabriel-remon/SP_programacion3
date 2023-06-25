


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
                $foto->moveTo(__DIR__ . '/../' . $directorioDestino);

                $newVenta->url_foto = $directorioDestino;
                $newid = $newVenta->altaVenta($dataJwt->id,$directorioDestino);
                if ($newid > -1) {
                    $message = 'arma creada con exito id: ' . $newid;
                    $status = 200;
                } else {
                    $message = 'no se creo el usuario';
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
    /*

    public function TraerUno($req, $res, $args)
    {
        $body = $req->getParsedBody();

        $usuario = Usuario::validarUsuario($body['email'], $body['password']);
        //var_dump($usuario);
        if (isset($usuario) && $usuario->estado) {
            $token = [
                'id' => $usuario->id,
                'sector' => $usuario->sector,
                'email' => $usuario->email
            ];
            $jwt = ControlerJWT::CrearToken($token);
            $res = $res->withHeader('Set-Cookie', 'jwt=' . $jwt . '; path=/; HttpOnly; Secure; SameSite=Strict');
            $res->getBody()->write("Bienvenido " . $usuario->nombre);
            $res = $res->withStatus(200);
        } else {
            $res = $res->withStatus(400);
            $res->getBody()->write('usuario o password incorrectos');
        }
        return $res;
    }


    public function TraerTodos($req, $res, $args)
    {
        $usuarios = Usuario::obtenerTodos();

        $res->getBody()->write(json_encode($usuarios));
        return $res;
    }

    public function ModificarUno($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $new = new Usuario();

        $new->email = $body['email'];
        $new->password = $body['password'];
        $new->nombre = $body['nombre'];
        $new->fecha_nacimiento = new DateTime($body['fecha_nacimiento']);
        $new->sector = $body['sector'];
        $new->estado = $body['estado'];

        $idProduct = Usuario::modificarUsuario($new);
        $res->getBody()->write('usuario modificado con exito id: ' . $idProduct);
        return $res;
    }

    public function BorrarUno($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $eliminado = Usuario::borrarUsuario($body['email']);
        $res->getBody()->write($eliminado ? 'usuario eliminado' : 'no se pudo eliminar');
        return $res;
    }
    public function logout($req, $res, $args)
    {
        try {
            $res = $res->withHeader('Set-Cookie', 'jwt=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; HttpOnly; Secure; SameSite=Strict');
            $res->getBody()->write('Usuario deslogado');
            $res = $res->withStatus(200);
        } catch (Exception $e) {
            $res->getBody()->write('Error: ' . $e->getMessage());
            $res = $res->withStatus(500);
        }
        return $res;
    }
    */
}
