<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

/**
 * Campaign List table class
 */
class Campaign_Editor {

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Editor primary data for Vue instance
     *
     * @param integer $id when creating a new campaign, $id = 0
     *
     * @return array
     */
    public function get_editor_data( $id = 0 ) {
        $campaign = erp_email_campaign()->get_campaign( $id );

        if ( empty( $campaign ) ) {
            $erp_options = get_option( 'erp_settings_erp-email_general', [] );

            $campaign = [
                'email_subject'     => null,
                'sender_name'       => !empty( $erp_options['from_name'] ) ? $erp_options['from_name'] : get_bloginfo('sitename'),
                'sender_email'      => !empty( $erp_options['from_email'] ) ? $erp_options['from_email'] : get_bloginfo('admin_email'),
                'reply_to_name'     => !empty( $erp_options['from_name'] ) ? $erp_options['from_name'] : get_bloginfo('sitename'),
                'reply_to_email'    => !empty( $erp_options['from_email'] ) ? $erp_options['from_email'] : get_bloginfo('admin_email'),
                'send'              => null,
                'campaign_name'     => null,
                'deliver_at'        => null,
            ];

            $campaign = (object) $campaign;
        }

        $hash = $this->get_hash_data();

        $schedule    = [ 'date' => '', 'time' => '' ];
        $isScheduled = false;

        if ( 'scheduled' === $campaign->send && !empty( $campaign->deliver_at ) ) {
            $schedule = [
                'date' => erp_format_date( $campaign->deliver_at ),
                'time' => date( get_option( 'time_format', 'g:i a' ), strtotime( $campaign->deliver_at ) )
            ];

            $isScheduled = true;
        }

        $event = erp_email_campaign()->get_campaign_event( $id );

        $data = [
            'pageTitle' => $id ? __( 'Edit Campaign', 'erp-email-campaign' ) : __( 'Create Campaign', 'erp-email-campaign' ),
            'step'  => $hash->step,
            'i18n' => $this->get_i18n_strings(),
            'formData' => [
                'subject' => $campaign->email_subject,
                'sender' => [
                    'name' => $campaign->sender_name,
                    'email' => $campaign->sender_email
                ],
                'replyTo' => [
                    'name' => $campaign->reply_to_name,
                    'email' => $campaign->reply_to_email
                ],
                'send' => $campaign->send ? $campaign->send : 'immediately',
                'campaignName' => $campaign->campaign_name,
                'schedule' => $schedule,
                'isScheduled' => $isScheduled,
                'event' => [
                    'action' => $event['action'],
                    'argVal' => $event['arg_value'],
                    'scheduleType' => $event['schedule_type'],
                    'scheduleOffset' => $event['schedule_offset'] ? $event['schedule_offset'] : 1,
                ],
                'lists' => erp_email_campaign()->get_campaign_lists( $id ),
                'peopleTypes' => ecamp_get_people_types()
            ],
            'automaticActions' => $this->get_automatic_actions(),
            'customizerData' => [
                'campaignId' => $id,
                'siteURL' => site_url( '/' ),
                'pluginURL' => WPERP_EMAIL_CAMPAIGN_URL,
                'dummyImage' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dummy-image.png',
                'dummyVideoImage' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dummy-video-image.png',
                'shortcodes' => templates()->shortcodes(),
                'contentTypes' => $this->get_content_types(),
                'socialIcons' => [
                    'sites' => [
                        'facebook' => [
                            'title' => 'Facebook',
                            'link' => 'http://facebook.com',
                        ],
                        'googleplus' => [
                            'title' => 'Google Plus',
                            'link' => 'http://googleplus.com',
                        ],
                        'instagram' => [
                            'title' => 'Instagram',
                            'link' => 'http://instagram.com',
                        ],
                        'link' => [
                            'title' => __( 'Website', 'erp-email-campaign' ),
                            'link' => 'http://example.com'
                        ],
                        'linkedin' => [
                            'title' => 'Linkedin',
                            'link' => 'http://linkedin.com',
                        ],
                        'pinterest' => [
                            'title' => 'Pinterest',
                            'link' => 'http://pinterest.com',
                        ],
                        'soundcloud' => [
                            'title' => 'SoundCloud',
                            'link' => 'http://soundcloud.com',
                        ],
                        'tumblr' => [
                            'title' => 'Tumblr',
                            'link' => 'http://tumblr.com',
                        ],
                        'twitter' => [
                            'title' => 'Twitter',
                            'link' => 'http://twitter.com',
                        ],
                        'youtube' => [
                            'title' => 'YouTube',
                            'link' => 'http://youtube.com',
                        ],

                    ],
                    'iconTypes' => [
                        'solid' => [ 'color', 'dark', 'gloss-round', 'gloss-square', 'square', 'gray', 'light' ],
                        'outlined' => [ 'color', 'dark', 'gray', 'light' ]
                    ],
                    'iconBGs' => [
                        'solid' => [ 'light' => '#23282d' ],
                        'outlined' => [ 'light' => '#23282d' ]
                    ]
                ],
                'borderStyles' => [
                    'none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset'
                ],
                'dividers' => [
                    'baseURL' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dividers/',
                    'images' => [
                        [ 'name' => 'brush-stroke-lite.png', 'height' => '24px' ],
                        [ 'name' => 'brush-stroke-orange.png', 'height' => '24px' ],
                        [ 'name' => 'dotted-line.png', 'height' => '24px' ],
                        [ 'name' => 'handwritten-swirl-black.png', 'height' => '24px' ],
                        [ 'name' => 'handwritten-swirl-cayan.png', 'height' => '24px' ],
                        [ 'name' => 'mail-ribbon.png', 'height' => '3px' ],
                        [ 'name' => 'ornamental-1.png', 'height' => '24px' ],
                        [ 'name' => 'ornamental-2.png', 'height' => '24px' ],
                        [ 'name' => 'ornamental-3.png', 'height' => '24px' ],
                        [ 'name' => 'shadow-1.png', 'height' => '24px' ],
                        [ 'name' => 'shadow-2.png', 'height' => '24px' ],
                        [ 'name' => 'star.png', 'height' => '24px' ],
                    ]
                ],
                'postTypes' => $this->get_all_post_types(),
                'baseTemplates' => $this->get_base_templates( $id ),
                'templateSelected' => ( $id || 0 ),
                'themes' => $this->get_themes_by_category( $id ),
            ],
            'nextBtnDisabled' => true,
            'emailTemplate' => templates()->get_campaign_template( $id ),
            'html' => '',
        ];

        return $data;
    }

    /**
     * i18n Strings for the editor
     *
     * @return array
     */
    private function get_i18n_strings() {
        return [
            'previous' => __( 'Previous', 'erp-email-campaign' ),
            'next' => __( 'Next', 'erp-email-campaign' ),
            'scheduleCampaign' => __( 'Schedule Campaign', 'erp-email-campaign' ),
            'activateNow' => __( 'Activate Now', 'erp-email-campaign' ),
            'saveAsDraft' => __( 'Save as Draft', 'erp-email-campaign' ),
            'emailSubject' => __( 'Email Subject', 'erp-email-campaign' ),
            'sender' => __( 'Sender', 'erp-email-campaign' ),
            'name' => __( 'Name', 'erp-email-campaign' ),
            'replyTo' => __( 'Reply To', 'erp-email-campaign' ),
            'campaignName' => __( 'Campaign Name', 'erp-email-campaign' ),
            'send' => __( 'Send', 'erp-email-campaign' ),
            'newsletterType' => __( 'Newsletter Type', 'erp-email-campaign' ),
            'immediately' => __( 'Immediately', 'erp-email-campaign' ),
            'immediatelyAfter' => __( 'Immediately after', 'erp-email-campaign' ),
            'scheduled' => __( 'Scheduled', 'erp-email-campaign' ),
            'standard' => __( 'Standard', 'erp-email-campaign' ),
            'automatic' => __( 'Automatic Newsletter', 'erp-email-campaign' ),
            'automaticallySend' => __( 'Automatically Send', 'erp-email-campaign' ),
            'hoursAfter' => __( 'hour(s) after', 'erp-email-campaign' ),
            'daysAfter' => __( 'day(s) after', 'erp-email-campaign' ),
            'weeksAfter' => __( 'week(s) after', 'erp-email-campaign' ),
            'schedule' => __( 'Schedule', 'erp-email-campaign' ),
            'deliveryDate' => __( 'Delivery Date', 'erp-email-campaign' ),
            'deliveryTime' => __( 'Delivery Time', 'erp-email-campaign' ),
            'lists' => __( 'Lists', 'erp-email-campaign' ),
            'content' => __( 'Content' , 'erp-email-campaign' ),
            'design' => __( 'Design' , 'erp-email-campaign' ),
            'templates' => __( 'Templates' , 'erp-email-campaign' ),
            'basic' => __( 'Basic', 'erp-email-campaign' ),
            'themes' => __( 'Themes' , 'erp-email-campaign' ),
            'text' => __( 'Text', 'erp-email-campaign' ),
            'image' => __( 'Image', 'erp-email-campaign' ),
            'imageGroup' => __( 'Image Group', 'erp-email-campaign' ),
            'imageCaption' => __( 'Image Caption', 'erp-email-campaign' ),
            'imageCard' => __( 'Image Card', 'erp-email-campaign' ),
            'socialShare' => __( 'Social Share', 'erp-email-campaign' ),
            'socialFollow' => __( 'Social Follow', 'erp-email-campaign' ),
            'wpPosts' => __( 'WP Posts', 'erp-email-campaign' ),
            'wpLatestPosts' => __( 'Latest Contents', 'erp-email-campaign' ),
            'button' => __( 'Button', 'erp-email-campaign' ),
            'divider' => __( 'Divider', 'erp-email-campaign' ),
            'video' => __( 'Video', 'erp-email-campaign' ),
            'style' => __( 'Style', 'erp-email-campaign' ),
            'settings' => __( 'Settings', 'erp-email-campaign' ),
            'numOfColumns' => __( 'Number of Columns', 'erp-email-campaign' ),
            'columnSplit' => __( 'Column Split', 'erp-email-campaign' ),
            'dropContentsHere' => __( 'Drop Contents Here' , 'erp-email-campaign' ),
            'moveThisContent' => __( 'Move this content', 'erp-email-campaign' ),
            'editThisContent' => __( 'Edit this content', 'erp-email-campaign' ),
            'copyThisContent' => __( 'Copy this content', 'erp-email-campaign' ),
            'deleteThisContent' => __( 'Delete this content', 'erp-email-campaign' ),
            'saveAndClose' => __( 'Save & Close', 'erp-email-campaign' ),
            'selectAnOption' => __( 'Select an option', 'erp-email-campaign' ),
            'numberOfColumns' => __( 'Number of Columns', 'erp-email-campaign' ),
            'numberOfImages' => __( 'Number of Images', 'erp-email-campaign' ),
            'uploadAnImage' => __( 'Upload an Image', 'erp-email-campaign' ),
            'browseImage' => __( 'Browse Image', 'erp-email-campaign' ),
            'replace' => __( 'Replace', 'erp-email-campaign' ),
            'link' => __( 'Link', 'erp-email-campaign' ),
            'alt' => __( 'Alt', 'erp-email-campaign' ),
            'title' => __( 'Title', 'erp-email-campaign' ),
            'attribute' => __( 'Attribute', 'erp-email-campaign' ),
            'remove' => __( 'Remove', 'erp-email-campaign' ),
            'close' => __( 'Close', 'erp-email-campaign' ),
            'setImageLink' => __( 'Set Image Link', 'erp-email-campaign' ),
            'setImageAltText' => __( 'Set Image Alt Text', 'erp-email-campaign' ),
            'openLinkInNewWindow' => __( 'Open Link In New Window', 'erp-email-campaign' ),
            'addMoreImage' => __( 'Add More Image', 'erp-email-campaign' ),
            'addMoreService' => __( 'Add More Service', 'erp-email-campaign' ),
            'deleteThisService' => __( 'Delete This Service', 'erp-email-campaign' ),
            'url' => __( 'URL', 'erp-email-campaign' ),
            'margins' => __( 'Margins', 'erp-email-campaign' ),
            'edgeToEdge' => __( 'Edge to Edge', 'erp-email-campaign' ),
            'imageLayout' => __( 'Image Layout', 'erp-email-campaign' ),
            'caption' => __( 'Caption', 'erp-email-campaign' ),
            'captionPosition' => __( 'Caption Position', 'erp-email-campaign' ),
            'display' => __( 'Display', 'erp-email-campaign' ),
            'iconOnly' => __( 'Icon Only', 'erp-email-campaign' ),
            'textOnly' => __( 'Text Only', 'erp-email-campaign' ),
            'bothIconAndText' => __( 'Both Icon and Text', 'erp-email-campaign' ),
            'column' => __( 'Column', 'erp-email-campaign' ),
            'columns' => __( 'Columns', 'erp-email-campaign' ),
            'page' => __( 'Page', 'erp-email-campaign' ),
            'preHeader' => __( 'Pre Header', 'erp-email-campaign' ),
            'header' => __( 'Header', 'erp-email-campaign' ),
            'body' => __( 'Body', 'erp-email-campaign' ),
            'footer' => __( 'Footer', 'erp-email-campaign' ),
            'lowerBody' => __( 'Lower Body', 'erp-email-campaign' ),
            'upperBody' => __( 'Upper Body', 'erp-email-campaign' ),
            'upperColumns' => __( 'Upper Columns', 'erp-email-campaign' ),
            'lowerColumns' => __( 'Lower Columns', 'erp-email-campaign' ),
            'email' => __( 'Email', 'erp-email-campaign' ),
            'background' => __( 'Background', 'erp-email-campaign' ),
            'color' => __( 'Color', 'erp-email-campaign' ),
            'width' => __( 'Width', 'erp-email-campaign' ),
            'height' => __( 'Height', 'erp-email-campaign' ),
            'top' => __( 'Top', 'erp-email-campaign' ),
            'bottom' => __( 'Bottom', 'erp-email-campaign' ),
            'left' => __( 'Left', 'erp-email-campaign' ),
            'right' => __( 'Right', 'erp-email-campaign' ),
            'center' => __( 'Center', 'erp-email-campaign' ),
            'border' => __( 'Border', 'erp-email-campaign' ),
            'padding' => __( 'Padding', 'erp-email-campaign' ),
            'margin' => __( 'Margin', 'erp-email-campaign' ),
            'valign' => __( 'Verticle Align', 'erp-email-campaign' ),
            'middle' => __( 'Middle', 'erp-email-campaign' ),
            'baseline' => __( 'Baseline', 'erp-email-campaign' ),
            'font' => __( 'Font', 'erp-email-campaign' ),
            'size' => __( 'Size', 'erp-email-campaign' ),
            'align' => __( 'Align', 'erp-email-campaign' ),
            'radius' => __( 'Radius', 'erp-email-campaign' ),
            'textCase' => __( 'Text Case', 'erp-email-campaign' ),
            'upperCase' => __( 'Uppercase', 'erp-email-campaign' ),
            'yes' => __( 'Yes', 'erp-email-campaign' ),
            'no' => __( 'No', 'erp-email-campaign' ),
            'ok' => __( 'OK', 'erp-email-campaign' ),
            'default' => __( 'Default', 'erp-email-campaign' ),
            'block' => __( 'Block', 'erp-email-campaign' ),
            'container' => __( 'Container', 'erp-email-campaign' ),
            'layout' => __( 'Layout', 'erp-email-campaign' ),
            'weight' => __( 'Weight', 'erp-email-campaign' ),
            'normal' => __( 'Normal', 'erp-email-campaign' ),
            'bold' => __( 'Bold', 'erp-email-campaign' ),
            'iconStyle' => __( 'Icon Style', 'erp-email-campaign' ),
            'solid' => __( 'Solid', 'erp-email-campaign' ),
            'outlined' => __( 'Outlined', 'erp-email-campaign' ),
            'dividerType' => __( 'Divider Type', 'erp-email-campaign' ),
            'line' => __( 'Line', 'erp-email-campaign' ),
            'gallery' => __( 'Gallery', 'erp-email-campaign' ),
            'chooseDivider' => __( 'Choose Divider', 'erp-email-campaign' ),
            'browse' => __( 'Browse', 'erp-email-campaign' ),
            'cancel' => __( 'Cancel', 'erp-email-campaign' ),
            'confirmDeleteMsg' => __( 'Are you sure you want to delete this content?', 'erp-email-campaign' ),
            'confirmDeleteBtn' => __( 'Yes, delete it', 'erp-email-campaign' ),
            'confirmCancelBtn' => __( 'No, cancel it', 'erp-email-campaign' ),
            'filterByPostType' => __( 'Filter by Post Type', 'erp-email-campaign' ),
            'categoriesTags' => __( 'Categories and Tags', 'erp-email-campaign' ),
            'filterByPostStatus' => __( 'Filter By Status', 'erp-email-campaign' ),
            'publish' => __( 'Publish', 'erp-email-campaign' ),
            'draft' => __( 'Draft', 'erp-email-campaign' ),
            'pending' => __( 'Pending Review', 'erp-email-campaign' ),
            'future' => __( 'Scheduled', 'erp-email-campaign' ),
            'private' => __( 'Private', 'erp-email-campaign' ),
            'hide' => __( 'Hide', 'erp-email-campaign' ),
            'show' => __( 'Show', 'erp-email-campaign' ),
            'underline' => __( 'Underline', 'erp-email-campaign' ),
            'hideDivider' => __( 'Hide Divider', 'erp-email-campaign' ),
            'excerpt' => __( 'Excerpt', 'erp-email-campaign' ),
            'fullPost' => __( 'Full Post', 'erp-email-campaign' ),
            'titleAndImage' => __( 'Title and Image', 'erp-email-campaign' ),
            'insertSelected' => __( 'Insert Selected', 'erp-email-campaign' ),
            'loadMore' => __( 'Load More', 'erp-email-campaign' ),
            'maxPostsToShow' => __( 'Maximum Posts to Show', 'erp-email-campaign' ),
            'done' => __( 'Done', 'erp-email-campaign' ),
            'previewTemplate' => __( 'Preview Template', 'erp-email-campaign' ),
            'selectTemplate' => __( 'Select Template', 'erp-email-campaign' ),
            'selectTheme' => __( 'Select Theme', 'erp-email-campaign' ),
            'all' => __( 'All', 'erp-email-campaign' ),
            'searchThemes' => __( 'Search Themes', 'erp-email-campaign' ),
            'videoLinkTips' => sprintf( __( 'We\'ll link the URL above to a video preview image in your email. Preview images will be generated automatically for <a href="%s">YouTube</a> and <a href="%s">Vimeo</a> URLs.', 'erp-email-campaign' ), 'http://youtube.com/', 'http://vimeo.com/' ),
            'videoNoThumbError' => __( 'Sorry, we can\'t generate a preview image for that URL. Please upload an image.', 'erp-email-campaign' ),
            'wordpressPosts' => __( 'WordPress Posts', 'erp-email-campaign' ),
            'autoLatestContent' => __( 'Automatic Latest Content', 'erp-email-campaign' ),
            'icon' => __( 'Icon', 'erp-email-campaign' ),
            'none' => __( 'None', 'erp-email-campaign' ),
            'sendPreview' => __( 'Send Preview', 'erp-email-campaign' ),
            'writeYourEmail' => __( 'You need to input your email address.', 'erp-email-campaign' ),
            'preview' => __( 'Preview', 'erp-email-campaign' ),
            'reviewDetails' => __( 'Review Details', 'erp-email-campaign' ),
            'charactersRemaining' => __( 'characters remaining', 'erp-email-campaign' ),
            'requiredField' => __( 'This field is required', 'erp-email-campaign' ),
            'invalidEmail' => __( 'Invalid email address', 'erp-email-campaign' ),
            'senderHint' => __( 'Name & email of yourself or your company.', 'erp-email-campaign' ),
            'replyToHint' => __( 'When the subscribers hit "reply" this is who will receive their emails.', 'erp-email-campaign' ),
            'googleCampaignName' => __( 'Google Analytics Campaign', 'erp-email-campaign' ),
            'emailContent' => __( 'Email Content', 'erp-email-campaign' ),
            'fontFamily' => __( 'Font Family', 'erp-email-campaign' ),
            'fontSize' => __( 'Font Size', 'erp-email-campaign' ),
            'restoreToDefault' => __( 'Restore to default', 'erp-email-campaign' ),
            'inherit' => __( 'Inherit', 'erp-email-campaign' ),
            'noListFound' => __( 'No list found', 'erp-email-campaign' ),
            'addShortcode' => __( 'Add Shortcode', 'erp-email-campaign' ),
            'noListFoundForAction' => __( 'no list found for this action', 'erp-email-campaign' ),
        ];
    }

    /**
     * Get content types with their default settings
     *
     * @return array
     */
    public function get_content_types() {
        return [
            'text' => [
                'image' => 'content-type-text.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'paddingTop' => '15px',
                        'paddingBottom' => '15px',
                        'paddingLeft' => '15px',
                        'paddingRight' => '15px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5'
                    ],
                    'activeColumns' => 1,
                    'texts' => [
                        sprintf( '<p>%s</p>', __( 'This is a text block. You can use it to add text to your template.', 'erp-email-campaign' ) ),
                        sprintf( '<p>%s</p>', __( 'This is a text block. You can use it to add text to your template.', 'erp-email-campaign' ) )
                    ],

                    'columnSplit' => '1-1',
                    'valign' => 'top',
                ]
            ],
            'image' => [
                'image' => 'content-type-image.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'padding' => '15px',
                        'marginLeft' => '0px',
                        'marginRight' => '0px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5',
                        'textAlign' => 'center',
                    ],
                    'images' => [],
                    'widths' => [ '0px' ]
                ]
            ],
            'imageGroup' => [
                'image' => 'content-type-image-group.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'padding' => '15px',
                        'marginLeft' => '0px',
                        'marginRight' => '0px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5',
                        'textAlign' => 'center',
                    ],
                    'images' => [],
                    'widths' => [ '0px', '0px', '0px' ],
                    'layout' => 'r1-r1',
                ]
            ],
            'imageCaption' => [
                'image' => 'content-type-image-caption.png',
                'defaultText' => sprintf( '<p>%s</p>', __( 'Your text caption goes here. You can change the position of the caption and set styles in the block’s settings tab.', 'erp-email-campaign' ) ),
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'padding' => '15px 15px',
                        'fontSize' => '14px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5'
                    ],
                    'activeColumns' => 1,
                    'groups' => [],

                    'capPosition' => 'bottom',
                ]
            ],
            'socialFollow' => [
                'image' => 'content-type-social-follow.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'padding' => '15px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'none',
                        'borderColor' => '',
                        'fontSize' => '14px',
                        'fontWeight' => 'normal',
                        'textTransform' => 'none',
                        'lineHeight' => '0px'
                    ],
                    'iconStyle' => 'solid-color',
                    'icons' => [
                        [
                            'site' => 'facebook',
                            'link' => 'http://facebook.com',
                            'text' => 'Facebook',
                        ],
                        [
                            'site' => 'twitter',
                            'link' => 'http://twitter.com',
                            'text' => 'Twitter',
                        ],
                        [
                            'site' => 'link',
                            'link' => 'http://example.com',
                            'text' => __( 'Website', 'erp-email-campaign' ),
                        ]
                    ],
                    'iconMargin' => '15px',
                    'display' => 'both', // icon/text/both
                    'containerAlign' => 'center',
                    'layout' => 'horizontal', // verticle/horizontal
                    'layoutSize' => 'default', // default/big
                ]
            ],
            'button' => [
                'image' => 'content-type-button.png',
                'default' => [
                    'style' => [
                        'display' => 'inline-block',
                        'padding' => '18px 65px',
                        'margin' => '15px 15px',
                        'fontFamily' => 'sans-serif',
                        'fontSize' => '14px',
                        'fontWeight' => 'bold',
                        'lineHeight' => '1',
                        'color' => '#fff',
                        'textAlign' => 'center',
                        'textDecoration' => 'none',
                        'textTransform' => 'none',
                        'backgroundColor' => '#2980b9',
                        'border' => '0px none #176598',
                        'borderRadius' => '3px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5'
                    ],
                    'text' => __( 'Button Text', 'erp-email-campaign' ),
                    'link' => '#',
                    'title' => '',
                    'containerStyle' => [
                        'textAlign' => 'center',
                        'backgroundColor' => '#ffffff'
                    ]
                ]
            ],
            'divider' => [
                'image' => 'content-type-divider.png',
                'noStyleSettings' => true,
                'default' => [
                    'containerStyle' => [
                        'paddingTop' => '15px',
                        'paddingBottom' => '15px',
                        'backgroundColor' => '#ffffff',
                        'marginTop' => '0px',
                        'marginBottom' => '0px'
                    ],
                    'style' => [
                        'width' => '600px',
                        'borderTopWidth' => '2px',
                        'borderTopStyle' => 'dashed',
                        'borderTopColor' => '#e5e5e5',
                    ],
                    'useImage' => false,
                    'image' => [
                        'image' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dividers/brush-stroke-lite.png',
                        'style' => [
                            'height' => '7px',
                            'width' => '600px',
                        ]
                    ]
                ]
            ],
            'wpPosts' => [
                'image' => 'content-type-wp.png',
                'default' => [
                    'style' => [
                        'padding' => '15px',
                    ],
                    'postIds' => [],
                    'layout' => 'i1-tc1',
                    'column' => 1,
                    'title' => [
                        'container' => [
                            'tag' => 'h2',
                            'style' => [
                                'lineHeight' => '30px',
                                'marginBottom' => '15px'
                            ]
                        ],
                        'style' => [
                            'fontSize' => '30px',
                            'color' => '#333333',
                            'textAlign' => 'left',
                            'fontWeight' => 'normal',
                            'textTransform' => 'none',
                        ],
                    ],
                    'image' => [
                        'active' => true,
                        'style' => [
                            'width' => '100%',
                            'float' => 'none',
                            'borderWidth' => '0px',
                            'borderStyle' => 'none',
                            'borderColor' => '#ffffff',
                            'padding' => '0px',
                            'backgroundColor' => '#ffffff',
                            'marginTop' => '0px',
                            'marginRight' => '0px',
                            'marginBottom' => '0px',
                            'marginLeft' => '0px',
                        ]
                    ],
                    'postContent' => [
                        'length' => 'excerpt',
                        'containerStyle' => [
                            'marginBottom' => '15px',
                        ],
                        'style' => [
                            'fontSize' => '14px',
                            'color' => '#333333',
                            'textAlign' => 'left',
                            'lineHeight' => '20px',
                        ],
                    ],
                    'readMore' => [
                        'show' => 'display',
                        'style' => [
                            'display' => 'inline-block',
                            'padding' => '0px 0px',
                            'fontSize' => '14px',
                            'fontWeight' => 'normal',
                            'lineHeight' => '1',
                            'color' => '#0073aa',
                            'textAlign' => 'center',
                            'textDecoration' => 'underline',
                            'textTransform' => 'none',
                            'backgroundColor' => '',
                            'borderRadius' => '0px',
                            'borderWidth' => '0px',
                            'borderStyle' => 'solid',
                            'borderColor' => '#e5e5e5'
                        ],
                        'text' => __( 'Read More', 'erp-email-campaign' ),
                        'containerStyle' => [
                            'textAlign' => 'left',
                        ]
                    ],
                    'divider' => [
                        'display' => 'show',
                        'containerStyle' => [
                            'marginTop' => '15px',
                            'marginBottom' => '15px'
                        ],
                        'style' => [
                            'width' => '570px',
                            'borderTopWidth' => '1px',
                            'borderTopStyle' => 'dashed',
                            'borderTopColor' => '#e5e5e5',
                        ],
                        'useImage' => true,
                        'image' => [
                            'image' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dividers/brush-stroke-lite.png',
                            'style' => [
                                'height' => '7px',
                                'width' => '600px',
                            ]
                        ]
                    ],
                    'author' => [
                        'position' => 'above',
                        'precededBy' => 'Author:',
                    ],
                    'categories' => [
                        'position' => 'above',
                        'precededBy' => 'Categories:'
                    ],
                ]
            ],
            'wpLatestPosts' => [
                'image' => 'content-type-wp-latest.png',
                'default' => [
                    'style' => [
                        'padding' => '15px'
                    ],
                    'postIds' => [],
                    'postType' => 'post',
                    'taxTerms' => [],
                    'layout' => 'i1-tc1',
                    'column' => 1,
                    'title' => [
                        'container' => [
                            'tag' => 'h2',
                            'style' => [
                                'lineHeight' => '30px',
                                'marginBottom' => '15px'
                            ]
                        ],
                        'style' => [
                            'fontSize' => '30px',
                            'color' => '#333333',
                            'textAlign' => 'left',
                            'fontWeight' => 'normal',
                            'textTransform' => 'none',
                        ],
                    ],
                    'image' => [
                        'active' => true,
                        'style' => [
                            'width' => '100%',
                            'float' => 'none',
                            'borderWidth' => '0px',
                            'borderStyle' => 'none',
                            'borderColor' => '#ffffff',
                            'padding' => '0px',
                            'backgroundColor' => '#ffffff',
                            'marginTop' => '0px',
                            'marginRight' => '0px',
                            'marginBottom' => '0px',
                            'marginLeft' => '0px',
                        ]
                    ],
                    'postContent' => [
                        'length' => 'excerpt',
                        'containerStyle' => [
                            'marginBottom' => '15px',
                        ],
                        'style' => [
                            'fontSize' => '14px',
                            'color' => '#333333',
                            'textAlign' => 'left',
                            'lineHeight' => '20px',
                        ],
                    ],
                    'readMore' => [
                        'show' => 'display',
                        'style' => [
                            'display' => 'inline-block',
                            'padding' => '0px 0px',
                            'fontSize' => '14px',
                            'fontWeight' => 'normal',
                            'lineHeight' => '1',
                            'color' => '#0073aa',
                            'textAlign' => 'center',
                            'textDecoration' => 'underline',
                            'textTransform' => 'none',
                            'backgroundColor' => '',
                            'border' => '0px none #176598',
                            'borderRadius' => '0px',
                        ],
                        'text' => __( 'Read More', 'erp-email-campaign' ),
                        'containerStyle' => [
                            'textAlign' => 'left',
                        ]
                    ],
                    'divider' => [
                        'display' => 'show',
                        'containerStyle' => [
                            'marginTop' => '15px',
                            'marginBottom' => '15px'
                        ],
                        'style' => [
                            'width' => '570px',
                            'borderTopWidth' => '1px',
                            'borderTopStyle' => 'dashed',
                            'borderTopColor' => '#e5e5e5',
                        ],
                        'useImage' => false,
                        'image' => [
                            'image' => WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dividers/default.png',
                            'style' => [
                                'margin' => '0 auto', // fixed
                                'backgroundPosition' => 'center center', // fixed
                                'backgroundImage' => 'url(' . WPERP_EMAIL_CAMPAIGN_ASSETS . '/images/dividers/default.png)',
                                'backgroundSize' => 'contain',
                                'backgroundRepeatX' => 'repeat',
                                'backgroundRepeatY' => 'repeat',
                                'height' => '30px',
                                'width' => '600px',
                            ]
                        ]
                    ],
                    'author' => [
                        'position' => 'above',
                        'precededBy' => 'Author:',
                    ],
                    'categories' => [
                        'position' => 'above',
                        'precededBy' => 'Categories:'
                    ],
                ]
            ],
            'video' => [
                'image' => 'content-type-video.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => 'transparent',
                        'padding' => '15px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5'
                    ],
                    'textStyle' => [
                        'backgroundColor' => '#333333',
                        'fontSize' => '14px',
                        'color' => '#ffffff',
                        'textAlign' => 'center',
                        'padding' => '15px',
                    ],
                    'video' => [
                        'link' => '',
                        'image' => '',
                        'alt' => '',
                        'openAttrEditor' => ''
                    ],
                    'text' => sprintf( '<p>%s</p>', __( 'This is a text block. You can use it to add text to your template.', 'erp-email-campaign' ) ),
                    'capPosition' => 'bottom',
                ]
            ],
            'footer' => [
                'image' => 'content-type-footer.png',
                'default' => [
                    'style' => [
                        'backgroundColor' => '#ffffff',
                        'paddingTop' => '15px',
                        'paddingBottom' => '15px',
                        'paddingLeft' => '15px',
                        'paddingRight' => '15px',
                        'borderWidth' => '0px',
                        'borderStyle' => 'solid',
                        'borderColor' => '#e5e5e5'
                    ],
                    'activeColumns' => 1,
                    'texts' => [
                        '<p style="text-align: center;"><span style="font-size: 12px;">This email was sent to [user:email] because you have opted in to receive specific updates on our website.</span></p><p style="text-align: center;"><span style="font-size: 12px;">If you would prefer not to receive any email from us in the future, please [links:unsubscribe text="click here to unsubscribe"] or go to your [links:edit_subscription text="account preferences"] on our website.</span></p><p style="text-align: center;"><span style="font-size: 12px;"><strong>Our mailing address</strong></span><br /><span style="font-size: 12px;"> [company:name]</span><br /><span style="font-size: 12px;"> [company:address]</span></p><p style="text-align: center;"><span style="font-size: 12px;">Copyright © [date:year] [company:name], All rights reserved.</span></p>',
                        sprintf( '<p>%s</p>', __( 'This is a text block. You can use it to add text to your template.', 'erp-email-campaign' ) )
                    ],
                    'columnSplit' => '1-1',
                    'valign' => 'top',
                ]
            ],
        ];
    }

    /**
     * Returns editor current step number based on URL hash
     *
     * @return int
     */
    private function get_hash_data() {
        $data = [ 'step' => 1 ];

        if ( !empty( $_REQUEST['urlHash'] ) ) {
            $hash = str_replace( '#', '', $_REQUEST['urlHash'] );

            $hash = explode( '/' , $hash );

            for ($i = 0; $i < count( $hash ); $i += 2) {
                $data[ $hash[ $i ] ] = absint( $hash[ $i+1 ] );
            }
        }

        return json_decode( json_encode( $data ), false );
    }

    /**
     * Returns all the post types
     *
     * @return array
     */
    private function get_all_post_types() {
        return campaign_posts()->get_all_post_types();
    }

    /**
     * Automatic email sending options
     *
     * @since 1.0.0
     * @since 1.1.0 Add `erp_matches_segment` option
     *
     * @return array
     */
    public function get_automatic_actions() {
        $actions = [
            'erp_crm_create_contact_subscriber' => __( 'when someone subscribes to the list', 'erp-email-campaign' ),
            'erp_create_new_people'             => __( 'when a new ERP user is created as', 'erp-email-campaign' ),
            'erp_matches_segment'               => __( 'when a contact added to the segment', 'erp-email-campaign' ),
        ];

        return $actions;
    }

    /**
     * Save Campaign
     *
     * We are not going to store data into DB in this method.
     * This method is only responsible to format the posted data and provide them
     * to save_campaign method of Email_Campaign class.
     *
     * @since 1.0.0
     * @since 1.1.0 Fix `deliver_at` date time format based on the ERP date_time settings
     *
     * @param array $data
     *
     * @return int saved campaign id
     */
    public function save_campaign( $data ) {
        $selected_lists = [];

        $campaign_id = !empty( $data['campaign_id'] ) ? absint( $data['campaign_id'] ) : 0;

        $form_data = $data['form_data'];

        $status = $data['status'];

        $campaign = [
            'email_subject'     => $form_data['subject'],
            'status'            => $status,
            'sender_name'       => $form_data['sender']['name'],
            'sender_email'      => $form_data['sender']['email'],
            'reply_to_name'     => $form_data['replyTo']['name'],
            'reply_to_email'    => $form_data['replyTo']['email'],
            'send'              => $form_data['send'],
            'campaign_name'     => $form_data['campaignName'],
            'deliver_at'        => null,
        ];

        if ( !empty( $form_data['schedule']['date'] ) && !empty( $form_data['schedule']['time'] ) ) {
            $erp_date_format = erp_get_option( 'date_format', 'erp_settings_general', 'd-m-Y' );
            $date            = $_POST['form_data']['schedule']['date'];
            $time            = $_POST['form_data']['schedule']['time'];
            $tzstring        = ecamp_get_wp_timezone();

            $date = \Carbon\Carbon::createFromFormat( $erp_date_format . ' g:i a', $date . ' ' . $time, $tzstring );

            $campaign['deliver_at'] = $date->toDateTimeString();
        }

        if ( 'scheduled' !== $form_data['send'] ) {
            $campaign['deliver_at'] = null;
        }

        if ( 'automatic' === $form_data['send'] ) {
            $campaign['event'] = [
                'action'            => $form_data['event']['action'],
                'arg_value'         => $form_data['event']['argVal'],
                'schedule_type'     => $form_data['event']['scheduleType'],
                'schedule_offset'   => ( 'immediately' === $form_data['event']['scheduleType'] ) ? 0 : $form_data['event']['scheduleOffset']
            ];

        } else {
            foreach ( $form_data['lists'] as $type => $list ) {
                if ( !empty( $list['selected'] ) ) {
                    $selected_lists[ $type ] = $list['selected'];
                }
            }
        }

        $email_template = [
            'template' => $data['email_template'],
            'html' => $data['html']
        ];

        $log_campaign = !empty( $data['log_campaign'] ) ? true : false;

        return erp_email_campaign()->save_campaign( $campaign_id, $campaign, $selected_lists, $email_template, $log_campaign );
    }

    /**
     * Get Base Templates
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_base_templates( $campaign_id ) {
        // we don't need base templates when we're editing an existing campaign
        if ( !empty( $campaign_id ) ) {
            return [];
        }

        return templates()->get_base_templates();

    }

    /**
     * Get themes grouped by categories
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function get_themes_by_category( $campaign_id ) {
        // we don't need base templates when we're editing and existing campaign
        if ( !empty( $campaign_id ) ) {
            return [];
        }

        return templates()->get_themes_by_category();

    }

    /**
     * Get youtube and vimeo video thumbnail image links
     *
     * @param string $source
     * @param string $video_id
     *
     * @return string
     */
    public function get_video_thumb_from_url( $source, $video_id ) {
        $image_link = '';

        if ( 'youtube' === $source ) {
            $response = wp_remote_head( "http://img.youtube.com/vi/$video_id/maxresdefault.jpg" );
            $code = wp_remote_retrieve_response_code( $response );

            if ( 200 !== $code ) {
                $response = wp_remote_head( "http://img.youtube.com/vi/$video_id/0.jpg" );
                $code = wp_remote_retrieve_response_code( $response );

                if ( 200 === $code ) {
                    $image_link = "http://img.youtube.com/vi/$video_id/0.jpg";
                }

            } else {
                $image_link = "http://img.youtube.com/vi/$video_id/maxresdefault.jpg";
            }

        } else if ( 'vimeo' === $source ) {
            $response = wp_remote_get( "https://vimeo.com/api/v2/video/$video_id.xml" );
            $code = wp_remote_retrieve_response_code( $response );
            if ( 200 === $code ) {
                $body = wp_remote_retrieve_body( $response );
                $xml = simplexml_load_string( $body );
                $json = json_encode( $xml );
                $obj = json_decode( $json );

                if ( !empty( $obj->video->thumbnail_large ) ) {
                    $image_link = $obj->video->thumbnail_large;
                } else {
                    $image_link = $obj->video->thumbnail_medium;
                }
            }
        }

        return $image_link;
    }

}

/**
 * Class instance
 *
 * @return object
 */
function campaign_editor() {
    return Campaign_Editor::instance();
}
