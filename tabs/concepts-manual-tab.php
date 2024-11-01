<?php

use Contabilium\Concept;
use Contabilium\Tools;

if ( Tools::getValue( 'proceed' ) ) {
	$per_page = Tools::getValue( 'per_page' );
	$pages    = Tools::getValue( 'pages' );
	$items    = Tools::getValue( 'items' );
	$success  = Concept::syncAllForUpdate( $pages, $per_page );

	if ( $success > 0 ) {
		cb_message( 'Se ha actualizado el precio y existencia de los productos sincronizados correctamente.', 'success' );

	} else {
		if ( isset( $_SESSION['error'] ) ) {
			$msg = $_SESSION['error'];
			unset( $_SESSION['error'] );
		} else {
			$msg = 'Ha ocurrido un error al actualizar los productos, por favor intente de nuevo.';
		}
		cb_message( $msg, 'error' );
	}
	?>
<?php } ?>
<a href="admin.php?page=contabilium_sync_page&tab=concepts-tab" class="button button-secondary">Volver</a>

