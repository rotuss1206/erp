;(function($) {
    'use strict';

    /**
     * Sometimes we must set the parent close to interaction
     * point. In that case we'll choose another parent and after
     * start we'll call this function to set back the default parent
     */
    window.setDefaultNProgressParent = function() {
        NProgress.configure({parent: '#wpadminbar'});
    };
    window.setDefaultNProgressParent();

    $(document).on('click', '.pause-this-campaign', function (e) {
        e.preventDefault();

        var anch = $(this),
            tr = anch.parents('tr'),
            top = tr.position().top,
            height = tr.height(),
            tableLoader = $('#list-table-loader');

        tableLoader.addClass('loading').css({
            top: top, height: height
        });

        $.ajax({
            url: ecampGlobal.ajaxurl,
            method: 'get',
            dataType: 'json',
            data: {
                action: 'pause_campaign',
                _wpnonce: ecampGlobal.nonce,
                campaign_id: anch.get(0).dataset.campaign,
                title: tr.find('.campaign-link-name').text(),
            },
            beforeSend: function () {
                NProgress.configure({parent: '#list-table-loader'});
                NProgress.start();
            }

        }).done(function (response) {
            if ( NProgress.done() ) {
                setTimeout(function () {
                    tableLoader.removeClass('loading');
                }, 500);
            }
            window.setDefaultNProgressParent();

            if (response.success) {
                anch.replaceWith(response.data.resume);
                tr.find('.campaign-link-view').replaceWith(response.data.edit);
                tr.find('.campaign-link-name').replaceWith(response.data.name);
                tr.find('.list-table-status.' + response.data.replace).replaceWith(response.data.status);
                tr.removeClass(response.data.replace).addClass(response.data.replaceWith);
                tr.find('.schedule-label').remove();
            }
        });

    });

    $(document).on('click', '.resume-this-campaign', function (e) {
        e.preventDefault();

        var anch = $(this),
            tr = anch.parents('tr'),
            top = tr.position().top,
            height = tr.height(),
            tableLoader = $('#list-table-loader');

        tableLoader.addClass('loading').css({
            top: top, height: height
        });

        $.ajax({
            url: ecampGlobal.ajaxurl,
            method: 'get',
            dataType: 'json',
            data: {
                action: 'resume_campaign',
                _wpnonce: ecampGlobal.nonce,
                campaign_id: anch.get(0).dataset.campaign,
                title: tr.find('.campaign-link-name').text(),
            },
            beforeSend: function () {
                NProgress.configure({parent: '#list-table-loader'});
                NProgress.start();
            }

        }).done(function (response) {
            if ( NProgress.done() ) {
                setTimeout(function () {
                    tableLoader.removeClass('loading');
                }, 500);
            }
            window.setDefaultNProgressParent();

            if (response.success) {
                anch.replaceWith(response.data.pause);
                tr.find('.campaign-link-edit').replaceWith(response.data.view);
                tr.find('.campaign-link-name').replaceWith(response.data.name);
                tr.find('.list-table-status.' + response.data.replace).replaceWith(response.data.status);
                tr.removeClass(response.data.replace).addClass(response.data.replaceWith);
            }
        });

    });

    $('.duplicate-campaign').on('click', function (e) {
        e.preventDefault();
        var campaign_id = $(this).data('campaign');

        swal({
            title: '',
            text: ecampGlobal.i18n.confirmDuplicate,
            type: 'info',
            showCancelButton: true,
            closeOnConfirm: false,
            showLoaderOnConfirm: true,

        }, function(isConfirm){
            if (isConfirm) {
                $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'post',
                    dataType: 'json',
                    data: {
                        action: 'duplicate_campaign',
                        _wpnonce: ecampGlobal.nonce,
                        campaign_id: campaign_id
                    },

                }).done(function (response) {
                    swal({
                        title: '',
                        text: response.data,
                        type: response.success? 'success' : 'error',
                        html: true
                    });
                });
            }
        });
    });


    /**
     * Subscriber Stats in single campaign page
     */
    Vue.config.debug = ecampGlobal.debug;

    if ($('#campaign-people-stats').length > 0) {
        new Vue({
            el: '#campaign-people-stats',
            data : {
                wpnonce: ecampGlobal.nonce,
                fields: [
                    {
                        name: 'name',
                        title: ecampGlobal.i18n.name,
                        callback: 'column_name'
                    },
                    {
                        name: 'email-status',
                        title: ecampGlobal.i18n.email,
                    },
                    {
                        name: 'lists',
                        title: ecampGlobal.i18n.lists,
                    },
                    {
                        name: 'subs_status',
                        title: ecampGlobal.i18n.subs_status,
                    },
                    {
                        name: 'opened',
                        title: ecampGlobal.i18n.opened,
                    }
                ],
                search: {
                    params: 's',
                    wrapperClass: '',
                    screenReaderText: ecampGlobal.searchPlaceHolder,
                    inputId: 'search-input',
                    btnId: 'search-submit',
                    placeholder: ecampGlobal.searchPlaceHolder
                },
                topNavFilter: {
                    data: ecampGlobal.topNavFilter,
                    default: 'all',
                    field: 'email-status'
                },
                groupFilter: {
                    group : {
                        name: 'group',
                        type: 'select',
                        options: ecampGlobal.groupFilter
                    }
                },
                hideCb: true,
                subscriberDetails: {},
                subscriberDetailsIsFetching: true,
                subsciberActivities: {},
                subscriberInfo: {}
            },

            computed: {
                timeLineItems: function () {
                    var timelineItems = [];

                    // format data into an array
                    $.each(this.subsciberActivities, function (key, activities) {
                        switch(key) {
                            case 'sent':
                                timelineItems.push({
                                    type: 'sent',
                                    time: activities,
                                    timestamp: moment(activities).unix()
                                });
                                break;

                            case 'open':
                                activities.forEach(function (activity) {
                                    timelineItems.push({
                                        type: 'open',
                                        time: activity.opened_at,
                                        timestamp: moment(activity.opened_at).unix()
                                    });
                                });

                                break;

                            case 'url':
                                activities.forEach(function (activity) {
                                    timelineItems.push({
                                        type: 'url',
                                        url: activity.url,
                                        time: activity.clicked_at,
                                        timestamp: moment(activity.clicked_at).unix()
                                    });
                                });
                                break;
                        }
                    });

                    // sort with respect to time
                    return timelineItems.sort(function(a,b){
                      return b.timestamp - a.timestamp;
                    });
                }
            },

            methods: {
                column_name: function (value, item) {
                    var link  = '<a href="' + item.contactProfile +
                            '" data-subscriber-details="' + item.id + '"><strong>';

                    var name = '';

                    if (item.company) {
                        name = item.company;
                    } else {
                        name = [ item.first_name, item.last_name ].join(' ').trim();
                    }

                    if ( name ) {
                        link += name + '</strong></a></br>' + item.email;

                    } else {
                        link += item.email + '</strong></a>';
                    }

                    return item.avatar + link;
                },

                afterFetchData: function () {
                    var self = this;

                    Vue.nextTick(function () {
                        $('[data-subscriber-details]', document).on('click', function (e) {
                            e.preventDefault();
                            self.openSubscriberModal(this.dataset.subscriberDetails);
                        });
                    });
                },

                openSubscriberModal: function (subsciberId) {
                    var self = this;

                    if (!this.subscriberDetails[subsciberId]) {
                        this.subscriberDetailsIsFetching = true;
                        this.subsciberActivities = {};
                    } else {
                        this.subscriberDetailsIsFetching = false;
                        this.subsciberActivities = this.subscriberDetails[subsciberId];
                    }

                    $('#erp-email-campaign-subscriber-details').erpCampaignModal();

                    $.ajax({
                        url: ecampGlobal.ajaxurl,
                        method: 'get',
                        dataType: 'json',
                        data: {
                            action: 'get_subscriber_campaign_activities',
                            _wpnonce: ecampGlobal.nonce,
                            campaign_id: ecampGlobal.campaignId,
                            subscriber_id: subsciberId
                        }

                    }).done(function (response) {
                        if (response.success) {
                            self.subscriberDetails[subsciberId] = response.data.activities;
                            self.subsciberActivities = response.data.activities;
                            self.subscriberInfo = response.data.info;
                        }

                    }).always(function () {
                        self.subscriberDetailsIsFetching = false;
                    });
                },

                getTimeLineDateTime: function (dateTime) {
                    return moment(dateTime).format('ddd MMM DD YYYY, hh:mm A');
                }
            }
        });

        // flot chart for email stats
        if (window.campaignEmailStats && window.campaignEmailStats.length) {

            $.plot('#ecmap-single-email-stats', window.campaignEmailStats, {
                series: {
                    pie: {
                        show: true,
                        innerRadius: 0.3,
                        label: {
                            show: true,
                            radius: 1/2,
                            formatter: function (label, series) {
                                return '<div style="font-size:8pt; text-align:center; padding:2px; color:white;"">' + Math.round(series.percent) + '%</div>';
                            },
                            threshold: 0.05
                        },
                    }
                },
                legend: {
                    show: true
                }
            });

        }

    }

    if ($('[data-tiptip]').length) {
        var tiptipSettings = {
            defaultPosition: 'top',
        };

        $('[data-tiptip]').tipTip(tiptipSettings);
    }
})(jQuery);
