
<?php

include_once __DIR__ . '/../models/Usuarios.php';
include_once __DIR__ . '/../utils/jwtController.php';

class routerUsuarios
{




    public function Login($req, $res, $args)
    {
        
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;
        try {
            $usuario=Usuarios::validarUsuario($body['email'], $body['password']);
            if (isset($usuario)) {
                
                $token = [
                    'id' => $usuario->id,
                    'sector' => $usuario->tipo,
                    'email' => $usuario->email
                ];
                $jwt = ControlerJWT::CrearToken($token);
                $res = $res->withHeader('Set-Cookie', 'jwt=' . $jwt . '; path=/; HttpOnly; Secure; SameSite=Strict');
                $message = "logeado usuario con email: " . $usuario->email. ' - tipo de usuario: '.$usuario->tipo;
                $status = 200;
            } else {
                $message = 'email o password invalidos';
                $status = 500;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write($message);
        $res = $res->withStatus($status);
        return $res;
    }

    public function SingUp($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;
        try {
            $newUser = new Usuarios();
            $newUser->email = $body['email'];
            $newUser->password = $body['password'];
            $newUser->tipo = $body['tipo'];
            $newUser->nombre = strtolower($body['nombre']);
            $newid = $newUser->crearUsuario();
            if ($newid > -1) {
                $message = 'usuario creado con exito id: ' . $newid;
                $status = 200;
            } else {
                $message = 'no se creo el usuario';
                $status = 500;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }

        $res->getBody()->write($message);
        $res = $res->withStatus($status);
        return $res;
    }

    public function traerUsuariosVenta($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;

        try {
                $message = Usuarios::usuariosPorVenta($body['nombre_arma']);
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
