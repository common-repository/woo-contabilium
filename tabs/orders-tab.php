<?php
/*
$type = get_user_meta( 1, 'cb_document_type', 1 );
$number = get_user_meta( 1, 'cb_document_number', 1 );
echo $type . '' . $number;
$CbCustomer = Customer::getCustomerByDocument($number, $type, CB_TOKEN);
if ( $CbCustomer != 'El cliente no existe') {
	CbApi::getOutput($CbCustomer);	
} else {
	echo 'El cliente no existe.';
}
*/
//CbApi::getOutput(Order::getStructuredItems( 150 ));
//$start_date = date("Y-m-d");
//$end_date = date( "Y-m-d", strtotime ( '+10 day' , strtotime ( $start_date ) ) ) ;
//echo "Fecha Inicial: $start_date Fecha Final: $end_date <br/>";
//echo "Sistema Operativo.<br/>";
//CbApi::getOutput(php_uname('s'));

//$tax = WC_Tax::get_base_tax_rates();
use Contabilium\CbApi;
use Contabilium\Tools;

$order           = new WC_Order( Tools::getValue( 'id' ) );
$payment_methods = WC_Payment_Gateways::instance()->payment_gateways();
CbApi::getOutput( Tools::getTempDocument() );
