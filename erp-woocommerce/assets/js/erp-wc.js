;(function($){

    var total_orders = 0;

    function erp_wc_sync_order_data( submit, wrap, loader, responseDiv ) {
        var s_data = {
            action : 'erp_wc_sync_table',
            limit: wrap.find('input[name=limit]').val(),
            offset: wrap.find('input[name=offset]').val(),
            total_orders : total_orders,
            _wpnonce: erpWC.nonce
        };

        $.post( erpWC.ajaxurl, s_data, function(resp) {
            if ( resp.success ) {
                if( resp.data.total_orders != 0 ){
                    total_orders = resp.data.total_orders;
                }

                completed = (resp.data.done*100)/total_orders;

                completed = Math.round(completed);

                 $( "#regen-pro" ).animate({
                    width: completed+'%',
                  }, 500 );
                // $('#regen-pro').width();

                if(!$.isNumeric(completed)){
                    $('#regen-pro').html('Finished');
                }else{
                    $('#regen-pro').html(completed+'%');
                }

                $('#progressbar').show();

                responseDiv.html( '<span>' + resp.data.message +'</span>' );

                if ( resp.data.done != 'All' ) {
                    wrap.find('input[name="offset"]').val( resp.data.offset );
                    erp_wc_sync_order_data( submit, wrap, loader, responseDiv );
                    return;
                } else {
                    submit.removeAttr('disabled');
                    loader.hide();
                    wrap.find('input[name="offset"]').val( 0 );
                }
            } else {
                responseDiv.html( '<span>' + resp.data.message +'</span>' );

                submit.removeAttr('disabled');
                loader.hide();
                wrap.find('input[name="offset"]').val( 0 );
            }
        } );
    }

    $(document).ready( function() {
        $('div.erp-wc-data-sync').on('click', 'input#btn-rebuild', function(e) {
            e.preventDefault();

            var submit = $(this),
                wrap = $(this).closest('#regen-sync-table'),
                loader = wrap.find('.regen-sync-loader'),
                responseDiv = $('.regen-sync-response');

            submit.attr('disabled', 'disabled');
            loader.show();

            erp_wc_sync_order_data( submit, wrap, loader, responseDiv );
        });
    });

})(jQuery);