;(function() {
    'use strict';

    Vue.component( 'email-campaign-component', {
        props: [ 'i18n', 'feed' ],

        data: function() {
            return {
                headerText: '',
            };
        },

        template: '#erp-crm-timeline-email-campaign',

        computed: {
            createdForUser: function() {
                return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
            },

            isActivityPage: function() {
                return ( window.wpCRMvue.isActivityPage === undefined ) ? false : true;
            },
        }
    });
})();
