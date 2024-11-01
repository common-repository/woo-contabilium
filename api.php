<?php
/**
 * Archivo para la sincronización de un producto.
 *
 */

// Registrar el endpoint

use Contabilium\Concept;
use Contabilium\Helper;

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp/v2', '/contabilium/(?P<sku>\S+)', array(
		'methods'  => 'POST',
		'callback' => 'contabilium_sync_product',
	) );
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp/v2', '/contabilium/(?P<sku>\S+)', array(
		'methods'  => 'GET',
		'callback' => 'contabilium_sync_product',
	) );
} );

function contabilium_handle_error( $error, $code = 500 ) {
	$data = array(
		'msg' => $error,
	);

	wp_send_json_error( $data, $code );
	wp_die();
}

function contabilium_sync_product( WP_REST_Request $request, $print_response = true ) {
	$sku = $request->get_param( 'sku' );

	if ( empty( $sku ) ) {
		contabilium_handle_error( 'No se recibió el SKU del producto' );
	}

	try {
		$concepto   = Concept::getByCodigo( $sku, CB_TOKEN );
		$wc_product = Concept::getWcProductBySKU( $sku );
	} catch ( \Exception $e ) {
		contabilium_handle_error( 'No existe un producto con ese SKU', 404 );

		return false;
	}

	if ( is_string( $concepto ) ) {
		contabilium_handle_error( 'No se encontró el producto en Contabilium: ' . $concepto, 404 );
	}

	if ( $concepto && property_exists( $concepto, 'Message' ) ) {
		if ( false !== strpos( $concepto->Message, 'Error' ) ) {
			contabilium_handle_error( $concepto->Message, 404 );
		}
	}

	if ( ! $wc_product ) {
		contabilium_handle_error( 'No existe un producto con ese SKU', 404 );
	}

	Helper::update_product_price( $wc_product, $concepto );

	if ( 'variable' === $wc_product->get_type() ) {
		$variations = $wc_product->get_available_variations();

		if ( ! empty( $variations ) ) {
			foreach ( $variations as $variation ) {
				$new_request = new WP_REST_Request();
				if ( ! empty( $variation['sku'] ) ) {
					$new_request->set_param( 'sku', $variation['sku'] );
					contabilium_sync_product( $new_request, false );
				}
			}
		}
	}

	if ( $print_response ) {
		$data = [
			'msg' => 'Se actualizó correctamente el producto.',
		];
		wp_send_json_success( $data );
	}
}