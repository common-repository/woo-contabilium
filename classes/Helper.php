<?php

namespace Contabilium;

class Helper

{

    public static function getCountries($token)

    {

        return CbApi::getRequest([

			'url' => 'https://rest.contabilium.com/api/common/getPaises/',

			'token' =>  $token

		]);

    }



    public static function getStates($id_country = 10, $token)

    {

        return CbApi::getRequest([

			'url' => 'https://rest.contabilium.com/api/common/getProvincias/' . (int) $id_country,

			'token' =>  $token

		]);

    }



    public static function getCities($id_state = 2, $token)

    {

        return CbApi::getRequest([

			'url' => 'https://rest.contabilium.com/api/common/getCiudades/' . (int) $id_state,

			'token' =>  $token

		]);

    }

    public static function getPosList($token)
    {
        return CbApi::getRequest([

            'url' => 'https://rest.contabilium.com/api/puntosdeventa/search',

            'token' =>  $token

        ]);
    }

	/**
	 * Updates price for a product
	 *
	 * @param WC_Product $wc_product WooCommerce Product
	 * @param \stdClass  $concepto   Concepto de Contabilium
	 */
	public static function update_product_price( $wc_product, $concepto ) {
		if ( 'yes' === get_option( 'cb_sync_stock' ) ) {
			$wc_product->set_stock_quantity( $concepto->Stock );
		}

		$overwrite = 'yes' === get_option( 'cb_overwrite_price' );

		if ( 'yes' === get_option( 'cb_sync_price' ) ) {
			$sale_price = $wc_product->get_sale_price();

			if ( $sale_price && $concepto->PrecioFinal < $sale_price && $overwrite ) {
				$wc_product->set_sale_price( $concepto->PrecioFinal );
			}

			$wc_product->set_regular_price( $concepto->PrecioFinal );
		}

		return $wc_product->save();
	}
}