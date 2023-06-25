
<?php

include_once __DIR__ . "/../utils/AccesoDatos.php";

class Armas
{
    public $id;
    public $precio;
    public $nombre;
    public $nacionalidad;
    public $url_foto;


    public function crearProducto()
    {

        $retorno = -1;

        if (Armas::obtenerProducto($this->id) == null); {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO " . $_ENV['BD_PRODUCTOS'] . " (precio,nombre, nacionalidad ,url_foto)
                                                             VALUES (:precio,:nombre,:nacionalidad, :url_foto)");

            $consulta->bindValue(':precio', $this->precio);
            $consulta->bindValue(':nombre', $this->nombre);
            $consulta->bindValue(':nacionalidad', strtolower($this->nacionalidad));
            $consulta->bindValue(':url_foto', $this->url_foto);
            $consulta->execute();
            $retorno = $objAccesoDatos->obtenerUltimoId();
        }


        return $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_PRODUCTOS']);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $retorno = [];
        foreach ($data as $element) {
            $usuario = new Armas();
            $usuario->id = $element['id'];
            $usuario->precio = $element['precio'];
            $usuario->nombre = $element['nombre'];
            $usuario->nacionalidad = $element['nacionalidad'];
            $usuario->url_foto = $element['url_foto'];
            array_push($retorno, $usuario);
        }

        return $retorno;
    }

    public static function obtenerProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_PRODUCTOS'] . " WHERE id = :id");
        $consulta->bindValue(':id', $id);
        //$consulta->execute();
        if ($consulta->execute()) {
            $retorno = $consulta->fetchObject('Armas');
        } else {
            $retorno = null;
        }

        return $retorno;
    }
    public static function obtenerPorNacionalidad($nacionalidad)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_PRODUCTOS'] . " 
                                                        WHERE nacionalidad = :nacionalidad");
        $consulta->bindValue(':nacionalidad', strtolower($nacionalidad));
        //$consulta->execute();
        if ($consulta->execute()) {
            $retorno = $consulta->fetchAll();
        } else {
            $retorno = null;
        }

        return $retorno;
    }

    public static function modificarProducto($newProduct)
    {
        $retorno = false;

        if (Armas::obtenerProducto($newProduct->id)) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE " . $_ENV['BD_PRODUCTOS'] . " 
                                                        SET precio = :precio, nombre = :nombre, nacionalidad = :nacionalidad, url_foto = :url_foto 
                                                        WHERE id = :id");

            $consulta->bindValue(':precio', $newProduct->precio);
            $consulta->bindValue(':nombre', $newProduct->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':nacionalidad',  strtolower($newProduct->nacionalidad));
            $consulta->bindValue(':url_foto', $newProduct->url_foto);
            $consulta->bindValue(':id',  $newProduct->id);
            $retorno = $consulta->execute();
        }

        return $retorno;
    }

    /**
     * -1 no existe el producto
     * -2 el producto ya esta dado de baja
     * -3 error en el server
     * mayor 0  - el producto fue dado de baja
     *
     * @param [type] $id
     * @return int
     */
    public static function borrarProducto($id)
    {
        $producto = Armas::obtenerProducto($id);
        $retorno = -1;
        if ($producto) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("DELETE " . $_ENV['BD_PRODUCTOS'] . "
                                                         WHERE id = :id");
            $consulta->bindValue(':id', $id);
            $consulta->execute() ? $retorno = $id : $retorno = -2;
        }

        return $retorno;
    }

    public static function exist($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT id from " . $_ENV['BD_PRODUCTOS'] . " WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
        $retorno = $consulta->fetchObject('Armas');
        if ($retorno !== false) {
            $retorno = true;
        }

        return $retorno;
    }
}
