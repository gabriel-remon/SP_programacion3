<?php

use Firebase\JWT\JWT;

class ControlerJWT
{
    private static $claveSecreta = 'c63b4736368eb57bd707e47d5ad50b050b773012b0bacc88f494e67e9deef0af26b9198f436f279f445c101b2f55a07b662f9cd1b415c5205afd86c7b1b4d8ec';
    //private static $claveSecreta =$_ENV['SECRT_PASSWORD'];
    private static $tipoEncriptacion = ['HS256'];

    public static function CrearToken($datos)
    {
        $ahora = time();
        $payload = array(
            'iat' => $ahora,
            //'exp' => $ahora + (60000),
            'aud' => self::Aud(),
            'data' => $datos,
            'app' => "Segundo parcial de programacion"
        );
        return JWT::encode($payload, self::$claveSecreta);
    }

    public static function VerificarToken($token)
    {
        try {
            $decodificado = ControlerJWT::ObtenerPayLoad($token);
            if ($decodificado->aud !== self::Aud()) {
                throw new Exception("No es el usuario valido");
            }
        } catch (Exception $e) {
            throw $e;
        }
        
        
        return $decodificado->data;
    }


    public static function ObtenerPayLoad($token)
    {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        );
    }

    public static function ObtenerData($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->data;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}