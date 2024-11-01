<?php
use Contabilium\SaleVoucher;
?>
<div class="cb_container">
    <div class="cb_heading">
        Comprobantes
    </div>
    <table class="wp-list-table widefat fixed striped pages" id="cbSaleVouchersList">
        <thead>
            <th>Razón Social</th>
            <th>Número</th>
            <th>Tipo</th>
            <th>Condición</th>
            <th>Modo</th>
            <th>CAE</th>
            <th>Total Neto</th>
            <th>Total Bruto</th>
            <th>Acciones</th>
        </thead>
        <tbody>
        <?php
        $page            = 1;
	        $total_pages = 1;

            for ( $page = 1; $page <= $total_pages; $page ++ ) {
	            $SaleVouchers = SaleVoucher::search( '', date( "Y-01-01" ), date( "Y-m-d" ), 1, CB_TOKEN );

	            foreach ( $SaleVouchers->Items as $SaleVoucher ) {

		            // Filtrar solo por canal WP
		            // if ( $SaleVoucher->Items == 'WP' ) {

		            ?>
		            <tr>
			            <td><?= $SaleVoucher->RazonSocial ?></td>
			            <td>
				            <?= $SaleVoucher->Numero ?>
			            </td>
			            <td><?= $SaleVoucher->TipoFc ?></td>
			            <td><?= $SaleVoucher->CondicionVenta ?></td>
			            <td><?= $SaleVoucher->Modo ?></td>
			            <td><?= ( $SaleVoucher->Cae == "" || $SaleVoucher->Cae == null ) ? "<b>Pendiente</b>" : $SaleVoucher->Cae ?></td>
			            <td><?= $SaleVoucher->ImporteTotalNeto ?></td>
			            <td><?= $SaleVoucher->ImporteTotalBruto ?></td>
			            <td>
				            <?php if ( ( $SaleVoucher->Cae == "" || $SaleVoucher->Cae == null ) && $SaleVoucher->TipoFc != "COT" ) { ?>
					            <a class="button tips add send-e-invoice" href="#"
					               data-voucher-id="<?= $SaleVoucher->Id ?>"><span
							            class="dashicons dashicons-media-spreadsheet"></span></a>
				            <?php } else if ( $SaleVoucher->TipoFc != "COT" ) { ?>
					            <a class="button tips download download-invoice" href="#"
					               data-voucher-id="<?= $SaleVoucher->Id ?>"><span
							            class="dashicons dashicons-download"></span></a>
				            <?php } ?>
			            </td>
		            </tr>
		            <?php

		            // Fin de condición
		            // }
	            }
	            $total_pages = $SaleVouchers->TotalPage;
            }
	        ?>
        </tbody>
        <tfoot></tfoot>
    </table>
</div>
<link href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet" />
<script src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" ></script>
<script>
    jQuery(document).ready(function(){
    jQuery('#cbSaleVouchersList').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "Sin datos para mostrar - disculpe",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros aún",
            "infoFiltered": "(filtrado de _MAX_ total registros)"
        }
    } );
});
</script>