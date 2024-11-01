<?php

namespace Contabilium;

/**
* Comprobantes de Ventas
*/

class SaleVoucher

{

    public static function search($filter, $from = '', $to = '', $page = 1, $token)

    {

		return CbApi::getRequest([

			'url' => 'https://rest.contabilium.com/api/comprobantes/search?filtro='.$filter.'&fechaDesde='.$from.'&fechaHasta='.$to.'&page=' . $page,

			'token' => $token

		]);

    }

    

    

    public static function get($id_voucher, $token)

    {

		return CbApi::getRequest([

			'url' => 'https://rest.contabilium.com/api/comprobantes/' . (int) $id_voucher,

			'token' =>  $token

		]);

    }

    

    public static function add($data, $token)

    {

		return CbApi::postRequest([
			'data' => $data,
			'url' => 'https://rest.contabilium.com/api/comprobantes/crear',
			'token' => $token
		]);

    }



    public static function update( $data, $token)

    {

	    $authorization = "Authorization: Bearer $token";

	    $ch = curl_init();

	    $url = 'https://rest.contabilium.com/api/comprobantes/' . (int) $data["id"];   

	    curl_setopt($ch, CURLOPT_URL, $url);

	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization ));

	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);

	    return json_decode($result);

    }

    

	public static function delete( $id_voucher, $token)

    {

		return CbApi::deleteRequest([

			'url' => 'https://rest.contabilium.com/api/comprobantes/' . (int) $id_voucher,

			'token' => $token

		]);

    }

}