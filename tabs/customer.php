<?php

use Contabilium\Customer;
use Contabilium\Tools;
use Contabilium\Helper;

$type        = (Tools::g('type')) ? Tools::g('type') : null;
$id          = (Tools::g('id')) ? Tools::g('id') : null ;
$action      = ( isset($_GET["id"]) && is_numeric($_GET["id"]) ) ? 'updatecustomer' : 'createcustomer';
$title       = ($action == 'createcustomer') ? 'Registrar cliente' : 'Actualizar cliente';
$description = ($action == 'createcustomer') ? 'Registrar un nuevo cliente en Contabilium.com' :'Actualizar datos del cliente en Contabilium.com';
if ( Tools::p("action") ) {
                $data = [
                    'id' => (int)Tools::p('cbCustomerId'),
                    'razonSocial' => (string)Tools::p('cbCustomerName'),
                    'nombreFantasia' => (string)Tools::p('cbFantasyName'),
                    'condicionIva' => (string)Tools::p('cbIvaCondition'),
                    'personeria' => (string)Tools::p('cbCustomerType'),
                    'tipoDoc' => (string)Tools::p('cbDocumentType'),
                    'nroDoc' => (int)Tools::p('cbDocumentNumber'),
                    'telefono' => (string)Tools::p('cbCustomerPhone'),
                    'email' => (string)Tools::p('cbCustomerEmail'),
                    'idPais' => (int)Tools::p('cbCustomerCountry'),
                    'idProvincia' => (int)Tools::p('cbCustomerState'),
                    'idCiudad' => (int)Tools::p('cbCustomerCity'),
                    'domicilio' => (string)Tools::p('cbCustomerAddress'),
                    'pisoDepto' => (string)Tools::p('cbFloorNumber'),
                    'cp' => (string)Tools::p('cbPostalCode'),
                    'observaciones' => (string)Tools::p('cbCustomerNotes'),
                    'codigo' => (string)Tools::p('cbCustomerCode')
                    ];

                if ( Tools::p("action") == 'updatecustomer' ) {                    
                    $customer = Customer::update($data, CB_TOKEN);
                    if( $customer == null ) {
                        cb_message('Se han actualizado los datos del cliente correctamente.');
                    }
                } 
                
                if ( Tools::p("action") == 'createcustomer' ) {
                    
                    $customer = Customer::add($data, CB_TOKEN);

                    if ( is_numeric($customer)  ) {
                        cb_message('Se han registrado los datos del cliente correctamente. ' .  $customer);
                    } else {
                        cb_message('Ha ocurrido un error al intentar registrar los datos del cliente. ' .  $customer);
                    }
                    
                    //Tools::getOutput($data);
                }
                
               
               
}

if ( $type == 'delete' &&  is_numeric($id ) ) {

    $customer = Customer::delete($id, CB_TOKEN);
              
    if ( $customer == null ) {
        cb_message('Se han eliminado los datos del cliente correctamente');
    } else {
        cb_message('Ha ocurrido un error al intentar eliminar los datos del cliente');
    }
}


if ( $type !== null || $type != 'delete' ) {
    if ( $id ) {
         $customer = Customer::get($id, CB_TOKEN);
    } 
    
?>
<h1><?=$title?></h1>
<p><?=$description?></p>
<form id="cbCustomerForm" method="post">
    <input type="hidden" name="action" value="<?=$action?>" />
    <input type="hidden" name="cbCustomerId" value="<?=$id?>" />
    <input type="hidden" name="cbCustomerCountry" value="10" />
    <?php wp_nonce_field( $type . '-customer', '_wpnonce_'.$type.'-customer' ) ?>
    <table class="form-table" style="max-width:65%">
    <tr class="form-field form-required">
        <th scope="row">
            <label for="cbCustomerName"><?=_e('Razón Social', 'woocommerce')?></label>        
        </th>
        <td>
            <input type="text" name="cbCustomerName" placeholder="Razón Social" value="<?=$customer->RazonSocial?>" />
        </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbFantasyName"><?=_e('Nombre Fantasia', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbFantasyName" id="cbFantasyName" placeholder="Nombre Fantasía" value="<?=$customer->NombreFantasia?>" />        
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerCode"><?=_e('Código', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerCode" id="cbCustomerCode" placeholder="Código" value="<?=$customer->Codigo?>" />        
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbIvaCondition"><?=_e('Categoría Impositiva', 'woocommerce')?></label>
    </th>
    <td>
        <select name="cbIvaCondition" id="cbIvaCondition">
            <option value="RI" <?=Tools::_selected($customer->CondicionIva, 'RI')?>>Responsable Inscripto</option>
            <option value="CF" <?=Tools::_selected($customer->CondicionIva, 'CF')?>>Consumidor Final</option>
            <option value="MO" <?=Tools::_selected($customer->CondicionIva, 'MO')?>>Monotributista</option>
            <option value="EX" <?=Tools::_selected($customer->CondicionIva, 'EX')?>>Exento</option>            
        </select>       
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerType"><?=_e('Personería', 'woocommerce')?></label>
    </th>
    <td>
        <select name="cbCustomerType" id="cbCustomerType">
            <option value="J" <?=Tools::_selected($customer->Personeria, 'J')?>>Jurídica</option>
            <option value="F" <?=Tools::_selected($customer->Personeria, 'F')?>>Física</option>
        </select>       
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbDocumentType"><?=_e('Tipo Documento', 'woocommerce')?></label>
    </th>
    <td>
        <select name="cbDocumentType" id="cbDocumentType">
            <option value="DNI" <?=Tools::_selected($customer->TipoDoc, 'DNI')?>>DNI</option>
            <option value="CUIT" <?=Tools::_selected($customer->TipoDoc, 'CUIT')?>>CUIT</option>
        </select>        
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbDocumentNumber"><?=_e('Número Documento', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbDocumentNumber" id="cbDocumentNumber" placeholder="Número" value="<?=$customer->NroDoc?>" />     
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerPhone"><?=_e('Teléfono', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerPhone" id="cbCustomerPhone" placeholder="Teléfono" value="<?=$customer->Telefono?>" />     
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerEmail"><?=_e('Email', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerEmail" id="cbCustomerEmail" placeholder="Email" value="<?=$customer->Email?>" value="<?=$customer->Email?>" />     
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerState"><?=_e('Provincia', 'woocommerce')?></label>
    </th>
    <td>       
        <select name="cbCustomerState" id="cbCustomerState">
            <?php
                $states = Helper::getStates(10, CB_TOKEN);
                foreach ( $states as $state ) {
                    ?>
                    <option value="<?=$state->ID?>" <?=Tools::_selected($customer->IdProvincia, $state->ID)?>><?=$state->Nombre?></option>
                    <?php
                }

            ?>
        </select>        
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerCity"><?=_e('Ciudad', 'woocommerce')?></label>
    </th>
    <td>
        <select name="cbCustomerCity" id="cbCustomerCity">
            <?php
                $cities = Helper::getCities(2, CB_TOKEN);
                foreach ( $cities as $city ) {
                    ?>
                    <option value="<?=$city->ID?>" <?=Tools::_selected($customer->IdCiudad, $city->ID)?>><?=$city->Nombre?></option>
                    <?php
                }

            ?>
        </select>        
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerAddress"><?=_e('Domicilio', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerAddress" id="cbCustomerAddress" placeholder="<?=_e('Domicilio', 'woocommerce')?>" value="<?=$customer->Domicilio?>" />                
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbFloorNumber"><?=_e('Piso Depto.', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbFloorNumber" id="cbFloorNumber" placeholder="<?=_e('Piso Depto.', 'woocommerce')?>" value="<?=$customer->PisoDepto?>" />                
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbPostalCode"><?=_e('Código Postal', 'woocommerce') ?></label>
    </th>
    <td>
        <input type="text" name="cbPostalCode" id="cbPostalCode" placeholder="<?=_e('Código Postal', 'woocommerce')?>" value="<?=$customer->Cp?>"/>                
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerCode"><?=_e('Código', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerCode" id="cbCustomerCode" placeholder="<?=_e('Código', 'woocommerce')?>" value="<?=$customer->Codigo?>" />                
    </td>
    </tr>
    <tr class="form-field form-required">
    <th scope="row">
        <label for="cbCustomerNotes"><?=_e('Observaciones', 'woocommerce')?></label>
    </th>
    <td>
        <input type="text" name="cbCustomerNotes" id="cbCustomerNotes" placeholder="<?=_e('Observaciones', 'woocommerce')?>" value="<?=$customer->Observaciones?>"/>                
    </td>
    </tr>
    <tr>
    <th></th>
    <td>
        <input type="submit" class="button button-primary" name="cbSubmit" value="<?=_e(ucfirst($type))?>" />
    </td>
    </tr>
    </table>    
</form>
<?php
}
?>
