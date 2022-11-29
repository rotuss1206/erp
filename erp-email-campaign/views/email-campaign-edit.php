<div class="wrap erp-email-campaign erp-email-campaign-edit" id="erp-email-campaign-edit" v-cloak>

    <h2 class="clear ecamp-page-title">
        {{ pageTitle }}

        <span class="alignright">
            <button class="button" :disabled="isPreviewBtnDisabled" v-on:click="goToPreviewPage">
                <i class="fa fa-eye"></i> {{ i18n.previewTemplate }}
            </button>
            <button class="button" :disabled="isPreviewBtnDisabled" v-on:click="sendPreviewEmail">
                <i class="fa fa-paper-plane-o"></i> {{ i18n.sendPreview }}
            </button>
        </span>
    </h2>

    <form action="#" v-on:submit="preventFormSubmission">
        <div class="erp-grid-container margin-top-12">
            <div id="editor-step-1" v-if="1 === step">
                <campaign-form  :i18n="i18n" :form-data="formData" :automatic-actions="automaticActions" :shortcodes="customizerData.shortcodes"></campaign-form>
            </div>

            <div id="editor-step-2" v-if="2 === step">
                <customizer :customizer-data="customizerData" :i18n="i18n" :email-template="emailTemplate" ></customizer>
            </div>

            <div id="editor-step-3" v-if="3 === step">
                <h3>{{ i18n.reviewDetails }}</h3>
                <hr>
                <table class="form-table review-details-table">
                    <tbody>
                        <tr>
                            <th><label for="email-subject">{{ i18n.emailSubject }}</label></th>
                            <td>{{ formData.subject }}</td>
                        </tr>
                        <tr>
                            <th><label for="sender">{{ i18n.sender }}</label></th>
                            <td>{{ formData.sender.name }} <{{ formData.sender.email }}></td>
                        </tr>
                        <tr>
                            <th><label for="reply-to">{{ i18n.replyTo }}</label></th>
                            <td>{{ formData.replyTo.name }} <{{ formData.replyTo.email }}></td>
                        </tr>
                        <tr>
                            <th><label for="campaign-type">{{ i18n.newsletterType }}</label></th>
                            <td>
                                <span v-if="'automatic' !== formData.send">{{ i18n.standard }}</span>
                                <span v-else>{{ i18n.automatic }}</span>

                                <p v-if="'automatic' === formData.send">{{{ automaticPhrase }}}</p>
                            </td>
                        </tr>
                        <tr v-if="'automatic' !== formData.send">
                            <th><label>{{ i18n.lists }}</label></th>
                            <td>
                                <div class="row">
                                    <div class="col-3">
                                        <ul class="ecamp-contact-lists" v-for="(typeSlug, listType) in formData.lists">
                                            <li><strong>{{ listType.title }}</strong></li>
                                            <ul>
                                                <li v-for="list in listType.lists" v-if="isListSelected(typeSlug, list.id)">
                                                    {{ list.name }} ({{ list.count }})
                                                </li>
                                                <li v-if="!listType.lists.length"><em>{{ i18n.noListFound }}</em></li>
                                            </ul>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="'automatic' !== formData.send">
                            <th><label>{{ i18n.scheduleCampaign }}</label></th>
                            <td>
                                <div class="row erp-email-campaign-schedule-setter">
                                    <div class="col-3" v-if="formData.isScheduled">
                                        <div class="row">
                                            <div class="col-1">
                                                <input type="checkbox" class="form-control" v-model="formData.isScheduled">
                                            </div>
                                            <div class="col-2">
                                                <datepicker
                                                    id="delivery-date"
                                                    class="form-control margin-bottom-12"
                                                    :date="formData.schedule.date"
                                                    :exclude="'prev'"
                                                ></datepicker>
                                            </div>

                                            <div class="col-1 symbol">
                                                @
                                            </div>

                                            <div class="col-2">
                                                <timepicker
                                                    id="delivery-time"
                                                    class="form-control"
                                                    :time="formData.schedule.time"
                                                ></timepicker>
                                            </div>
                                        </div>

                                        <p class="hint">Current local time is {{ currentLocalTime }}</p>

                                    </div>
                                    <div class="col-4" v-else>
                                        <label><input type="checkbox" class="form-control" v-model="formData.isScheduled"> Yes schedule it</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label>{{ i18n.googleCampaignName }}</label></th>
                            <td>
                                <div class="row">
                                    <div class="col-3">
                                        <input type="text" class="form-control" v-model="formData.campaignName">
                                        <p class="hint">For example "New year sale"</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div><!-- .erp-grid-container -->

        <p v-if="!hideFooterBtns">
            <button class="button button-primary" type="button" v-if="showPrevBtn" v-on:click="--step">
                <i class="fa fa-angle-double-left"></i> {{ i18n.previous }}
            </button>
             <button class="button button-primary" type="button" v-if="showNextBtn" :disabled="isNextBtnDisabled" v-on:click="goToNextStep(step)">
                {{ i18n.next }} <i class="fa fa-angle-double-right"></i>
            </button>
             <button class="button button-primary" type="button" v-if="showSubmitBtn" :disabled="isSubmitBtnDisabled" v-on:click="saveCampaign(false, false)">
                {{ submitBtnLabel }}
            </button>
             <button class="button" type="button" v-if="showSubmitBtn" :disabled="isSubmitBtnDisabled" v-on:click="saveCampaign(true, false)">
                {{ i18n.saveAsDraft }}
            </button>
        </p>

        <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
    </form>

</div><!-- #erp-email-campaign-edit -->
