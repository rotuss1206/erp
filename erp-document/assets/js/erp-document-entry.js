/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_Doc = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            $( 'body' ).on( 'change', '#erp-state', this.erp_state_saver );

            $('#file_browser_breadcrumb_list li:first-child a').html('<span class="dashicons dashicons-admin-home"></span>');

            // file upload for file system
            $( '#loader_wrapper' ).hide();
            new WP_Uploader( 'doc-upload-pickfiles', 'doc-upload-filelist' );
            /* =============== Uploder ============ */
            $( 'body' ).on( 'click', '.doc-delete-file', this.Uploader.deleteFile );
            var employeeId = getParameterByName('id');
            $( '#doc-upload-container' ).on( 'click', '.doc-delete-file', this.Uploader.deleteFile );

            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(location.search);
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
            }
            // end of file upload in file system

        },
        Uploader: {
            deleteFile: function(e) {
                e.preventDefault();
                if (confirm('This file will be deleted permanently')) {
                    var that = $(this),
                        data = {
                            file_id: that.data('id'),
                            action: 'erp_hr_attachment_delete_file',
                            _wpnonce: wpErpDoc.nonce
                        };
                    $.post(wpErpDoc.ajaxurl, data, function() {});
                    that.closest('.doc-uploaded-item').fadeOut(function() {
                        $(this).remove();
                    });
                }
            }
        },
        erp_state_saver: function(){
        	$( "#erp_state_text" ).val( $( "#erp-state :selected" ).text() );
        }

    };

    $(function() {
        WeDevs_ERP_Doc.initialize();
    });
})(jQuery);