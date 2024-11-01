<?php
use Contabilium\CbApi;
use Contabilium\Concept;

@ini_set( 'display_errors', 'on' );
@error_reporting( E_ALL | E_STRICT );
require( '../../../wp-load.php' );
$CbApiKey    = get_option( 'cb_api_client_id' );
$CbApiSecret = get_option( 'cb_api_client_secret' );
$CbApiPosId  = get_option( 'cb_api_pos_id' );

$api      = new CbApi();
$token    = $api->getAuth( $CbApiKey, $CbApiSecret );
$Concept  = Concept::search( '', '', '', 1, CB_TOKEN );
$per_page = $Concept->TotalPage;
$pages    = ceil( $Concept->TotalItems / $Concept->TotalPage );
$items    = $Concept->TotalItems;
Concept::syncAllForUpdate( $pages, $per_page );
