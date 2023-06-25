
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
