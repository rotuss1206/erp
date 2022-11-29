;(function($) {
    'use strict';

    /**
     * Subscriber Stats in single campaign page
     */
    Vue.config.debug = ecampGlobal.debug;

    if ( $( '#ecamp-after-bounce-settings' ).length > 0 ) {
        new Vue({
            el: '#ecamp-after-bounce-settings',
            data: {
                actions: ecampGlobal.afterBounceActions,
                selectedAction: ecampGlobal.selectedAction,
                groups: ecampGlobal.contact_groups,
                selectedList: parseInt(ecampGlobal.selectedList),
                i18n: ecampGlobal.i18n,
            },

            ready: function () {
                var vm = this;
                $('#mainform').on('submit', function (e) {
                    if ('unsub_add_to_list' === vm.selectedAction && !parseInt(vm.selectedList)) {
                        e.preventDefault();

                        swal({
                            title: '',
                            text: ecampGlobal.i18n.mustSelectAGroup,
                            type: 'error',
                        });
                    }
                });
            }
        });
    }

    if ( $( '[data-settings-id="ecamp-managerial-roles-settings"]' ).length > 0 ) {
        $('[data-settings-id="ecamp-managerial-roles-settings"]')
            .removeClass('select2-is-loading')
            .select2()
            .on('select2:unselecting', function (e) {
                if ('administrator' === e.params.args.data.id || 'erp_crm_manager' === e.params.args.data.id) {
                    e.preventDefault();
                }
            });
    }
})(jQuery);
