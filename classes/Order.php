<?php
namespace Contabilium;

use WC_Order;

class Order {


	public static function getItems( $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ( $items as $i => $item ) {
			$data[] = [
				'Cantidad'     => (int) $item['quantity'],
				'Concepto'     => (string) $item['name'],
				'Precio'       => (float) $item['subtotal'],
				'Iva'          => Tools::getRatePercent( (float) $item['subtotal'], (float) $item['subtotal_tax'] ),
				'Bonificacion' => 0,
			];
		}

		return $data;
	}

	public static function getStructuredItems( $order_id ) {
		$order = new WC_Order( $order_id );

		return CbApi::getOutput( $order );
	}
}