<?php

include_once __DIR__ . "/../utils/AccesoDatos.php";

class Usuarios
{
    public $id;
    public $email;
    public $nombre;
    public $password;
    public $tipo;


    public function crearUsuario()
    {

        $retorno = null;
        $usuario = Usuarios::obtenerUsuario($this->email);

        if (!isset($usuario)) {
            
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO " . $_ENV['BD_USUARIOS'] . " (email,nombre, password,tipo)
                                                         VALUES (:email,:nombre ,:password, :tipo)");
            $claveHash = password_hash($this->password, PASSWORD_DEFAULT);
            $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':password', $claveHash);
            $consulta->bindValue(':tipo', $this->tipo);

            $consulta->execute();
            $retorno = $objAccesoDatos->obtenerUltimoId();
        }


        return $retorno;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, email,tipo 
                                                        FROM " . $_ENV['BD_USUARIOS']);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $retorno = [];
        foreach ($data as $element) {
            $usuario = new Usuarios();
            $usuario->id = $element['id'];
            $usuario->email = $element['email'];
            $usuario->tipo = $element['tipo'];
            $usuario->nombre = $element['nombre'];
            array_push($retorno, $usuario);
        }

        return $retorno;
    }

    public static function obtenerUsuario($email)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM " . $_ENV['BD_USUARIOS'] . " WHERE email = :email");
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
        //$consulta->execute();
        if ($consulta->execute()) {
            $retorno = $consulta->fetchObject('Usuarios');
            if($retorno === false){
                $retorno = null;
            }
        } else {
            $retorno = null;
        }

        return $retorno;
    }
    public static function validarUsuario($email, $password)
    {

        $retorno = Usuarios::obtenerUsuario($email);
        //$consulta->execute();
        if (isset($retorno)) {

            if (!password_verify($password, $retorno->password)) {
                $retorno = null;
            }
        }

        return $retorno;
    }

    public static function modificarUsuario($newUser)
    {
        $retorno = Usuarios::obtenerUsuario($newUser->email);

        if (isset($retorno)) {

            $objAccesoDato = AccesoDatos::obtenerInstancia();

            $consulta = $objAccesoDato->prepararConsulta("UPDATE " . $_ENV['BD_USUARIOS'] . " 
                                                        SET email = :email, password = :password, tipo = :tipo
                                                        WHERE id = :id");
            $claveHash = password_hash($newUser->password, PASSWORD_DEFAULT);
            $consulta->bindValue(':email', $newUser->email, PDO::PARAM_STR);
            $consulta->bindValue(':password', $claveHash);
            $consulta->bindValue(':tipo', $newUser->tipo);
            $consulta->bindValue(':nombre', $newUser->nombre);
            $consulta->bindValue(':id',  $retorno->id);
            $retorno = $consulta->execute();
        }

        return $retorno;
    }

    public static function borrarUsuario($email)
    {
        $retorno = false;
        $usuario = Usuarios::obtenerUsuario($email);
        if ($usuario && $usuario->estado) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE " . $_ENV['BD_USUARIOS'] . " SET estado = false WHERE email = :email");
            //$fecha = new DateTime(date("d-m-Y"));
            $consulta->bindValue(':email', $email, PDO::PARAM_INT);
            //$consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
            $retorno =  $consulta->execute();
        }

        return $retorno;
    }

    public static function altaUsuario($email)
    {

        $usuario = Usuarios::obtenerUsuario($email);
        if ($usuario && $usuario->estado == false) {
            $objAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDato->prepararConsulta("UPDATE " . $_ENV['BD_USUARIOS'] . " SET estado = true WHERE email = :email");
            //$fecha = new DateTime(date("d-m-Y"));
            $consulta->bindValue(':email', $email, PDO::PARAM_INT);
            //$consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
            $consulta->execute();
        }
    }

    public static function exist($id, $tipo = null)
    {

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        if (isset($tipo)) {
            $consulta = $objAccesoDato->prepararConsulta("SELECT id " . $_ENV['BD_USUARIOS'] . " 
                                                                WHERE id = :id and tipo = :tipo");
            $consulta->bindValue(':tipo', $tipo);
        } else {
            $consulta = $objAccesoDato->prepararConsulta("SELECT id " . $_ENV['BD_USUARIOS'] . " 
                                                           WHERE id = :id");
        }
        $consulta->bindValue(':id', $id);
        $consulta->execute();
        $retorno = $consulta->fetchObject('Usuarios');
        if ($retorno !== false) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function obtenerTipo($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT tipo from " . $_ENV['BD_USUARIOS'] . " 
                                                    WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
        $retorno = $consulta->fetchObject();
        if ($retorno !== false) {
            $retorno = $retorno->tipo;
        } 

        return $retorno;
    }
    public static function usuariosPorVenta($nombre_arma)
    {
        
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $data = [];
            $query = "SELECT 
            " . $_ENV['BD_USUARIOS'] . ".*
            FROM " . $_ENV['BD_USUARIOS'] . " 
            JOIN " . $_ENV['BD_VENTA'] . " ON " . $_ENV['BD_USUARIOS'] . ".id = " . $_ENV['BD_VENTA'] . ".id_usuario 
            JOIN " . $_ENV['BD_PRODUCTOS'] . " ON " . $_ENV['BD_VENTA'] . ".id_arma = " . $_ENV['BD_PRODUCTOS'] . ".id 
            WHERE " . $_ENV['BD_PRODUCTOS'] . ".nombre = :nombre ";
            
    
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':nombre', $nombre_arma);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
            return $data;


      
    }

    
}
