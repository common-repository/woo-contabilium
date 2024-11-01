<?php
/*
Plugin Name: Contabilium para WooCommerce
Plugin URI:  https://contabilium.com/
Description: Conector de integración a la API de Contabilium. Sincronice su stock y creee tareas programadas.
Version:     0.3
Author:      contabilium.com
Author URI:  https://shop.wanderlust-webdesign.com
WC tested up to: 4.3.2
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: woocommerce-contabilium
Domain Path: /languages
*/

require( dirname( __FILE__ ) . '/classes/CbApi.php' );
require( dirname( __FILE__ ) . '/classes/Concept.php' );
require( dirname( __FILE__ ) . '/classes/Customer.php' );
require( dirname( __FILE__ ) . '/classes/Helper.php' );
require( dirname( __FILE__ ) . '/classes/Order.php' );
require( dirname( __FILE__ ) . '/classes/SaleVoucher.php' );
require( dirname( __FILE__ ) . '/classes/Tools.php' );

use Contabilium\CbApi;
use Contabilium\Customer;
use Contabilium\Helper;
use Contabilium\Order;
use Contabilium\SaleVoucher;
use Contabilium\Tools;

defined( 'ABSPATH' ) or die( '¡Acceso prohibido! Su dirección IP ha sido almacenada' );

global $woocommerce;

$payment_methods = [
	'Efectivo'    => 'Efectivo',
	'Cheque'      => 'Cheque',
	'MercadoPago' => 'MercadoPago',
];

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	wp_cache_delete( 'alloptions', 'options' );


	if ( defined( 'cb_api_client_id' ) == null or defined( 'cb_api_client_secret' ) == null or defined( 'cb_api_pos_id' ) == null ) {
		add_option( 'cb_api_client_id', get_option( 'user_email' ) );
		add_option( 'cb_api_client_secret', '' );
		add_option( 'cb_api_pos_id', 0 );
	}

	$api                 = new CbApi();
	$saved_client_id     = get_option( 'cb_api_client_id' );
	$saved_client_secret = get_option( 'cb_api_client_secret' );
	if ( ! empty( $saved_client_secret ) || ! empty( $saved_client_secret ) ) {
		define( 'CB_TOKEN', $api->getAuth( $saved_client_id, $saved_client_secret ) );
	} else {
		define( 'CB_TOKEN', '' );
	}
	define( 'CB_POS_ID', get_option( 'cb_api_pos_id' ) );
	define( 'CB_CRON_RECURRENCE', get_option( 'cb_cron_recurrence' ) );

	function woocommerce_contabilium() {
		return true;
	}

	function contabilium_main_menu() {
		add_menu_page( 'Configuración', 'Contabilium', 'manage_options', 'contabilium_main_menu', 'contabilium_config_page_html', plugin_dir_url( __FILE__ ) . 'images/logo-icon.svg', 20 );
	}

	add_action( 'admin_menu', 'contabilium_main_menu' );

	function contabilium_options_page() {
		add_submenu_page( 'contabilium_main_menu', 'Configuración', 'Configuración', 'manage_options', 'contabilium_config_page', 'contabilium_config_page_html' );
	}

	add_action( 'admin_menu', 'contabilium_options_page' );

	function contabilium_suboptions_page() {
		add_submenu_page( 'contabilium_main_menu', 'Sincronización', 'Sincronización', 'manage_options', 'contabilium_sync_page', 'contabilium_sync_page_html' );
	}

	add_action( 'admin_menu', 'contabilium_suboptions_page' );

	function contabilium_config_page_html() {
		wp_enqueue_script( 'jquery' );

		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( Tools::isSubmit( 'submit' ) ) {
			update_option( 'cb_api_client_id', empty( $_POST["wc_api_client_id"] ) ? null : $_POST["wc_api_client_id"] );
			update_option( 'cb_api_client_secret', empty( $_POST["wc_api_client_secret"] ) ? null : $_POST["wc_api_client_secret"] );
			update_option( 'cb_api_pos_id', empty( $_POST["wc_api_pos_id"] ) ? null : $_POST["wc_api_pos_id"] );
			update_option( 'cb_api_integration', filter_input( INPUT_POST, 'wc_api_integration' ) );
			update_option( 'cb_sync_price', filter_input( INPUT_POST, 'wc_sync_price' ) );
			update_option( 'cb_overwrite_price', filter_input( INPUT_POST, 'wc_overwrite_price' ) );
			update_option( 'cb_sync_stock', filter_input( INPUT_POST, 'wc_sync_stock' ) );
			update_option( 'cb_default_dni', filter_input( INPUT_POST, 'wc_contabilium_default_dni' ) );
			update_option( 'cb_default_type', filter_input( INPUT_POST, 'wc_contabilium_default_type' ) );
			update_option( 'cb_custom_dni_name', filter_input( INPUT_POST, 'wc_contabilium_custom_dni_name' ) );
			update_option( 'cb_custom_type_name', filter_input( INPUT_POST, 'wc_contabilium_custom_type_name' ) );
			update_option( 'cb_cancelled_status', isset( $_POST['wc_contabilium_cancelled_status'] ) ? $_POST['wc_contabilium_cancelled_status'] : [] );
			update_option( 'cb_accepted_status', isset( $_POST['wc_contabilium_accepted_status'] ) ? $_POST['wc_contabilium_accepted_status'] : [] );
			cb_message( 'Los datos han sido guardados correctamente', 'success' );
		}

		$statuses = wc_get_order_statuses();

		?>
		<div class="wrap">
			<div class="cb_container">
				<div class="cb_heading"><?php echo esc_html( get_admin_page_title() ); ?></div>
				<p class="cb_description">Ingrese los datos de acceso por API REST. En caso de que no los tenga,
					solicitelos a soporte.</p>
				<form action="" method="post">
					<input type="hidden" name="action" value="updatesettings"/>
					<?php wp_nonce_field( 'add-user', '_wpnonce_add-user' ) ?>
					<table class="form-table">
						<thead></thead>
						<tbody>
						<tr class="form-field form-required">
							<th scope="row"><label><?php echo __( 'Email', 'woocommerce' ) ?> <span
										class="description"><?php _e( '(required)' ); ?></span></label></th>
							<td><input type="text" name="wc_api_client_id" id="wc_api_client_id" value="<?php echo get_option( 'cb_api_client_id' ) ?>" autocapitalize="none" autocorrect="off" maxlength="60"/></td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row">
								<label><?php echo __( 'Api Key', 'woocommerce' ); ?>
									<span class="description"><?php _e( '(required)' ); ?></span>
								</label>
							</th>
							<td>
								<input type="text" name="wc_api_client_secret" id="wc_api_client_secret" value="<?php echo get_option( 'cb_api_client_secret' ); ?>"/>
							</td>
						</tr>
					
						<tr class="form-field form-required">
							<th scope="row"><label><?php echo __( 'ID de Integración', 'woocommerce' ) ?> <span
										class="description"><?php _e( '(required)' ); ?></span></label></th>
							<td><input type="text" name="wc_api_integration" id="wc_api_integration" value="<?php echo get_option( 'cb_api_integration' ) ?>"/></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label
									for="wc_sync_price"><?php echo __( 'Sincronizar precios', 'contabilium' ) ?> </label></th>
							<td><input type="checkbox" name="wc_sync_price" id="wc_sync_price"
							           value="yes" <?php echo 'yes' === get_option( 'cb_sync_price' ) ? 'checked' : '' ?>/>
							</td>
						</tr>
						<tr class="form-field update-price <?php if ( 'yes' !== get_option( 'cb_sync_price' ) )
							echo 'hidden' ?>">
							<th scope="row"><label
									for="wc_overwrite_price"><?php echo __( 'Actualizar precio cuando el precio en Contabilium es menor al precio de promocion', 'contabilium' ) ?> </label>
							</th>
							<td><input type="checkbox" name="wc_overwrite_price" id="wc_overwrite_price"
							           value="yes" <?php echo 'yes' === get_option( 'cb_overwrite_price' ) ? 'checked' : '' ?>/>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row">
								<label for="wc_sync_stock">
									<?php echo __( 'Sincronizar inventario', 'contabilium' ); ?>
								</label>
							</th>
							<td><input type="checkbox" name="wc_sync_stock" id="wc_sync_stock"
									value="yes" <?php echo 'yes' === get_option( 'cb_sync_stock' ) ? 'checked' : ''; ?>/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="accepted_status">
									<?php echo __( 'Pedidos aceptados', 'contabilium' ); ?>
								</label>
							</th>
							<td>
								<select name="wc_contabilium_accepted_status[]" id="accepted_status" class="select2" multiple>
									<?php
									$status = get_option( 'cb_accepted_status', [ 'completed' ] );

									if ( ! is_array( $status ) ) {
										$status = [];
									}
									?>
									<?php foreach ( $statuses as $key => $label ) : ?>
										<option value="<?php echo $key; ?>" <?php echo in_array( $key, $status ) ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label for="cancelled_status">
									<?php echo __( 'Pedidos cancelados', 'contabilium' ); ?>
								</label>
							</th>
							<td>
								<select name="wc_contabilium_cancelled_status[]" id="cancelled_status" class="select2" multiple>
									<?php
									$status = get_option( 'cb_cancelled_status', [ 'refunded' ] );

									if ( ! is_array( $status ) ) {
										$status = [];
									}
									?>
									<?php foreach ( $statuses as $key => $label ) : ?>
										<option value="<?php echo $key; ?>" <?php echo in_array( $key, $status ) ? 'selected="selected"' : ''; ?>>
											<?php echo $label; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr class="form-field">
							<td colspan="2">
								<h3>Valores por defecto</h3>
								<p>
									Al definir un valor por defecto, éste se enviará a Contabilium y no se
									solicitará en el formulario de checkout.
								</p>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row">
								<label><?php echo __( 'Valor por defecto para DNI', 'contabilium' ); ?>
							</th>
							<td>
								<input type="text" name="wc_contabilium_default_dni"
									id="wc_contabilium_default_dni"
									value="<?php echo get_option( 'cb_default_dni' ); ?>"/>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row">
								<label>
									<?php echo __( 'Valor por defecto para Tipo de documento', 'contabilium' ); ?>
								</label>
							</th>
							<td><input type="text" name="wc_contabilium_default_type"
								id="wc_contabilium_default_type"
								value="<?php echo get_option( 'cb_default_type' ); ?>"/></td>
						</tr>
						<tr class="form-field">
							<td colspan="2">
								<h3>WooCommerce Customer Fields</h3>
								<p>
									Al definir el ID de un campo personalizado, Contabilium toma el valor de dicho campo en el proceso de checkout y no lo solicita.
								</p>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row">
								<label>
									<?php echo __( 'Nombre del campo para DNI', 'contabilium' ); ?>
									<span class="description">
										<?php _e( '(required)' ); ?>
									</span>
								</label>
							</th>
							<td>
								<input type="text" name="wc_contabilium_custom_dni_name"
									id="wc_contabilium_custom_dni_name"
									value="<?php echo get_option( 'cb_custom_dni_name' ); ?>"/>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row">
								<label>
									<?php echo __( 'Nombre del campo para Tipo de documento', 'contabilium' ); ?>
									<span class="description">
										<?php _e( '(required)' ); ?>
									</span>
								</label>
							</th>
							<td>
								<input type="text" name="wc_contabilium_custom_type_name"
									id="wc_contabilium_custom_type_name"
									value="<?php echo get_option( 'cb_custom_type_name' ); ?>"
								/>
							</td>
						</tr>
						<tr class="form-field">
							<td colspan="2">
								<h3>REST API</h3>
							</td>
						</tr>
						<tr>
							<th>
								Callback URL
							</th>
							<td>
								<input
									type="text"
									id="callback"
									name="callback_url"
									readonly="readonly"
									value="<?php bloginfo( 'url' ); ?>/wp-json/wp/v2/contabilium/"
									style="width: 80%"
								>
								<p>URL para ser configurada en Contabilium.com</p>
							</td>
						</tr>
						</tbody>
						<tfooter>
							<th scope="row">
							</td>
							<td><?php submit_button( 'Guardar' ); ?></td>
						</tfooter>
					</table>
				</form>
			</div>
		</div>
		<script>
			jQuery(document).ready(function () {
				jQuery('#wc_sync_price').on('click', function () {
					if (jQuery(this).prop('checked')) {
						jQuery('tr.update-price').removeClass('hidden');
					} else {
						jQuery('tr.update-price').addClass('hidden');
					}
				});
			});
		</script>
		<?php
	}


	function contabilium_sync_page_html() {
		$active_tab = ! empty( $_GET["tab"] ) ? $_GET["tab"] : 'concepts-tab';
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<nav class="nav-tab-wrapper">
				<!--a href="?page=contabilium_sync_page&amp;tab=sale-vouchers-tab" class="nav-tab !-->
				<!--?= $active_tab == 'sale-vouchers-tab' ? 'nav-tab-active' : '' ?>"!-->
				<!--?=_e('Comprobantes','woocommerce')?></a!-->
				<a href="?page=contabilium_sync_page&amp;tab=concepts-tab"
				   class="nav-tab <?php echo $active_tab == 'concepts-tab' ? 'nav-tab-active' : '' ?>"><?php echo _e( 'Productos/Servicios', 'woocommerce' ) ?></a>
			</nav>
			<?php
			if ( $active_tab != null ) {
				$file = dirname( __FILE__ ) . '/tabs/' . $active_tab . '.php';
				if ( file_exists( $file ) ) {
					require( $file );
				} else {
					cb_message( 'Opción no válida, por favor seleccione otra', 'error' );
				}
			}
			?>
		</div>
		<?php
	}

// Get customer ID
	function get_customer_id( $order_id ) {
		// Get the user ID
		$user_id = get_post_meta( $order_id, '_customer_user', true );
		return $user_id;
	}

	function get_customer_address( $user_id ) {

		$address = '';
		$address .= get_user_meta( $user_id, 'shipping_first_name', true );
		$address .= ' ';
		$address .= get_user_meta( $user_id, 'shipping_last_name', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_company', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_address_1', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_address_2', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_city', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_state', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_postcode', true );
		$address .= "\n";
		$address .= get_user_meta( $user_id, 'shipping_country', true );

		return $address;
	}


	function cb_message( $text, $type = 'success', $domain = 'woocommerce' ) {
		if ( ! empty( $text ) ) {
			$class   = 'notice notice-' . $type;
			$message = __( $text, $domain );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	add_action( 'admin_notices', 'cb_message' );


	/** WooCommerce campos extras necesarios para el registro **/
	function wc_extra_register_fields() { ?>       
		      <p class="form-row form-row-wide">
			      <label for=""><?php _e( 'Nombre / Razón Social', 'woocommerce' ); ?><span
					class="required">*</span></label>
			      <input type="text" class="input-text" name="cbCustomerName" id="" value=""/>
			      </p>
		      
		<p class="form-row form-row-wide">
			      <label for=""><?php _e( 'Tipo y Número de Documento', 'woocommerce' ); ?><span
					class="required">*</span></label>
			<select class="woocommmerce-input input-text" name="cbDocumentType" id="cbDocumentType"
			        style="max-width:14%;float:left;margin-right:1%;display: block;">
				<option value="DNI">DNI</option>
				<option value="CUIT">CUIT/CUIL</option>
			</select>
			      <input type="text" style="max-width:80%;float:left;display: block;"
			             class="woocommmerce-input input-text" name="cbDocumentNumber" id="cbDocumentNumber"
			             placeholder="Ingrese el número"/>
			      
		</p>
		<p class="form-row form-row-wide">
			      <label for=""><?php _e( 'Domicilio', 'woocommerce' ); ?><span class="required">*</span></label>
			      <input type="text" class="input-text" name="cbCustomerAddress" id="cbCustomerAddress"
			             placeholder="Ingrese su dirección"/>
			      </p>
		      
		<div class="clear"></div>
		      <?php
	}

	add_action( 'woocommerce_register_form_start', 'wc_extra_register_fields' );

	/** Ajax **/
	add_action( 'admin_enqueue_scripts', 'cb_api' );

	function cb_api() {
		if ( ! is_admin() ) {
			return;
		}
		wp_register_script( 'cb_api_script', plugins_url() . '/woocommerce-contabilium/js/cbapi.js', array( 'jquery' ), '1', true );
		wp_enqueue_script( 'cb_api_script' );
		wp_localize_script( 'cb_api_script', 'wc_cb', [
			'ajaxurl'         => 'https://rest.contabilium.com/api/common/getCiudades/',
			'comprobantesurl' => 'https://rest.contabilium.com/api/comprobantes/',
			'token'           => CB_TOKEN,
			'posid'           => CB_POS_ID
		] );
	}

	/** Hoja de estilos propia **/
	add_action( 'admin_head', 'cb_styles' );

	function cb_styles() {
		echo '<style>
    .cb_container {
        background:white;
        border-top:3px solid #2B9B8F;
        border-bottom:1px solid #ebebeb;
        border-right:1px solid #ebebeb;
        border-left:1px solid #ebebeb;
        margin: 0 0 10px 0;
        padding:8px;
        overflow:auto;
        -moz-box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    -webkit-box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    }
    .cb_container .cb_heading {
        color: #666;
        text-transform: uppercase;
        border-bottom: 1px solid #ebebeb;
        margin-bottom: 6px;
        padding: 4px 0;
        font-weight: bold;
    }
    .cb_container .cb_footer {
        color: #666;
        border-top: 1px solid #ebebeb;
        margin-top: 6px;
        padding: 4px 0;
    }
    .cb_container label {

    }
    .cb_row {
        padding: 2px;
        margin-top: 0px;
        margin-left: 0px;
        margin-right: 0px;
        margin-bottom: 12px;
    }   
    /**
    .cb_row input[type="text"], input[type="search"] {
        padding: 4px;
        border-top:  none;
        border-left: none;
        border-right: none;
        border-bottom: 2px solid #BF315F;
        box-shadow: none;
    } 
    .cb_row input[type="text"]:focus, input[type="search"]:focus {
        box-shadow:none;
    }
    **/
    .cb_container .cb_description {
        color: #777;
    }
 </style>';
	}

}

// New modifications
function contabilium_hide_top_menu() {
	echo '<style  type="text/css">.toplevel_page_contabilium_main_menu .wp-first-item {display: none }</style>';
}

add_action( 'admin_head', 'contabilium_hide_top_menu' );

function cb_admin_enqueue_scripts( $page ) {
	if ( 'contabilium_page_contabilium_config_page' === $page ) {
		wp_enqueue_script( 'select2',plugin_dir_url( __FILE__ ) . 'js/vendor/select2.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'contabilium-admin',plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'select2' ) );
		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'cb_admin_enqueue_scripts', 10, 1 );


include_once( 'api.php' );
include_once( 'includes/manage-orders.php' );