<?php

namespace Contabilium;

class Customer {
	public static function get( $id_customer, $token ) {
		return CbApi::getRequest( [
			'url'   => 'https://rest.contabilium.com/api/clientes/' . (int) $id_customer,
			'token' => $token,
		] );
	}

	public static function add( $data, $token ) {
		return CbApi::postRequest( [
			'data'  => $data,
			'url'   => 'https://rest.contabilium.com/api/clientes/',
			'token' => $token,
		] );
	}

	public static function update( $data, $token ) {
		return CbApi::putRequest( [
			'data'  => $data,
			'url'   => 'https://rest.contabilium.com/api/clientes/' . (int) $data['id'],
			'token' => $token,
		] );
	}

	public static function delete( $id_customer, $token ) {
		return CbApi::deleteRequest( [
			'url'   => 'https://rest.contabilium.com/api/clientes/' . (int) $id_customer,
			'token' => $token,
		] );
	}

	public static function isRegistered( $filter, $page, $token ) {
		$customer = self::search( $filter, $page, $token );
		if ( $customer->TotalItems < 1 ) {
			return false;
		}

		return true;
	}

	public static function search( $filter, $page = 1, $token ) {
		return CbApi::getRequest( [
			'url'   => 'https://rest.contabilium.com/api/clientes/search?filtro=' . $filter . '&page=' . $page,
			'token' => $token,
		] );
	}

	// Get Customer by Document Type

	public static function getCustomerByDocument( $number, $type, $token ) {
		return CbApi::getRequest( [
			'url'   => 'https://rest.contabilium.com/api/clientes/GetClientByDoc?tipoDoc=' . (string) $type . '&&nroDoc=' . (string) $number,
			'token' => $token,
		] );
	}
}