<?php
namespace Contabilium;

class Tools 

{

    public static function _selected( $a, $b )

    {

        return ($a == $b) ? 'selected="selected"' : null;

    }



    public static function getValue($field)

    {

        return empty($_REQUEST["$field"]) ? null : $_REQUEST["$field"];

    }



    public static function p($field)

    {

        return $_POST["$field"];

    }

    

    public static function g($field)

    {

        return $_GET["$field"];

    }



    public static function getOutput($string)

    {

    	echo '<pre>';

    	var_dump($string);

    	echo '</pre>';

    }

    public static function isValidDocument( $number, $type = 'DNI' ){

        if ( $type == 'CUIT') {

            $number = preg_replace( '/[^\d]/', '', (string) $number );
            if( strlen( $number ) != 11 ){
                return false;
            }
            $acumulado = 0;
            $digitos = str_split( $number );
            $digito = array_pop( $digitos );

            for( $i = 0; $i < count( $digitos ); $i++ ){
                $acumulado += $digitos[ 9 - $i ] * ( 2 + ( $i % 6 ) );
            }
            $verif = 11 - ( $acumulado % 11 );
            $verif = $verif == 11? 0 : $verif;

            return $digito == $verif;

        }

        if ( $type == 'DNI' ) {
            return preg_match('/[0-9]{6,10}/', $number);
        }

    }


    public static function isSubmit($field)
    {
        return ( isset($field) && Tools::getValue("$field") !== null) ? true : false;
    }

    public static function getOS()
    {
        return strtoupper(php_uname('s'));
    }

    public static function getRatePercent($base, $mount)
    {
        return  ( $base > 0 ) ? ($mount * 100) / $base : 0;
    }

    public static function getTempDocument()
    {

        return mt_rand(100000000, 999999999);
    }

}