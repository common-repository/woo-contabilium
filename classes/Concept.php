<?php

/**
 * Conceptos de Ventas
 */

namespace Contabilium;

use mysql_xdevapi\Exception;

class Concept {

	public static function get( $id_voucher, $token ) {

		return CbApi::getRequest( [

			'url' => 'https://rest.contabilium.com/api/conceptos/' . (int) $id_voucher,

			'token' => $token

		] );

	}

	public static function getByCodigo( $id_voucher, $token ) {
		return CbApi::getRequest( [

			'url' => 'https://rest.contabilium.com/api/conceptos/getByCodigo?codigo=' . strip_tags( $id_voucher ),

			'token' => $token

		] );

	}

	public static function add( $data, $token ) {

		return CbApi::postRequest( [

			'data' => $data,

			'url' => 'https://rest.contabilium.com/api/conceptos/',

			'token' => $token

		] );

	}

	public static function update( $data, $token ) {

		$authorization = "Authorization: Bearer $token";

		$ch = curl_init();

		$url = 'https://rest.contabilium.com/api/conceptos/' . (int) $data["id"];

		curl_setopt( $ch, CURLOPT_URL, $url );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json', $authorization ) );

		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );

		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$result = curl_exec( $ch );

		return json_decode( $result );

	}

	public static function delete( $id_voucher, $token ) {

		return CbApi::deleteRequest( [

			'url' => 'https://rest.contabilium.com/api/conceptos/' . (int) $id_voucher,

			'token' => $token

		] );

	}

	public static function updateWcProductById( $data ) {

		if ( self::updateWcProductDetailsById( $data ) ) {
			return true;
		} else {
			return false;
		}


	}

	public static function updateWcProductDetailsById( $data ) {
		//if (
		update_post_meta( $data["wc_id"], '_price', $data["price"] );// &&
		update_post_meta( $data["wc_id"], '_stock', $data["stock"] );

		//)
		return true;
		//return false;

	}

	public static function syncAllForUpdate( $pages, $per_page ) {
		$success     = 0;
		$Products    = Concept::getAllForUpdate( $pages, $per_page );
		$invalid_skus = [];
		$error_msg   = null;

		foreach ( $Products as $i => $Concepto ) {
			$Concepto = (object) $Concepto;
			$wcprd    = Concept::getWcProductBySKU( $Concepto->Codigo );

			try {
				if ( $wcprd != null && $wcprd != "Invalid product." ) {
					if ( Helper::update_product_price( $wcprd, $Concepto ) ) {
						if ( 'variable' === $wcprd->get_type() ) {
							$variations = $wcprd->get_available_variations();

							if ( ! empty( $variations ) ) {
								foreach ( $variations as $variation ) {
									if ( ! empty( $variation['sku'] ) ) {
										$child_product = wc_get_product( $variation['variation_id'] );
										if ( $child_product ) {
											$updated_child = Helper::update_product_price( $child_product, $Concepto );
										}
									}
								}
							}
						}

						$success ++;
					}
				}
			} catch ( \Exception $e ) {
				$invalid_skus[] = $Concepto->Codigo;
				$error_msg     = $e->getMessage();
			}
		}

		if ( $error_msg ) {
			$_SESSION['error'] = 'Problema al sincronizar los siguientes SKUs:<br>' .
				implode( ', ', $invalid_skus ) . '. Error: ' . $error_msg;

			return false;
		} else {
			return $success;
		}

	}

	public static function getAllForUpdate( $pages, $per_page ) {
		$p = [];
		for ( $i = 1; $i <= $pages; $i ++ ) {
			$Concept = Concept::search( '', date( "Y-m-01" ), date( "Y-m-d" ), $i, CB_TOKEN );
			for ( $j = 0; $j < $Concept->TotalPage; $j ++ ) {

				if ( $Concept->Items[ $j ]->Codigo != null && ( $Concept->Items[ $j ]->PrecioFinal != 0 || $Concept->Items[ $j ]->Stock != null ) ) {
					array_push( $p, [
						"Codigo" => $Concept->Items[ $j ]->Codigo,
						"PrecioFinal" => $Concept->Items[ $j ]->PrecioFinal,
						"Stock"  => $Concept->Items[ $j ]->Stock
					] );
				}
			}

		}

		return $p;
	}

	public static function search( $filter, $from = '', $to = '', $page = 1, $token ) {

		return CbApi::getRequest( [

			'url' => 'https://rest.contabilium.com/api/conceptos/search?filtro=' . $filter . '&fechaDesde=' . $from . '&fechaHasta=' . $to . '&page=' . $page,

			'token' => $token

		] );

	}

	public static function getWcProductBySKU( $sku ) {
		global $wpdb;

		try {
			$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );

			return $product_id ? wc_get_product( $product_id ) : null;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}

	}


}