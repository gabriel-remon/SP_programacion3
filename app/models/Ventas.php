<?php

include_once __DIR__ . "/../utils/AccesoDatos.php";
include_once __DIR__ . "/Usuarios.php";
include_once __DIR__ . "/Armas.php";

class Ventas
{
    public $id;
    public $id_usuario;
    public $id_arma;
    public $cantidad;
    public $fecha_venta;
    public $url_foto;

    function __construct($id_arma, $fecha_venta = null)
    {
        if (isset($id_arma) && !Armas::exist($id_arma))
            throw new Exception("El producto ingresado no se encuentra en la base de datos");

        $this->id_arma = $id_arma;
        $this->fecha_venta = $fecha_venta ? date($fecha_venta) : date("Y-m-d H:i:s");
    }

    /**
     * retorna el id guarado en la base de datos
     *
     * @param [type] $id_comanda
     * @return void
     */
    public function altaVenta($id_usuario, $url_foto)
    {
        $retorno = null;
        $tipo_usuario = Usuarios::obtenerTipo($id_usuario);

        //if ($tipo_usuario && $tipo_usuario == 'comprador') {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO " . $_ENV['BD_VENTA'] . " (id_usuario, id_arma,cantidad, fecha_venta,url_foto)  
                                                        VALUES (:id_usuario, :id_arma,:cantidad, :fecha_venta,:url_foto) ");
            $consulta->bindValue(':id_usuario', $id_usuario);
            $consulta->bindValue(':id_arma', $this->id_arma);
            $consulta->bindValue(':cantidad', $this->cantidad);
            $consulta->bindValue(':fecha_venta', $this->fecha_venta);
            $consulta->bindValue(':url_foto', $url_foto);
            $carga = $consulta->execute();
            
            if ($carga) {
                $retorno = $objAccesoDatos->obtenerUltimoId();
                $this->id = $retorno;
                $this->id_usuario = $id_usuario;
                $this->url_foto = $url_foto;
            }
        //}

        return  $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        //$consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_VENTA']);

        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        " . $_ENV['BD_VENTA'] . ".cantidad, 
        " . $_ENV['BD_VENTA'] . ".fecha_venta , 
        " . $_ENV['BD_VENTA'] . ".url_foto , 
        " . $_ENV['BD_PRODUCTOS'] . ".precio , 
        " . $_ENV['BD_PRODUCTOS'] . ".nombre , 
        " . $_ENV['BD_USUARIOS'] . ".email  
        FROM " . $_ENV['BD_VENTA'] . " 
        JOIN " . $_ENV['BD_PRODUCTOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_arma = " . $_ENV['BD_PRODUCTOS'] . ".id 
        JOIN " . $_ENV['BD_USUARIOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_usuario = " . $_ENV['BD_USUARIOS'] . ".id");
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        //var_dump($data);
        return $data;
    }

    public static function obtenerPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_VENTA'] . " WHERE id = :id");
        $consulta->bindValue(':id', $id,);
        $consulta->execute();

        $pedido = $consulta->fetchObject();
        $newPedido = new Ventas($pedido->id_arma, $pedido->fecha_venta);
        $newPedido->id = $pedido->id;
        $newPedido->cantidad = $pedido->cantidad;
        $newPedido->url_foto = $pedido->url_foto;

        //var_dump($newPedido);
        return $newPedido;
    }

    public static function modificarPedido($newPedido)
    {
        $retotno = false;
        $pedidoAnterior = Ventas::obtenerPedido($newPedido->id);
        if ($pedidoAnterior) {

            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE " . $_ENV['BD_VENTA'] . "
                                                         SET  id_usuario = :id_usuario , 
                                                         id_arma = :id_arma, 
                                                         cantidad = :cantidad, 
                                                         fecha_venta = :fecha_venta,
                                                         url_foto = :url_foto,
                                                         WHERE id = :id");

            $consulta->bindValue(':id_usuario',  $newPedido->id_usuario  ? $newPedido->id_usuario  : $pedidoAnterior->id_usuario);
            $consulta->bindValue(':id_arma',     $newPedido->id_arma     ? $newPedido->id_arma     : $pedidoAnterior->id_arma);;
            $consulta->bindValue(':cantidad',    $newPedido->cantidad    ? $newPedido->cantidad    : $pedidoAnterior->cantidad);
            $consulta->bindValue(':fecha_venta', $newPedido->fecha_venta ? $newPedido->fecha_venta : $pedidoAnterior->fecha_venta);
            $consulta->bindValue(':url_foto',    $newPedido->url_foto    ? $newPedido->url_foto    : $pedidoAnterior->url_foto);;
            $consulta->bindValue(':id', $newPedido->id);

            $retotno = $consulta->execute();
        }

        return $retotno;
    }

    public static function borrarPedido($id,)
    {
        $pedido = Ventas::obtenerPedido($id);
        if ($pedido) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("DELETE " . $_ENV['BD_VENTA'] . " 
                                                        WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        } else {
            throw new Exception("Solo los admin pueden borrar un pedido");
        }
    }

    public static function pedidosCliente($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $data = [];
        $consulta = $objAccesoDatos->prepararConsulta("SELECT " . $_ENV['BD_VENTA'] . ".cantidad, " . $_ENV['BD_VENTA'] . ".fecha_venta, " . $_ENV['BD_VENTA'] . ".url_foto, " . $_ENV['BD_ARMAS'] . ".precio, " . $_ENV['BD_ARMAS'] . ".nombre, " . $_ENV['BD_USUARIOS'] . ".email 
                                                    FROM " . $_ENV['BD_VENTA'] . " 
                                                    JOIN " . $_ENV['BD_ARMAS'] . " ON " . $_ENV['BD_VENTA'] . ".id_arma = " . $_ENV['BD_ARMAS'] . ".id 
                                                    JOIN " . $_ENV['BD_USUARIOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_usuario = " . $_ENV['BD_USUARIOS'] . ".id 
                                                    WHERE " . $_ENV['BD_VENTA'] . ".id_usuario = :id_usuario");
        $consulta->bindValue(':id_usuario', $id_usuario);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
    public static function pedidosNacionalidadFecha($nacionalidad, $fecha_inicio, $fecha_fin)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $data = [];
        /*
        $query ="SELECT 
        " . $_ENV['BD_VENTA'] . ".cantidad, 
        " . $_ENV['BD_VENTA'] . ".fecha_venta, 
        " . $_ENV['BD_VENTA'] . ".url_foto, 
        " . $_ENV['BD_PRODUCTOS'] . ".precio, 
        " . $_ENV['BD_PRODUCTOS'] . ".nombre, 
        " . $_ENV['BD_USUARIOS'] . ".email 
        FROM " . $_ENV['BD_VENTA'] . " 
        JOIN " . $_ENV['BD_PRODUCTOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_arma = " . $_ENV['BD_PRODUCTOS'] . ".id 
        JOIN " . $_ENV['BD_USUARIOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_usuario = " . $_ENV['BD_USUARIOS'] . ".id 
        WHERE " . $_ENV['BD_VENTA'] . ".id_usuario = :id_usuario";
        */
        $query = "SELECT 
        " . $_ENV['BD_VENTA'] . ".cantidad, 
        " . $_ENV['BD_VENTA'] . ".fecha_venta, 
        " . $_ENV['BD_VENTA'] . ".url_foto, 
        " . $_ENV['BD_PRODUCTOS'] . ".precio as precio_arma, 
        " . $_ENV['BD_PRODUCTOS'] . ".nombre as nombre_arma, 
        " . $_ENV['BD_USUARIOS'] . ".email 
        FROM " . $_ENV['BD_VENTA'] . " 
        JOIN " . $_ENV['BD_USUARIOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_usuario = " . $_ENV['BD_USUARIOS'] . ".id 
        JOIN " . $_ENV['BD_PRODUCTOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_arma = " . $_ENV['BD_PRODUCTOS'] . ".id 
        WHERE " . $_ENV['BD_VENTA'] . ".fecha_venta BETWEEN :fecha_inicio AND :fecha_fin 
        AND " . $_ENV['BD_PRODUCTOS'] . ".nacionalidad = :nacionalidad";

        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':nacionalidad', $nacionalidad);
        $consulta->bindValue(':fecha_inicio', $fecha_inicio);
        $consulta->bindValue(':fecha_fin', $fecha_fin);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
