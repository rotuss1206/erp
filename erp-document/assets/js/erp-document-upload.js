;
(function ( $ ) {
    /**
     * Upload handler helper
     *
     * @param string browse_button ID of the pickfile
     * @param string container ID of the wrapper
     */
    var fileUploadedCounter = 0;

    window.WP_Uploader = function ( browse_button, container ) {
        this.container = container;
        this.browse_button = browse_button;

        //if no element found on the page, bail out
        if ( !$( '#' + browse_button ).length ) {
            return;
        }

        //instantiate the uploader
        this.uploader = new plupload.Uploader( {
            runtimes: 'html5,silverlight,flash,html4',
            browse_button: browse_button,
            container: container,
            multipart: true,
            multipart_params: [],
            multiple_queues: false,
            urlstream_upload: true,
            file_data_name: 'doc_attachment',
            max_file_size: wpErpDoc.plupload.max_file_size,
            url_default: wpErpDoc.plupload.url,
            url: wpErpDoc.plupload.url,
            flash_swf_url: wpErpDoc.plupload.flash_swf_url,
            silverlight_xap_url: wpErpDoc.plupload.silverlight_xap_url,
            filters: wpErpDoc.plupload.filters,
            resize: wpErpDoc.plupload.resize
        } );

        //attach event handlers
        this.uploader.bind( 'Init', $.proxy( this, 'init' ) );
        this.uploader.bind( 'FilesAdded', $.proxy( this, 'added' ) );
        this.uploader.bind( 'QueueChanged', $.proxy( this, 'upload' ) );
        this.uploader.bind( 'UploadProgress', $.proxy( this, 'progress' ) );
        this.uploader.bind( 'Error', $.proxy( this, 'error' ) );
        this.uploader.bind( 'FileUploaded', $.proxy( this, 'uploaded' ) );

        this.uploader.init();
    };

    WP_Uploader.prototype = {

        init: function ( up, params ) {
            // console.log('uploader init');
        },

        added: function ( up, files ) {
            var $container = $( '#' + this.container ).find( '.doc-upload-filelist' );
            //appending head title area
            if ( fileUploadedCounter == 0 ) {
                $container.append( '<div class="upload-item-head">Upload Items</div>' );
            }

            $.each( files, function ( i, file ) {
                $container.append(
                    '<div class="upload-item" id="' + file.id + '"><div class="progress">' +
                    '<div class="percent">0%</div><div class="bar"></div></div><div class="filename original">' +
                    file.name + ' (' + plupload.formatSize( file.size ) + ') <b></b>' +
                    '</div></div>' );
            } );

            up.refresh(); // Reposition Flash/Silverlight
            // up.start();
        },

        upload: function ( uploader ) {

            var parent_id = $( '#file_browser_breadcrumb_list' ).find( 'a' ).last().data( 'id' );
            var employee_id = this.getParameterByName( 'id' );

            this.uploader.settings.url = this.uploader.settings.url_default + '&parent_id=' + parent_id + '&employee_id=' + employee_id;
            this.uploader.start();
            // upload-item-head head title should be visible now
            $( '.doc-upload-filelist' ).show();
        },

        progress: function ( up, file ) {
            var item = $( '#' + file.id );

            $( '.bar', item ).width( (200 * file.loaded) / file.size );
            $( '.percent', item ).html( file.percent + '%' );
        },

        error: function ( up, error ) {
            $( '#' + this.container ).find( '#' + error.file.id ).remove();
            alert( 'Error #' + error.code + ': ' + error.message );
        },

        uploaded: function ( up, file, response ) {

            var res = $.parseJSON( response.response );

            $( '#' + file.id + " b" ).html( "100%" );
            $( '#' + file.id ).remove();

            if ( res.success ) {
                var $container = $( '#' + this.container ).find( '.doc-upload-filelist' );
                // now after uploading file in runtime, refresh/reload the file folder list ;) we are done
                var current_dir_id = $( '#file_browser_breadcrumb_list' ).find( 'a' ).last().data( 'id' );
                var employee_id = this.getParameterByName( 'id' );
                var action_for_ce_dir_info = 'wp-erp-rec-get-dir-info';
                var action_for_ce_file_info = 'wp-erp-rec-get-file-info';

                $.get( wpErpDoc.ajaxurl,
                    {
                        action: action_for_ce_dir_info,
                        employee_id: employee_id,
                        dir_id: current_dir_id
                    }, function ( response ) {
                        if ( response.success === true ) {
                            if ( employee_id == '' ) {
                                company_dir_file_system.dir_data = response.data;
                            } else {
                                dir_file_system.dir_data = response.data;
                            }
                        }
                    }
                );

                $.get( wpErpDoc.ajaxurl,
                    {
                        action: action_for_ce_file_info,
                        employee_id: employee_id,
                        dir_id: current_dir_id
                    }, function ( response ) {
                        if ( response.success === true ) {
                            if ( employee_id == '' ) {
                                company_dir_file_system.file_data = response.data;
                            } else {
                                dir_file_system.file_data = response.data;
                            }
                        }
                    }
                );
                //dir_file_system.dir_file_checkboxx = [];
                fileUploadedCounter++;
                // check total selected items is equal to uploaded items
                if ( up.files.length == fileUploadedCounter ) {
                    //$( '.doc-upload-filelist' ).hide().animate( { width: '0px' }, 5000, function(){ $( this ).hide() } );
                    $( '.doc-upload-filelist' ).hide('slow');
                }
            } else {
                alert( res.error );
            }
        },

        getParameterByName: function ( name ) {
            name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
            var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                results = regex.exec( location.search );
            return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
        }
    };

    $( function () {
        new WP_Uploader( 'doc-upload-pickfiles-cm', 'doc-upload-container-cm' );
    } );
})( jQuery );