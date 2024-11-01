(function($){
    var TOKEN = wc_cb.token;
    $("#cbCustomerState").on('change', function(){
        var state_id = $(this).val();

        if (!TOKEN) {            
            return false;
        }

        $.ajax({
            url: wc_cb.ajaxurl + state_id,
            type: "GET",
            beforeSend: function (xhr) { 
                xhr.setRequestHeader('Authorization', 'Bearer ' + TOKEN);
                $("#cbCustomerCity").empty().html('<option>Cargando...</option>'); 
            },
            success: function (data) {
                console.log(JSON.stringify(data, null, 4));
                $("#cbCustomerCity").empty().append('<option>Seleccione:</option>');
                $.each(data, function(index, item){
                    $("#cbCustomerCity").append('<option value=' + item.ID + '>' + item.Nombre + '</option>')
                });
            },
            error: function (data) {
                console.log(JSON.stringify(data, null, 4));
            }
        });
        return false;

    });


    $(document).on('click', '.send-e-invoice', function() {
        var id = $(this).data('voucher-id');
        console.log('Voucher ID: ' + id + ' URL:' + wc_cb.comprobantesurl );
        var url = wc_cb.comprobantesurl + 'emitirFE?id=' + id;
        if (!TOKEN) {
           return false;
        }

        $.ajax({
            url: url,
            type: "GET",
            //data: 'comprobantes',
            beforeSend: function (xhr) { xhr.setRequestHeader('Authorization', 'Bearer ' + TOKEN); },
            success: function (data) {
                console.log("Se ha emitido la factura electronica");
            },
            error: function (data) {
                console.log("Ha ocurrido un error al emitir la factura electronica. Error: " + data.responseText)
            }
        });


    });

    $(document).on('click','.download-invoice', function(){
        var id = $(this).data('voucher-id');
        console.log('Voucher ID: ' + id + ' URL:' + wc_cb.comprobantesurl);
        var url = wc_cb.comprobantesurl + 'obtenerPdf?id=' + id;
        if (!TOKEN) {
           return false;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'arraybuffer';
        xhr.setRequestHeader('Authorization', 'Bearer ' + TOKEN);
        xhr.onload = function (e) {
            if (this.status == 200) {
                var blob = new Blob([this.response], { type: "application/pdf" });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Comprobante_" + id + ".pdf";
                link.click();
            }
            if (this.status == 500) {
                console.log('Ocurrio un error 500');
            }
        };
        xhr.send();
        return false;
    });        


})(jQuery);