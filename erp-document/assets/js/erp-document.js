(function ( $ ) {
    // Vue.config.devtools = true;
    var renameComponent = Vue.extend( {
        data: function () {
            return {
                inEditMode: false,
                lastValue: '',
                blurControl: true,
                getDuplicateCounter: 0
            }
        },
        props: [ 'd_data', 'listcurrentindex' ],
        template: '<div v-on:click="clicked" class="list-item-name"><a v-if=" d_data.is_dir == 1 " v-show="!inEditMode" class="file_dir_link dir-link" href="#" ' +
        '@click.prevent.stop="dirLinkClicked( d_data.eid, d_data.dir_id, d_data.dir_file_name )">{{ d_data.dir_file_name }}' +
        '</a><a v-if=" d_data.is_dir == 0 " v-show="!inEditMode" class="file_dir_link dir-link" @click.stop href="{{d_data.attactment_url}}" ' +
        '">{{ d_data.dir_file_name }}' +
        '</a><input v-show="inEditMode" v-el:meta-key v-model="d_data.dir_file_name" @blur="blurTrigger" v-on:keyup.enter="focusOut" v-on:keyup.esc="focusOut" type="text"></div>',
        methods: {
            clicked: function () {
                this.inEditMode = true;
                var self = this;
                Vue.nextTick( function () {
                    self.$els.metaKey.focus();
                } );
                this.lastValue = this.d_data.dir_file_name;
            },
            blurTrigger: function () {
                if ( this.blurControl == true ) {
                    var dir_file_name_array = [];
                    if ( this.d_data.eid != "0" ) {
                        for ( var key in dir_file_system.dir_data ) {
                            dir_file_name_array.push( dir_file_system.dir_data[ key ].dir_file_name );
                        }
                        for ( var key in dir_file_system.file_data ) {
                            dir_file_name_array.push( dir_file_system.file_data[ key ].dir_file_name );
                        }
                    } else {
                        for ( var key in company_dir_file_system.dir_data ) {
                            dir_file_name_array.push( company_dir_file_system.dir_data[ key ].dir_file_name );
                        }
                        for ( var key in company_dir_file_system.file_data ) {
                            dir_file_name_array.push( company_dir_file_system.file_data[ key ].dir_file_name );
                        }
                    }

                    this.checkDuplicateDirFileName( dir_file_name_array );

                    if ( this.getDuplicateCounter > 0  ) { // name already exist
                        this.d_data.dir_file_name = this.lastValue;
                        if ( this.d_data.eid != "0" ) { // check for employee
                            dir_file_system.isError = true;
                            dir_file_system.isVisible = true;
                            dir_file_system.response_message = 'Given name already exist! Please choose another name!';
                            setTimeout( function () {
                                dir_file_system.isVisible = false;
                            }, 3000 );
                        } else {
                            company_dir_file_system.isError = true;
                            company_dir_file_system.isVisible = true;
                            company_dir_file_system.response_message = 'Given name already exist! Please choose another name!';
                            setTimeout( function () {
                                company_dir_file_system.isVisible = false;
                            }, 3000 );
                        }
                    }
                    if ( this.lastValue != this.d_data.dir_file_name ) {
                        this.renameDir();
                    }
                }
                this.inEditMode = false;
            },
            focusOut: function () {
                this.blurControl = false;
                var dir_file_name_array = [];
                var incremental_flag = 0;
                if ( this.d_data.eid != "0" ) { // check for employee
                    if ( this.d_data.dir_file_name == '' || this.d_data.dir_file_name == null ) {
                        this.d_data.dir_file_name = this.lastValue;
                        dir_file_system.isError = true;
                        dir_file_system.isVisible = true;
                        dir_file_system.response_message = 'You cannot give an empty name! Please enter a name!';
                        setTimeout( function () {
                            dir_file_system.isVisible = false;
                        }, 3000 );
                    } else {
                        for ( var key in dir_file_system.dir_data ) {
                            dir_file_name_array.push( dir_file_system.dir_data[ key ].dir_file_name );
                        }
                        for ( var key in dir_file_system.file_data ) {
                            dir_file_name_array.push( dir_file_system.file_data[ key ].dir_file_name );
                        }
                        for ( var key in dir_file_name_array ) {
                            if ( dir_file_name_array[ key ] == this.d_data.dir_file_name ) {
                                incremental_flag++;
                            }
                        }
                        if ( incremental_flag > 1 ) { // name already exist
                            this.d_data.dir_file_name = this.lastValue;
                            dir_file_system.isError = true;
                            dir_file_system.isVisible = true;
                            dir_file_system.response_message = 'Given name already exist! Please choose another name!';
                            setTimeout( function () {
                                dir_file_system.isVisible = false;
                            }, 3000 );
                        } else if ( incremental_flag == 1 && this.d_data.dir_file_name != this.lastValue ) {
                            this.renameDir();
                        }
                    }
                } else { // check for company
                    incremental_flag = 0;
                    if ( this.d_data.dir_file_name == '' || this.d_data.dir_file_name == null ) {
                        this.d_data.dir_file_name = this.lastValue;
                        company_dir_file_system.isError = true;
                        company_dir_file_system.isVisible = true;
                        company_dir_file_system.response_message = 'You cannot give an empty name! Please enter a name!';
                        setTimeout( function () {
                            company_dir_file_system.isVisible = false;
                        }, 3000 );
                    } else {
                        for ( var key in company_dir_file_system.dir_data ) {
                            dir_file_name_array.push( company_dir_file_system.dir_data[ key ].dir_file_name );
                        }
                        for ( var key in company_dir_file_system.file_data ) {
                            dir_file_name_array.push( company_dir_file_system.file_data[ key ].dir_file_name );
                        }
                        for ( var key in dir_file_name_array ) {
                            if ( dir_file_name_array[ key ] == this.d_data.dir_file_name ) {
                                incremental_flag++;
                            }
                        }
                        if ( incremental_flag > 1 ) { // name already exist
                            this.d_data.dir_file_name = this.lastValue;
                            company_dir_file_system.isError = true;
                            company_dir_file_system.isVisible = true;
                            company_dir_file_system.response_message = 'Given name already exist! Please choose another name!';
                            setTimeout( function () {
                                company_dir_file_system.isVisible = false;
                            }, 3000 );
                        } else if ( incremental_flag == 1 && this.d_data.dir_file_name != this.lastValue ) {
                            this.renameDir();
                        }
                    }
                }

                this.inEditMode = false;
            },
            renameDir: function () {
                if ( this.d_data.is_dir == "1" ) { // operation for folder
                    if ( this.d_data.eid != "0" ) { // operation for employee
                        dir_file_system.renameNow( 'folder', this.d_data.dir_file_name, this.listcurrentindex );
                    } else { // operation for company
                        company_dir_file_system.renameNow( 'folder', this.d_data.dir_file_name, this.listcurrentindex );
                    }
                } else { // operation for file
                    if ( this.d_data.eid != "0" ) { // operation for employee
                        dir_file_system.renameNow( 'file', this.d_data.dir_file_name, this.listcurrentindex );
                    } else { // operation for company
                        company_dir_file_system.renameNow( 'file', this.d_data.dir_file_name, this.listcurrentindex );
                    }
                }
                this.inEditMode = false;
            },
            dirLinkClicked: function ( eid, dir_id, dir_file_name ) {
                if ( eid != "0" ) {
                    dir_file_system.dir_link_clicked( dir_id, dir_file_name );
                } else {
                    company_dir_file_system.dir_link_clicked( dir_id, dir_file_name );
                }
            },
            checkDuplicateDirFileName: function ( iArray ) {
                var dresult = [];
                iArray.forEach( function( element, index ) {
                    // Find if there is a duplicate or not
                    if ( iArray.indexOf( element, index + 1 ) > -1 ) {
                        // Find if the element is already in the result array or not
                        if ( dresult.indexOf(element) === -1 ) {
                            dresult.push(element);
                        }
                    }
                });
                this.getDuplicateCounter = dresult.length;
            }
        }
    } );
    Vue.component( 'rename-component', renameComponent );

    if ( $( '#file_folder_wrapper' ).length > 0 ) {
        window.dir_file_system = new Vue( {
            el: '#file_folder_wrapper',

            data: {
                employee_id: 0,
                dir_data: [],
                file_data: [],
                clicked_link_dir_id: 0,
                breadcurm_data: [ {
                    indexx: 0,
                    text: "Home"
                } ],
                current_dir_id: 0,
                dir_file_checkboxx: [],
                toggleSelection: true,
                checkall: false,
                isDeleteMoveButtonShow: false,
                searchInput: '',
                success_notice_class: 'success_notice',
                error_notice_class: 'error_notice',
                isError: false,
                isVisible: false,
                response_message: '',
                isEditableDoc: false,
                ownerId: wpErpDoc.current_user_id,
                admin: wpErpDoc.isAdmin,
                crm: wpErpDoc.isCrmManager,
            },

            ready: function () {
                jQuery( document ).ready( function () {
                    jQuery( '.not-loaded' ).removeClass( 'not-loaded' );
                } );
                this.getApplicationDirFileSystem();
            },

            watch: {
                dir_file_checkboxx: function () {
                    this.isDeleteMoveButtonShow = ( this.dir_file_checkboxx.length > 0 ) ? true : false;
                }
            },

            computed: {
                marginLeft: function ( user_id ) {
                    if ( this.checkEditableDoc( user_id ) ) {
                        return {
                            'margin-left': '10px',
                        }
                    }
                }
            },

            methods: {

                getApplicationDirFileSystem: function () {
                    this.employee_id = this.getParameterByName( 'id' );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {action: 'wp-erp-rec-get-dir-info', employee_id: this.employee_id, dir_id: 0}, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.dir_data = response.data;
                                dir_file_system.current_dir_id = 0;
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {action: 'wp-erp-rec-get-file-info', employee_id: this.employee_id, dir_id: 0}, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    document.getElementById( 'checkall' ).checked = false;

                },

                getParameterByName: function ( name ) {
                    name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                    var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                        results = regex.exec( location.search );
                    return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                },

                dir_link_clicked: function ( click_dir_id, click_dir_name ) {
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-dir-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.dir_data = response.data;
                                dir_file_system.current_dir_id = click_dir_id;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-file-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    // set clicked link dir id
                    dir_file_system.clicked_link_dir_id = click_dir_id;
                    dir_file_system.breadcurm_data.push(
                        {indexx: click_dir_id, text: click_dir_name}
                    );
                    dir_file_system.dir_file_checkboxx = [];
                    dir_file_system.toggleSelection = true;
                    document.getElementById( 'checkall' ).checked = false;
                    //jQuery('#file_folder_list').hide();
                },

                bdir_link_clicked: function ( click_dir_id, list_index, event ) { //breadcrum link click
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-dir-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.dir_data = response.data;
                                dir_file_system.current_dir_id = click_dir_id;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-file-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    dir_file_system.breadcurm_data.splice( list_index + 1 );
                    dir_file_system.dir_file_checkboxx = [];
                    dir_file_system.toggleSelection = true;
                    document.getElementById( 'checkall' ).checked = false;

                },

                getPrompt: function () {
                    var folderName = prompt( 'Please enter your folder name', '' );
                    if ( folderName != null ) {
                        var current_dir_id = dir_file_system.current_dir_id;
                        dir_file_system.employee_id = this.employee_id;
                        jQuery.get( wpErpDoc.ajaxurl,
                            {
                                action: 'wp-erp-rec-createDir',
                                employee_id: this.employee_id,
                                parent_id: current_dir_id,
                                dirName: folderName
                            }, function ( response ) {
                                if ( response.success === true ) {
                                    jQuery.get( wpErpDoc.ajaxurl,
                                        {
                                            action: 'wp-erp-rec-get-dir-info',
                                            employee_id: dir_file_system.employee_id,
                                            dir_id: current_dir_id
                                        }, function ( response ) {
                                            if ( response.success === true ) {
                                                dir_file_system.dir_data = response.data;
                                                dir_file_system.current_dir_id = current_dir_id;
                                            }
                                        }
                                    );

                                    jQuery.get( wpErpDoc.ajaxurl,
                                        {
                                            action: 'wp-erp-rec-get-file-info',
                                            employee_id: dir_file_system.employee_id,
                                            dir_id: current_dir_id
                                        }, function ( response ) {
                                            if ( response.success === true ) {
                                                dir_file_system.file_data = response.data;
                                            }
                                        }
                                    );
                                    dir_file_system.isError = false;
                                    dir_file_system.isVisible = true;
                                    dir_file_system.response_message = response.data;
                                    setTimeout( function () {
                                        dir_file_system.isVisible = false;
                                    }, 3000 );
                                } else {
                                    dir_file_system.isError = true;
                                    dir_file_system.isVisible = true;
                                    dir_file_system.response_message = response.data;
                                    setTimeout( function () {
                                        dir_file_system.isVisible = false;
                                    }, 3000 );
                                }
                            }
                        );
                    }
                },

                selectAll: function () {
                    document.getElementsByClassName( 'dir_file_chkbox' ).checked = true;
                    if ( this.toggleSelection ) {
                        this.dir_file_checkboxx = [];

                        for ( dd in dir_file_system.dir_data ) {
                            this.dir_file_checkboxx.push( this.dir_data[ dd ].dir_id );
                        }

                        for ( fd in dir_file_system.file_data ) {
                            this.dir_file_checkboxx.push( this.file_data[ fd ].dir_id );
                        }

                        this.toggleSelection = false;
                    } else {
                        this.dir_file_checkboxx = [];
                        this.toggleSelection = true;
                    }
                },

                deleteSelectedDirFile: function () {
                    if ( confirm( 'Are you sure you want to delete?' ) ) {
                        if ( dir_file_system.dir_file_checkboxx.length > 0 ) {
                            var current_dir_id = dir_file_system.current_dir_id;
                            jQuery.post( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-rec-deleteDirFile',
                                    parent_id: current_dir_id,
                                    employee_id: dir_file_system.employee_id,
                                    selected_dir_file_id: JSON.stringify( dir_file_system.dir_file_checkboxx )
                                }, function ( response ) {
                                    if ( response.success === true ) {

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.dir_data = response.data;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );
                                        dir_file_system.dir_file_checkboxx = [];
                                        dir_file_system.isError = false;
                                        dir_file_system.isVisible = true;
                                        dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            dir_file_system.isVisible = false;
                                        }, 3000 );
                                        document.getElementById( 'checkall' ).checked = false;
                                    }
                                }
                            );
                        } else {
                            dir_file_system.isError = true;
                            dir_file_system.isVisible = true;
                            dir_file_system.response_message = 'Please select directory or file to delete';
                            setTimeout( function () {
                                dir_file_system.isVisible = false;
                            }, 3000 );
                        }
                    }
                },

                renameNow: function ( type, dir_name, $index ) {
                    if ( $index != undefined ) {
                        var dir_file_title = '';
                        var dir_file_id = 0;
                        if ( type == 'folder' ) {
                            dir_file_title = dir_file_system.dir_data[ $index ].dir_file_name;
                            dir_file_id = dir_file_system.dir_data[ $index ].dir_id;
                        } else {
                            dir_file_title = dir_file_system.file_data[ $index ].dir_file_name;
                            dir_file_id = dir_file_system.file_data[ $index ].dir_id;
                        }

                        //var folderName = prompt( 'Rename now', dir_file_title );
                        var folderName = dir_name;
                        if ( folderName != null ) {
                            var current_dir_id = dir_file_system.current_dir_id;
                            dir_file_system.employee_id = this.employee_id;
                            jQuery.get( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-rec-renameDirFile',
                                    dir_file_type: type,
                                    employee_id: this.employee_id,
                                    parent_id: current_dir_id,
                                    target_dir_id: dir_file_id,
                                    dirName: folderName
                                }, function ( response ) {
                                    if ( response.success === true ) {
                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.dir_data = response.data;
                                                    dir_file_system.current_dir_id = current_dir_id;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );
                                        dir_file_system.isError = false;
                                        dir_file_system.isVisible = true;
                                        dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            dir_file_system.isVisible = false;
                                        }, 3000 );
                                    } else {
                                        dir_file_system.isError = true;
                                        dir_file_system.isVisible = true;
                                        dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                }
                            );
                        }
                    }
                },

                moveSelectedDirFile: function () {
                    var current_dir_id = dir_file_system.current_dir_id;
                    var new_parent_id = 0;

                    jQuery.erpPopup( {
                        title: wpErpDoc.moveto,
                        button: wpErpDoc.move,
                        id: 'wp-erp-hr-document',
                        content: wp.template( 'erp-doc-tree-template' )().trim(),
                        extraClass: 'medium',
                        onReady: function () {
                            var modal = this;
                            // get employee id from url
                            var employee_id = getParameterByName( 'id' );
                            // get new parent id
                            new_parent_id = 0;

                            jQuery.get( wpErpDoc.ajaxurl + '?action=wp-erp-doc-loadParentNodes&employee_id=' + employee_id, function ( response ) {
                                var item = JSON.parse( response );

                                generateList( item.children, jQuery( '.root-main-thing' ) );

                                function generateList( data, $e ) {
                                    // create an inner item
                                    function createInner( obj, $target ) {
                                        var li = jQuery( '<li>' ).appendTo( $target );
                                        li.html( '<input class="itid" type="checkbox" id="' + obj.id + '" /><label class="tid">' + obj.text + '</label>' );
                                        if ( obj.children != undefined && obj.children.length > 0 ) {
                                            var innerList = jQuery( '<ul>' ).appendTo( li );
                                            for ( var i = 0; i < obj.children.length; i++ ) {
                                                var child = obj.children[ i ];
                                                createInner( child, innerList );
                                            }
                                        }
                                    }

                                    for ( var i = 0; i < data.length; i++ ) {
                                        createInner( data[ i ], $e );
                                    }
                                }
                            } );

                            // jquery to get id from tree view
                            jQuery( 'body' ).on( 'click', '.tid', function () {
                                new_parent_id = jQuery( this ).prev().attr( 'id' );
                                jQuery( '.tid' ).css( {'border': '0', 'background-color': '#fff'} );
                                jQuery( this ).css( {'border': '1px dotted #CBECF7', 'background-color': '#A6E1F5'} );
                            } );

                            function getParameterByName( name ) {
                                name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                                var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                                    results = regex.exec( location.search );
                                return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                            }
                        },
                        onSubmit: function ( modal ) {
                            jQuery.post( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-doc-moveNow',
                                    employee_id: dir_file_system.employee_id,
                                    parent_id: current_dir_id,
                                    new_parent_id: new_parent_id,
                                    selectedDirFile: JSON.stringify( dir_file_system.dir_file_checkboxx )
                                }, function ( response ) {
                                    if ( response.success === true ) {
                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.dir_data = response.data;
                                                    dir_file_system.current_dir_id = current_dir_id;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );

                                        dir_file_system.isError = false;
                                        dir_file_system.isVisible = true;
                                        dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                    else {
                                        dir_file_system.isError = true;
                                        dir_file_system.isVisible = true;
                                        dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                    modal.closeModal();
                                } );
                        }
                    } );
                },

                searchNow: function () {
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-search-dir-info',
                            employee_id: this.employee_id,
                            skey: this.searchInput
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.dir_data = response.data;
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-search-file-info',
                            employee_id: this.employee_id,
                            skey: this.searchInput
                        }, function ( response ) {
                            if ( response.success === true ) {
                                dir_file_system.file_data = response.data;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );
                },

                checkEditableDoc: function ( user_id ) {
                    if ( wpErpDoc.isAdmin || wpErpDoc.isCrmManager || this.checkoOwnDoc( user_id ) ) {
                        return true;
                    }

                    return false;
                },

                checkoOwnDoc: function ( user_id ) {
                    return ( wpErpDoc.isAgent ) && user_id == wpErpDoc.current_user_id;
                },

                crmDocselectAll: function () {
                    document.getElementsByClassName( 'dir_file_chkbox' ).checked = true;
                    if ( this.toggleSelection ) {
                        this.dir_file_checkboxx = [];

                        for ( dd in dir_file_system.dir_data ) {
                            if ( this.checkEditableDoc( this.dir_data[ dd ].user_id ) ) {
                                this.dir_file_checkboxx.push( this.dir_data[ dd ].dir_id );
                            }
                        }

                        for ( fd in dir_file_system.file_data ) {
                            if ( this.checkEditableDoc( this.dir_data[ dd ].user_id ) ) {
                                this.dir_file_checkboxx.push( this.file_data[ dd ].dir_id );
                            }
                        }

                        this.toggleSelection = false;
                    } else {
                        this.dir_file_checkboxx = [];
                        this.toggleSelection = true;
                    }
                },
            }
        } );
    }

    if ($( '#company_file_folder_wrapper' ).length > 0 ) {
        window.company_dir_file_system = new Vue( {
            el: '#company_file_folder_wrapper',

            data: {
                employee_id: 0,
                dir_data: [],
                file_data: [],
                clicked_link_dir_id: 0,
                breadcurm_data: [ {
                    indexx: 0,
                    text: 'Home'
                } ],
                current_dir_id: 0,
                dir_file_checkboxx: [],
                toggleSelection: true,
                checkall: false,
                isDeleteMoveButtonShow: false,
                searchInput: '',
                success_notice_class: 'success_notice',
                error_notice_class: 'error_notice',
                isError: false,
                isVisible: false,
                response_message: ''
            },

            ready: function () {
                jQuery( document ).ready( function () {
                    jQuery( '.not-loaded' ).removeClass( 'not-loaded' );
                } );

                this.getApplicationDirFileSystem();
            },

            watch: {
                dir_file_checkboxx: function () {
                    this.isDeleteMoveButtonShow = ( this.dir_file_checkboxx.length > 0 ) ? true : false;
                }
            },

            methods: {

                getApplicationDirFileSystem: function () {
                    this.employee_id = this.getParameterByName( 'id' );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {action: 'wp-erp-rec-get-dir-info', employee_id: this.employee_id, dir_id: 0}, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.dir_data = response.data;
                                company_dir_file_system.current_dir_id = 0;
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {action: 'wp-erp-rec-get-file-info', employee_id: this.employee_id, dir_id: 0}, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    document.getElementById( 'checkall' ).checked = false;

                },

                getParameterByName: function ( name ) {
                    name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                    var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                        results = regex.exec( location.search );
                    return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                },

                dir_link_clicked: function ( click_dir_id, click_dir_name ) {
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-dir-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.dir_data = response.data;
                                company_dir_file_system.current_dir_id = click_dir_id;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-file-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    // set clicked link dir id
                    company_dir_file_system.clicked_link_dir_id = click_dir_id;
                    company_dir_file_system.breadcurm_data.push(
                        {indexx: click_dir_id, text: click_dir_name}
                    );
                    company_dir_file_system.dir_file_checkboxx = [];
                    company_dir_file_system.toggleSelection = true;
                    document.getElementById( 'checkall' ).checked = false;
                    //jQuery('#file_folder_list').hide();
                },

                bdir_link_clicked: function ( click_dir_id, list_index, event ) {
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-dir-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.dir_data = response.data;
                                company_dir_file_system.current_dir_id = click_dir_id;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-get-file-info',
                            employee_id: this.employee_id,
                            dir_id: click_dir_id
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.file_data = response.data;
                            }
                        }
                    );
                    company_dir_file_system.breadcurm_data.splice( list_index + 1 );
                    company_dir_file_system.dir_file_checkboxx = [];
                    company_dir_file_system.toggleSelection = true;
                    document.getElementById( 'checkall' ).checked = false;

                },

                getPrompt: function () {
                    var folderName = prompt( 'Please enter your folder name', '' );
                    if ( folderName != null ) {
                        var current_dir_id = company_dir_file_system.current_dir_id;
                        company_dir_file_system.employee_id = this.employee_id;
                        jQuery.get( wpErpDoc.ajaxurl,
                            {
                                action: 'wp-erp-rec-createDir',
                                employee_id: this.employee_id,
                                parent_id: current_dir_id,
                                dirName: folderName
                            }, function ( response ) {
                                if ( response.success === true ) {
                                    jQuery.get( wpErpDoc.ajaxurl,
                                        {
                                            action: 'wp-erp-rec-get-dir-info',
                                            employee_id: company_dir_file_system.employee_id,
                                            dir_id: current_dir_id
                                        }, function ( response ) {
                                            if ( response.success === true ) {
                                                company_dir_file_system.dir_data = response.data;
                                                company_dir_file_system.current_dir_id = current_dir_id;
                                            }
                                        }
                                    );

                                    jQuery.get( wpErpDoc.ajaxurl,
                                        {
                                            action: 'wp-erp-rec-get-file-info',
                                            employee_id: company_dir_file_system.employee_id,
                                            dir_id: current_dir_id
                                        }, function ( response ) {
                                            if ( response.success === true ) {
                                                company_dir_file_system.file_data = response.data;
                                            }
                                        }
                                    );
                                    company_dir_file_system.isError = false;
                                    company_dir_file_system.isVisible = true;
                                    company_dir_file_system.response_message = response.data;
                                    setTimeout( function () {
                                        company_dir_file_system.isVisible = false;
                                    }, 3000 );
                                } else {
                                    company_dir_file_system.isError = true;
                                    company_dir_file_system.isVisible = true;
                                    company_dir_file_system.response_message = response.data;
                                    setTimeout( function () {
                                        company_dir_file_system.isVisible = false;
                                    }, 3000 );
                                }
                            }
                        );
                    }
                },

                selectAll: function () {
                    document.getElementsByClassName( 'dir_file_chkbox' ).checked = true;
                    if ( this.toggleSelection ) {
                        this.dir_file_checkboxx = [];
                        for ( dd in company_dir_file_system.dir_data ) {
                            this.dir_file_checkboxx.push( this.dir_data[ dd ].dir_id );
                        }
                        for ( fd in company_dir_file_system.file_data ) {
                            this.dir_file_checkboxx.push( this.file_data[ fd ].dir_id );
                        }
                        this.toggleSelection = false;
                    } else {
                        this.dir_file_checkboxx = [];
                        this.toggleSelection = true;
                    }



                },

                deleteSelectedDirFile: function () {
                    if ( confirm( 'Are you sure you want to delete?' ) ) {
                        if ( company_dir_file_system.dir_file_checkboxx.length > 0 ) {
                            var current_dir_id = company_dir_file_system.current_dir_id;
                            jQuery.post( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-rec-deleteDirFile',
                                    parent_id: current_dir_id,
                                    employee_id: company_dir_file_system.employee_id,
                                    selected_dir_file_id: JSON.stringify( company_dir_file_system.dir_file_checkboxx )
                                }, function ( response ) {
                                    if ( response.success === true ) {

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.dir_data = response.data;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );
                                        company_dir_file_system.dir_file_checkboxx = [];
                                        company_dir_file_system.isError = false;
                                        company_dir_file_system.isVisible = true;
                                        company_dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            company_dir_file_system.isVisible = false;
                                        }, 3000 );
                                        document.getElementById( 'checkall' ).checked = false;
                                    }
                                }
                            );
                        } else {
                            company_dir_file_system.isError = true;
                            company_dir_file_system.isVisible = true;
                            company_dir_file_system.response_message = 'Please select directory or file to delete';
                            setTimeout( function () {
                                company_dir_file_system.isVisible = false;
                            }, 3000 );
                        }
                    }
                },

                renameNow: function ( type, dir_name, $index ) {
                    if ( $index != undefined ) {
                        var dir_file_title = '';
                        var dir_file_id = 0;
                        if ( type == 'folder' ) {
                            dir_file_title = company_dir_file_system.dir_data[ $index ].dir_file_name;
                            dir_file_id = company_dir_file_system.dir_data[ $index ].dir_id;
                        } else {
                            dir_file_title = company_dir_file_system.file_data[ $index ].dir_file_name;
                            dir_file_id = company_dir_file_system.file_data[ $index ].dir_id;
                        }

                        //var folderName = prompt( 'Rename now', dir_file_title );
                        var folderName = dir_name;
                        if ( folderName != null ) {
                            var current_dir_id = company_dir_file_system.current_dir_id;
                            company_dir_file_system.employee_id = this.employee_id;
                            jQuery.get( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-rec-renameDirFile',
                                    dir_file_type: type,
                                    employee_id: this.employee_id,
                                    parent_id: current_dir_id,
                                    target_dir_id: dir_file_id,
                                    dirName: folderName
                                }, function ( response ) {
                                    if ( response.success === true ) {
                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.dir_data = response.data;
                                                    company_dir_file_system.current_dir_id = current_dir_id;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );
                                        company_dir_file_system.isError = false;
                                        company_dir_file_system.isVisible = true;
                                        company_dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            company_dir_file_system.isVisible = false;
                                        }, 3000 );
                                    } else {
                                        company_dir_file_system.isError = true;
                                        company_dir_file_system.isVisible = true;
                                        company_dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            company_dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                }
                            );
                        }
                    }
                },

                moveSelectedDirFile: function () {
                    this.employee_id = this.getParameterByName( 'id' );
                    var current_dir_id = company_dir_file_system.current_dir_id;
                    var new_parent_id = 0;

                    jQuery.erpPopup( {
                        title: wpErpDoc.moveto,
                        button: wpErpDoc.move,
                        id: 'wp-erp-hr-document',
                        content: wp.template( 'erp-doc-tree-template' )().trim(),
                        extraClass: 'medium',
                        onReady: function () {
                            var modal = this;

                            // get employee id from url
                            var employee_id = getParameterByName( 'id' );
                            // get new parent id
                            new_parent_id = 0;

                            jQuery.get( wpErpDoc.ajaxurl + '?action=wp-erp-doc-loadParentNodes&employee_id=' + employee_id, function ( response ) {
                                var item = JSON.parse( response );

                                generateList( item.children, jQuery( '.root-main-thing' ) );

                                function generateList( data, $e ) {
                                    // create an inner item
                                    function createInner( obj, $target ) {
                                        var li = jQuery( '<li>' ).appendTo( $target );
                                        li.html( '<input class="itid" type="checkbox" id="' + obj.id + '" /><label class="tid">' + obj.text + '</label>' );
                                        if ( obj.children != undefined && obj.children.length > 0 ) {
                                            var innerList = jQuery( '<ul>' ).appendTo( li );
                                            for ( var i = 0; i < obj.children.length; i++ ) {
                                                var child = obj.children[ i ];
                                                createInner( child, innerList );
                                            }
                                        }
                                    }

                                    for ( var i = 0; i < data.length; i++ ) {
                                        createInner( data[ i ], $e );
                                    }
                                }
                            } );

                            // jquery to get id from tree view
                            jQuery( 'body' ).on( 'click', '.tid', function () {
                                new_parent_id = jQuery( this ).prev().attr( 'id' );
                                jQuery( '.tid' ).css( {'border': '0', 'background-color': '#fff'} );
                                jQuery( this ).css( {'border': '1px dotted #CBECF7', 'background-color': '#A6E1F5'} );
                            } );

                            function getParameterByName( name ) {
                                name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                                var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                                    results = regex.exec( location.search );
                                return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                            }
                        },
                        onSubmit: function ( modal ) {
                            jQuery.post( wpErpDoc.ajaxurl,
                                {
                                    action: 'wp-erp-doc-moveNow',
                                    employee_id: company_dir_file_system.employee_id,
                                    parent_id: current_dir_id,
                                    new_parent_id: new_parent_id,
                                    selectedDirFile: JSON.stringify( company_dir_file_system.dir_file_checkboxx )
                                }, function ( response ) {
                                    if ( response.success === true ) {
                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-dir-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.dir_data = response.data;
                                                    company_dir_file_system.current_dir_id = current_dir_id;
                                                }
                                            }
                                        );

                                        jQuery.get( wpErpDoc.ajaxurl,
                                            {
                                                action: 'wp-erp-rec-get-file-info',
                                                employee_id: company_dir_file_system.employee_id,
                                                dir_id: current_dir_id
                                            }, function ( response ) {
                                                if ( response.success === true ) {
                                                    company_dir_file_system.file_data = response.data;
                                                }
                                            }
                                        );

                                        company_dir_file_system.isError = false;
                                        company_dir_file_system.isVisible = true;
                                        company_dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            company_dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                    else {
                                        company_dir_file_system.isError = true;
                                        company_dir_file_system.isVisible = true;
                                        company_dir_file_system.response_message = response.data;
                                        setTimeout( function () {
                                            company_dir_file_system.isVisible = false;
                                        }, 3000 );
                                    }
                                    modal.closeModal();
                                } );
                        }
                    } );
                },

                searchNow: function () {
                    jQuery( '#loader_wrapper' ).show();
                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-search-dir-info',
                            employee_id: this.employee_id,
                            skey: this.searchInput
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.dir_data = response.data;
                            }
                        }
                    );

                    jQuery.get( wpErpDoc.ajaxurl,
                        {
                            action: 'wp-erp-rec-search-file-info',
                            employee_id: this.employee_id,
                            skey: this.searchInput
                        }, function ( response ) {
                            if ( response.success === true ) {
                                company_dir_file_system.file_data = response.data;
                                jQuery( '#loader_wrapper' ).hide();
                            }
                        }
                    );
                }
            }
        } );
    }
})( jQuery );