

<?php

include_once __DIR__ . '/../models/Armas.php';
include_once __DIR__ . '/../utils/jwtController.php';

class routerArmas
{

    public function Nueva($req, $res, $args)
    {

        $body = $req->getParsedBody();
        $message = null;
        $status = 500;
        try {
            $newUser = new Armas();
            $newUser->precio = $body['precio'];
            $newUser->nombre = strtolower($body['nombre']);
            $newUser->nacionalidad = strtolower($body['nacionalidad']);

            $foto = $req->getUploadedFiles()['url_foto'];

            if ($foto->getError() === UPLOAD_ERR_OK) {
                $directorioDestino = 'image/' .  $newUser->nombre . '_' . $newUser->nacionalidad . '.jpg';
                $foto->moveTo(__DIR__ . '/../' . $directorioDestino);

                $newUser->url_foto = $directorioDestino;
                $newid = $newUser->crearProducto();
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

        $armas = Armas::obtenerTodos();

        //$res->getBody()->write(json_encode($productos));
        $res->getBody()->write(json_encode($armas));

        return $res;
    }
    public function TraerPorNacionalidadBody($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;

        try {
            if (isset($body['nacionalidad'])) {
                $message = Armas::obtenerPorNacionalidad(strtolower($body['nacionalidad']));
                $status = 200;
            } else {
                $message = 'no existe el parametro nacionalidad en el body';
                $status = 200;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }

    public function TraerPorNacionalidadParams($req, $res, $args)
    {
        $message = null;
        $status = 500;

        try {
            $message = Armas::obtenerPorNacionalidad(strtolower(($args['nacionalidad'])));
            $status = 200;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }
    public function TraerPorId($req, $res, $args)
    {
        $message = null;
        $status = 500;

        try {
            $message = Armas::obtenerProducto($args['id']);
            $status = 200;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }
    public function borrarUna($req, $res, $args)
    {
        $message = null;
        $status = 500;
        $body = $req->getParsedBody();

        try {
            if (isset($body['id_arma'])) {

                $accion = Armas::borrarProducto($body['id_arma']);
                if ($accion > 0) {
                    $message = 'se borro el producto';
                    $status = 200;
                } else {

                    $message = 'no se pudo borrar el producto';
                    $status = 500;
                }
            } else {
                $message = "falta el parametro id_arma";
                $status = 400;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = 500;
        }
        $res->getBody()->write(json_encode($message));
        $res = $res->withStatus($status);

        return $res;
    }
    public function descargarCsv($req, $res, $args)
    {
        try {
            
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " .  $_ENV['BD_PRODUCTOS']);
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            $contenidoCSV = '';
            $encabezados = array_keys($resultados[0]);
            $contenidoCSV .= implode(',', $encabezados) . "\n";

            foreach ($resultados as $fila) {
                $contenidoCSV .= implode(',', $fila) . "\n";
            }

            $res = $res
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', 'attachment; filename="' .  $_ENV['BD_PRODUCTOS'] . '.csv"')
                ->withBody(new \Slim\Psr7\Stream(fopen('php://temp', 'r+')));

            $res->getBody()->write($contenidoCSV);
            $res = $res->withStatus(200);
        } catch (Exception $e) {
            $res->getBody()->write($e->getMessage());
            $res = $res->withStatus(500);
        }
        return $res;
    }


    public function ModificarUno($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $message = null;
        $status = 500;
        //var_dump($body);
        ///$newProduct = new Armas();
        try {
            $product = Armas::obtenerProducto($args['id']);
            $directorioDestino =  $product->url_foto;

            if (Armas::exist($args['id'])) {
                $newProduct = new Armas();
                $newProduct->id = $args['id'];
                $newProduct->precio = isset($body['precio']) ? $body['precio'] : null;
                $newProduct->nombre = isset($body['nombre']) ? strtolower($body['nombre']) : null;
                $newProduct->nacionalidad = isset($body['nacionalidad']) ? strtolower($body['nacionalidad']) : null;
                $newProduct->precio = isset($body['precio']) ? $body['precio'] : null;
                // var_dump($body);
                if (isset($req->getUploadedFiles()['url_foto'])) {
                    $foto = $req->getUploadedFiles()['url_foto'];
                    if ($foto->getError() === UPLOAD_ERR_OK) {
                        $foto->moveTo(__DIR__ . '/../' . $directorioDestino);
                    }
                }


                if (Armas::modificarProducto($newProduct)) {

                    $updateProduct = Armas::obtenerProducto($args['id']);
                    $updateProduct->url_foto = 'image/' .  $updateProduct->nombre . '_' . $updateProduct->nacionalidad . '.jpg';

                    rename(__DIR__ . '/../' . $directorioDestino, __DIR__ . '/../' . $updateProduct->url_foto);
                    Armas::modificarProducto($updateProduct);
                    $message = "arma modificada";
                    $status = 200;
                } else {
                    $message = 'no se pudo modificar el producto';
                    $status = 500;
                }
            } else {
                $message = 'no existe el id ';
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
}
