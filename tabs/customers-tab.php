<h1>Clientes <a href="admin.php?page=contabilium&tab=customer&type=create" class="page-title-action">Nuevo</a></h1>
<div class="cb_container">
	<table class="wp-list-table widefat fixed striped pages" id="cbCustomersList">
		<thead>
		<th>Razón Social</th>
		<th>Código</th>
		<th>Email</th>
		<th>Tipo Doc.</th>
		<th>Nro. Doc.</th>
		<th>Cat. Impositiva</th>
		<th>Teléfono</th>
		<th>Acciones</th>
		</thead>
		<tbody>
		<?php

		use Contabilium\Customer;

		$page        = 1;
		$total_pages = 1;

		for ( $page = 1; $page <= $total_pages; $page ++ ) {
			$customers = Customer::search( '', $page, CB_TOKEN );
			foreach ( $customers->Items as $customer ) {
				?>
				<tr>
					<td><?= $customer->RazonSocial ?></td>
					<td><?= $customer->Codigo ?></td>
					<td><?= $customer->Email ?></td>
					<td><?= $customer->TipoDoc ?></td>
					<td><?= $customer->NroDoc ?></td>
					<td><?= $customer->CondicionIva ?></td>
					<td><?= $customer->Telefono ?></td>
					<td>
						<a class="button tips edit"
						   href="admin.php?page=contabilium&tab=customer&type=update&id=<?= $customer->Id ?>"><span
								class="dashicons dashicons-edit"></span></a>
						<a class="button tips delete"
						   href="admin.php?page=contabilium&tab=customer&type=delete&id=<?= $customer->Id ?>"><span
								class="dashicons dashicons-trash"></span></a>
					</td>
				</tr>
				<?php
			}
			$total_pages = $customers->TotalPage;
		} // End for.
		?>
		</tbody>
		<tfoot></tfoot>
	</table>
</div>
<link href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" rel="stylesheet"/>
<script src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script>
	jQuery(document).ready(function () {
		jQuery('#cbCustomersList').DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
				"lengthMenu": "Mostrar _MENU_ registros por página",
				"zeroRecords": "Sin datos para mostrar - disculpe",
				"info": "Mostrando página _PAGE_ de _PAGES_",
				"infoEmpty": "No hay registros aún",
				"infoFiltered": "(filtrado de _MAX_ total registros)"
			}
		});
	});
</script>