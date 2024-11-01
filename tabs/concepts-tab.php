<?php

use Contabilium\Concept;
use Contabilium\Tools;

if ( ! Tools::getValue( 'proceed' ) ) {
	$concept  = Concept::search( '', '', '', 1, CB_TOKEN );
	$per_page = is_object( $concept ) ? $concept->TotalPage : 10;
	$pages    = is_object( $concept ) ? ceil( $concept->TotalItems / $concept->TotalPage ) : 1;
	$items    = is_object( $concept ) ? $concept->TotalItems : 100;
}
?>
<div class="cb_container">
    <form action="admin.php?page=contabilium_sync_page&tab=concepts-manual-tab" method="post"/>
    <div class="cb_heading">
    <?=_e('Productos y Servicios', 'woocommerce');?>
    </div>
    <h3>Sincronizaci&oacute;n manual</h3>
    <?=cb_message('Use esta opción si desea actualizar el listado de productos desde Contabilium a su tienda', 'info')?>
    <input type="hidden" name="pages" value="<?=$pages?>"/>
    <input type="hidden" name="per_page" value="<?=$per_page?>"/>
    <input type="hidden" name="items" value="<?=$items?>"/>
    <button type="submit" class="button button-secondary" name="proceed" value="1"><?=_e("Iniciar sincronización", "woocommerce")?></button>
    <br>
    <hr />
    </form>
    
    <?php //if (PHP_OS =='Linux') {
        if(false){ ?>
    <form method="post">
    <h3>Sincronizaci&oacute;n programada</h3>
    <?=cb_message('Use esta opción si desea sincronizar sus productos/servicios con una tarea programada, elija el rango de su preferencia.','info') ?>
    <div>   
    <?php
        
        $ranges = [
            "* * * * *" => " Una vez por minuto",
            "*/5 * * * *" => "Una vez cada cinco minutos",
            "0,30 * * * *" => "Dos veces por hora",
            "0 0 */2 ? * *" => "Una vez por hora",            
            "0 0 12 */7 * ?" => "Una vez por semana",
            "0 0 1,15 * *" => "El 1 y el 15 del mes",
            "0 0 1 * *" => "Una vez al mes",
            "0 0 1 1 *" => "Una vez por año"
        ];
        
       
        //$current = shell_exec('crontab -l');
        //if ($current != null) echo '<p>Ya existe una tarea programa registrada, si crea una nueva la anterior será eliminada.</p>';

        if ( Tools::isSubmit('submit') ) {
            
            //$range = Tools::getValue('range');
            $range = Tools::getValue('recurrence');
            $plugin_dir = 'wp-content/plugins/woocommerce-contabilium/';
            $dir = get_home_path() . $plugin_dir;
            $path = get_site_url() . '/'. $plugin_dir. 'cron.php';
            exec('crontab -r');
            $output = shell_exec('crontab -l');
            file_put_contents($dir . 'crontab.txt', $output.''.$range.' wget '.$path.' > /dev/null 2>&1'.PHP_EOL);
            $exec = exec('crontab '.$dir.'crontab.txt');
            $data = [
                "recurrence" => $range,
                "path" => $path,
            ];
            //cb_message("La tarea será ejecutada " . strtolower($ranges["$range"]) . "");
            
            // Cron Tasks 

            if ( $range != '--') {
                
                if (add_option('cb_cron_data', serialize($data))) {
                    cb_message("Se ha registrado la recurrencia del CRON correctamente.", "success");
                }

            } else {

                cb_message("Se ha desactivado la tarea programada correctamente.", "success");

            }
        }
        
    ?>     
        <select name="recurrence" id="recurrence" class="">
            <option value="--">-- Configuración común --</option>
            <?php
                if (get_option('cb_cron_data')) {
                    $cron = unserialize(get_option('cb_cron_data'));
                }
                foreach($ranges as $i => $r){
                    echo '<option value="'.$i.'">'.$r.'</option>';
                }
                
            ?>
        </select> 
    </div>
    <br />
    <div class="clearfix">
        <button class="button button-secondary" type="submit" name="submit" value="1"><?=_e("Programar sincronización", "woocommerce")?></button>
    </div>
    </form>
    <?php 
    } else {
            //cb_message("El servicio de sincronización programada no está disponible en este servidor.", "warning");
    }?>
</div>
