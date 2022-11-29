;(function (templates, undefined) {
  templates['datepicker'] = '<input type="text" v-model="date" placeholder="{{ placeholder }}">';
  templates['timepicker'] = '<input type="text" v-model="time" placeholder="{{ placeholder }}">';
  templates['colorpicker'] = '<input type="text" v-model="color">';
  templates['input-range'] = '<input class="ecamp-range" type="range" v-model="value" min="{{ min }}" max="{{ max }}" step="{{ step }}">';
  templates['text-editor'] = '<textarea id="vue-text-editor-{{ editorId }}" class="vue-text-editor">{{ content }}</textarea>';
  templates['vselect'] = '<select></select>';
  templates['campaign-form'] = '<table class="form-table"><tbody><tr><th><label for="email-subject">{{ i18n.emailSubject }}</label></th><td><div class="row"><div class="col-3 has-validator"><div class="floating-info" style="z-index: 2"><input type="text" class="form-control" id="email-subject" :maxlength="subMaxLength" data-validation="isEmptyInput" data-validation-msg="emailSubject" v-on:focus="focusFloatingInfoInput" v-on:blur="blurFloatingInfoInput" v-model="formData.subject"><span :class="[\'floating-info-text\', \'text-right\', \'always-on-top\', subRemainingCharClass]">{{ subRemainingChar }} {{ i18n.charactersRemaining }}</span><div class="erp-email-campaign-shortcode-btn"><button class="button button-link erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false" data-content="addShortcode" v-tiptip="">&nbsp;</button><ul class="erp-dropdown-menu pull-right"><li v-for="shortcode in shortcodeList"><a :href="\'#\' + shortcode.title" v-on:click="addSubjectShortcode($event, shortcode)" :class="[shortcode.parent ? \'erp-dropdown-menu-section\' : \'\']"><strong v-if="shortcode.parent">{{ shortcode.title }}</strong> <span v-else="">{{ shortcode.title }}</span></a></li></ul></div></div><p class="validation-error" data-validtion-msg="emailSubject">{{ i18n.requiredField }}</p></div></div></td></tr><tr><th><label for="sender">{{ i18n.sender }}</label></th><td><div class="row"><div class="col-3"><div class="row pad-no-left-padding-xs"><div class="col-3 has-validator"><div class="floating-info"><input type="text" class="form-control" data-validation="isEmptyInput" data-validation-msg="senderName" v-on:focus="focusFloatingInfoInput" v-on:blur="blurFloatingInfoInput" v-model="formData.sender.name"><span class="floating-info-text">{{ i18n.name }}</span></div><p class="validation-error" data-validtion-msg="senderName">{{ i18n.requiredField }}</p></div><div class="col-3 no-left-padding has-validator"><div class="floating-info"><input type="text" class="form-control" data-validation="isInvalidEmail" data-validation-msg="senderEmail" v-on:focus="focusFloatingInfoInput" v-on:blur="blurFloatingInfoInput" v-model="formData.sender.email"><span class="floating-info-text">{{ i18n.email }}</span></div><p class="validation-error no-left-padding" data-validtion-msg="senderEmail">{{ i18n.invalidEmail }}</p></div></div><p class="hint">{{ i18n.senderHint }}</p></div></div></td></tr><tr><th><label for="reply-to">{{ i18n.replyTo }}</label></th><td><div class="row"><div class="col-3"><div class="row pad-no-left-padding-xs"><div class="col-3 has-validator"><div class="floating-info"><input type="text" class="form-control" data-validation="isEmptyInput" data-validation-msg="replyToName" v-on:focus="focusFloatingInfoInput" v-on:blur="blurFloatingInfoInput" v-model="formData.replyTo.name"><span class="floating-info-text">{{ i18n.name }}</span></div><p class="validation-error" data-validtion-msg="replyToName">{{ i18n.requiredField }}</p></div><div class="col-3 no-left-padding has-validator"><div class="floating-info"><input type="text" class="form-control" data-validation="isInvalidEmail" data-validation-msg="replyToEmail" v-on:focus="focusFloatingInfoInput" v-on:blur="blurFloatingInfoInput" v-model="formData.replyTo.email"><span class="floating-info-text">{{ i18n.email }}</span></div><p class="validation-error no-left-padding" data-validtion-msg="replyToEmail">{{ i18n.invalidEmail }}</p></div></div><p class="hint">{{ i18n.replyToHint }}</p></div></div></td></tr><tr><th><label for="campaign-type">{{ i18n.newsletterType }}</label></th><td><div class="list-radios"><label><input type="radio" name="campaign_type" value="standard" v-model="campaignType">{{ i18n.standard }}</label><label><input type="radio" name="campaign_type" value="automatic" v-model="campaignType">{{ i18n.automatic }}</label></div></td></tr><tr v-if="\'automatic\' === formData.send"><th><label>{{ i18n.automaticallySend }}</label></th><td><div class="row"><div class="col-2"><select name="" class="form-control margin-bottom-5" v-model="formData.event.action"><option v-for="(action, title) in automaticActions" value="{{ action }}">{{ title }}</option></select></div><div v-if="actionLists.length" class="col-2 no-left-padding"><select class="form-control margin-bottom-5" v-model="event.argVal"><option v-for="list in actionLists" value="{{ list.id }}">{{ list.name }}</option></select></div><div v-if="actionLists.length" class="col-2 no-left-padding"><div class="automatic-schedule"><input v-if="\'immediately\' !== event.scheduleType" class="small-text" type="number" min="1" v-model="event.scheduleOffset"><select v-model="event.scheduleType"><option value="immediately">{{ i18n.immediately }}</option><option value="hour">{{ i18n.hoursAfter }}</option><option value="day">{{ i18n.daysAfter }}</option><option value="week">{{ i18n.weeksAfter }}</option></select></div></div><div v-if="!actionLists.length" class="col-2 no-left-padding"><p><em>{{ i18n.noListFoundForAction }}</em></p></div></div></td></tr><tr v-if="\'automatic\' !== formData.send"><th><label>{{ i18n.lists }}</label></th><td><div class="row"><div class="col-3"><ul class="ecamp-contact-lists" v-for="(typeSlug, listType) in formData.lists"><li><strong>{{ listType.title }}</strong></li><li v-for="list in listType.lists" v-if="list.count"><label><input type="checkbox" value="{{ list.id }}" v-model="listType.selected">&nbsp;{{ list.name }} ({{ list.count }})</label></li><li v-if="!listType.lists.length"><em>{{ i18n.noListFound }}</em></li></ul></div></div></td></tr></tbody></table>';
  templates['content-tab'] = '<div id="content-types-container"><div class="content-type" v-for="(type, content) in contentTypes" data-content-type="{{ type }}"><div class="button" :style="{ backgroundImage: getBackgroundImage(content.image) }">{{ i18n[type] }}</div></div></div>';
  templates['design-tab'] = '<div class="section-list margin-bottom-12"><div class="section-name"><a href="#edit" class="link-black section-title" v-on:click="openPageEditor"><h3 class="clearfix">{{ i18n.page }} <i class="fa fa-angle-right"></i></h3></a></div><div class="section-name" v-for="(secIndex, section) in sections"><a href="#edit" class="link-black section-title" v-on:click="openEditor($event, secIndex)" v-on:mouseover="showHighlighter(secIndex)" v-on:mouseout="hideHighlighter"><h3>{{ i18n[section.title] }} <i class="fa fa-angle-right"></i></h3></a></div></div>';
  templates['page-editor'] = '<div class="sidebar-container"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ emailTemplate.globalCss.backgroundColor }}</span></h4><div class="property"><colorpicker :color="emailTemplate.globalCss.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} {{ i18n.top }} <span class="property-value">{{ parseInt(emailTemplate.globalCss.borderTopWidth) ? emailTemplate.globalCss.borderTopWidth : \'0px\' }} &nbsp; {{ emailTemplate.globalCss.borderTopColor ? emailTemplate.globalCss.borderTopColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="emailTemplate.globalCss.borderTopWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="emailTemplate.globalCss.borderTopColor"></colorpicker></td></tr></table></div></div><div class="control-property" v-if="\'full-width\' !== emailTemplate.templateType"><h4 class="property-title clearfix">{{ i18n.email }} {{ i18n.border }} <span class="property-value">{{ emailBorderLabel }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="emailBorderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="emailBorderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }} <span class="property-value">{{ emailTemplate.globalCss.paddingTop ? emailTemplate.globalCss.paddingTop : \'0px\' }}</span></h4><div class="property"><input-range :model="emailTemplate.globalCss.paddingTop" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.bottom }} <span class="property-value">{{ emailTemplate.globalCss.paddingBottom ? emailTemplate.globalCss.paddingBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="emailTemplate.globalCss.paddingBottom" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.fontFamily }}</h4><div class="property"><vselect name="page_editor_font_family" id="page-editor-font-family" :i18n="i18n" :data="getFontFamiliesForSelect2" :template-result="fontFamilyTemplate"></vselect></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.fontSize }} <span class="property-value">{{ emailTemplate.globalCss.fontSize ? emailTemplate.globalCss.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="emailTemplate.globalCss.fontSize" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.color }} <span class="property-value">{{ emailTemplate.globalCss.color ? emailTemplate.globalCss.color : \'#333\' }}</span></h4><div class="property"><colorpicker :color="emailTemplate.globalCss.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.link }} {{ i18n.color }} <span class="property-value">{{ linkColor }}</span></h4><div class="property"><colorpicker :color="linkColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.link }} {{ i18n.underline }}</h4><div class="property"><ul class="list-inline"><li><label><input type="radio" value="underline" v-model="emailTemplate.globalElementStyles.a.textDecoration">{{ i18n.underline }}</label></li><li><label><input type="radio" value="none" v-model="emailTemplate.globalElementStyles.a.textDecoration">{{ i18n.none }}</label></li></ul></div></div></div><div class="sidebar-bottom-btns"><button type="button" class="button" v-on:click="saveAndClose">{{ i18n.saveAndClose }}</button></div>';
  templates['design-editor'] = '<div class="sidebar-container"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.columns }}</h4><div class="property"><select class="form-control" v-model="section.rows[0].activeColumns"><option value="1">1</option><option value="2">2</option><option value="3">3</option></select></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ section.rowContainerStyle.backgroundColor ? section.rowContainerStyle.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="section.rowContainerStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.fontFamily }}</h4><div class="property"><vselect :name="\'page_editor_font_family_\' + _uid" id="page-editor-font-family" :i18n="i18n" :data="getFontFamiliesForSelect2" :template-result="fontFamilyTemplate"></vselect></div></div><div class="control-property"><h4 class="property-title clearfix reset-property">{{ i18n.fontSize }} <a :class="[\'inherit\' === section.rowContainerStyle.fontSize ? \'disabled\' : \'\']" href="#default" v-on:click="resetFontSize" data-content="restoreToDefault" v-tiptip=""><i class="fa fa-repeat"></i></a> <span class="property-value">{{ section.rowContainerStyle.fontSize }}</span></h4><div class="property"><input-range :model="sectionFontSize" :forced-synced="fontSizeForcedSynced" :model-name="\'sectionFontSize\'" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.color }} <span class="property-value">{{ sectionColor }}</span></h4><div class="property"><colorpicker :color="sectionColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ section.rowContainerStyle.paddingTop ? section.rowContainerStyle.paddingTop : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingTopBottom" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.left }}-{{ i18n.right }} <span class="property-value">{{ section.rowContainerStyle.paddingLeft ? section.rowContainerStyle.paddingLeft : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingLeftRight" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.margin }} {{ i18n.bottom }} <span class="property-value">{{ section.rowContainerStyle.marginBottom ? section.rowContainerStyle.marginBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="section.rowContainerStyle.marginBottom" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} {{ i18n.top }} <span class="property-value">{{ borderTopLabel }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="borderTopWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="borderTopColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} {{ i18n.bottom }} <span class="property-value">{{ borderBottomLabel }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="borderBottomWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="borderBottomColor"></colorpicker></td></tr></table></div></div></div><div class="sidebar-bottom-btns"><button type="button" class="button" v-on:click="saveAndClose">{{ i18n.saveAndClose }}</button></div>';
  templates['content-editor-text'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><div class="editor-list-tab" v-if="activeColumns > 1"><ul class="list-inline"><li v-for="$index in activeColumns" :class="[$index === currentColumn ? \'active\' : \'\']"><a href="#" v-on:click="setCurrentColumn($event, $index)">{{ i18n.column }} {{ $index + 1 }}</a></li></ul></div><div class="editor-column" v-for="$index in activeColumns"><text-editor v-if="$index === parseInt(currentColumn)" :content="contentEditor.contents.texts[$index]" :tinymce-settings="tinymceSettings"></text-editor></div></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.backgroundColor ? contentEditor.contents.style.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.color ? contentEditor.contents.style.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ contentEditor.contents.style.paddingTop ? contentEditor.contents.style.paddingTop : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingTopBottom" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.left }}-{{ i18n.right }} <span class="property-value">{{ contentEditor.contents.style.paddingLeft ? contentEditor.contents.style.paddingLeft : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingLeftRight" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.valign }} <span class="property-value">{{ contentEditor.contents.valign ? contentEditor.contents.valign : \'top\' }}</span></h4><div class="property"><ul class="list-inline"><li><label><input type="radio" value="top" v-model="contentEditor.contents.valign">{{ i18n.top }}</label></li><li><label><input type="radio" value="middle" v-model="contentEditor.contents.valign">{{ i18n.middle }}</label></li><li><label><input type="radio" value="bottom" v-model="contentEditor.contents.valign">{{ i18n.bottom }}</label></li><li><label><input type="radio" value="baseline" v-model="contentEditor.contents.valign">{{ i18n.baseline }}</label></li></ul></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.numOfColumns }} <span class="property-value">{{ contentEditor.contents.activeColumns }}</span></h4><div class="property"><select class="form-control" v-model="contentEditor.contents.activeColumns"><option value="1">1</option><option value="2">2</option></select></div></div><div class="control-property" v-if="activeColumns > 1"><h4 class="property-title clearfix">{{ i18n.columnSplit }}</h4><div class="property"><ul class="list-inline column-split-list"><li :class="[ \'1-1\' === contentEditor.contents.columnSplit ? \'active\' : \'\' ]"><a href="#" v-on:click="setColumnSplit($event, \'1-1\')"><span class="column-split split-1-1">&nbsp;</span></a></li><li :class="[ \'1-2\' === contentEditor.contents.columnSplit ? \'active\' : \'\' ]"><a href="#" v-on:click="setColumnSplit($event, \'1-2\')"><span class="column-split split-1-2">&nbsp;</span></a></li><li :class="[ \'2-1\' === contentEditor.contents.columnSplit ? \'active\' : \'\' ]"><a href="#" v-on:click="setColumnSplit($event, \'2-1\')"><span class="column-split split-2-1">&nbsp;</span></a></li></ul></div></div></div></div>';
  templates['content-editor-image'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><table class="image-editor-table fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr v-for="image in images"><td class="cell-3-1 image-preview">{{{ printImage(image) }}}</td><td class="cell-3-2"><div v-if="!image.image"><h3><strong>{{ i18n.uploadAnImage }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" class="image-editor-upload-btn" v-on:click="browseImage($event, $index)">{{ i18n.browseImage }}</a></li><li v-if="images.length > 2"><a href="#remove" v-on:click="removeImage($event, $index)">{{ i18n.remove }}</a></li></ul></div><div v-else=""><h3><strong>{{ image.alt }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" v-on:click="browseImage($event, $index)">{{ i18n.replace }}</a></li><li><a href="#link" v-on:click="openAttrEditor($event, $index, \'link\')">{{ i18n.link }}</a></li><li><a href="#alt" v-on:click="openAttrEditor($event, $index, \'alt\')">{{ i18n.alt }}</a></li><li><a href="#width" v-on:click="openAttrEditor($event, $index, \'width\')">{{ i18n.width }}</a></li><li v-if="images.length > 2"><a href="#remove" v-on:click="removeImage($event, $index)">{{ i18n.remove }}</a></li></ul><div class="image-attr-editor" v-if="image.openAttrEditor == \'link\'"><strong>{{ i18n.setImageLink }}</strong><input class="form-control" type="text" v-model="image.link" autofocus><label><small>{{ i18n.openLinkInNewWindow }}<input type="checkbox" v-model="image.openLinkInNewWindow"></small></label><p><button type="button" class="button button-small" v-on:click="image.openAttrEditor = \'\'">{{ i18n.close }}</button></p></div><div class="image-attr-editor" v-if="image.openAttrEditor == \'alt\'"><strong>{{ i18n.setImageAltText }}</strong><input class="form-control" type="text" v-model="image.alt" autofocus><p><button type="button" class="button button-small" v-on:click="image.openAttrEditor = \'\'">{{ i18n.close }}</button></p></div><div class="image-attr-editor" v-if="image.openAttrEditor == \'width\'"><input-range :model="contentEditor.contents.widths[$index]" :min="0" :max="600"></input-range></div></div></td></tr><tr v-if="group && images.length < 3"><td colspan="2"><button type="button" class="button button-primary button-block" v-on:click="addNewImage">{{ i18n.addMoreImage }}</button></td></tr></table></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.backgroundColor }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ contentEditor.contents.style.padding ? contentEditor.contents.style.padding : \'0px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.padding" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.image }} {{ i18n.align }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === contentEditor.contents.style.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.style.textAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === contentEditor.contents.style.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.style.textAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === contentEditor.contents.style.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.style.textAlign = \'right\'"><i class="fa fa-align-right"></i></button></div></div></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property" v-if="!group"><h4 class="padding-left-15"><label class="fix-checkbox"><input type="checkbox" value="true" v-model="edgeToEdge">&nbsp;{{ i18n.edgeToEdge }}</label></h4></div><div class="control-property" v-if="group"><h4 class="property-title clearfix">{{ i18n.imageLayout }}</h4><div class="property"><ul class="list-inline column-layout-list" v-if="images.length === 2"><li :class="[ \'r1-r1\' === contentEditor.contents.layout ? \'active\' : \'\' ]"><a href="#" v-on:click="setLayout($event, \'r1-r1\')"><span class="column-layout layout-r1-r1">&nbsp;</span></a></li><li :class="[ \'r1-r2\' === contentEditor.contents.layout ? \'active\' : \'\' ]"><a href="#" v-on:click="setLayout($event, \'r1-r2\')"><span class="column-layout layout-r1-r2">&nbsp;</span></a></li></ul><ul class="list-inline column-layout-list" v-if="images.length === 3"><li :class="[ \'r1-r2-r2\' === contentEditor.contents.layout ? \'active\' : \'\' ]"><a href="#" v-on:click="setLayout($event, \'r1-r2-r2\')"><span class="column-layout layout-r1-r2-r2">&nbsp;</span></a></li><li :class="[ \'r1-r1-r2\' === contentEditor.contents.layout ? \'active\' : \'\' ]"><a href="#" v-on:click="setLayout($event, \'r1-r1-r2\')"><span class="column-layout layout-r1-r1-r2">&nbsp;</span></a></li><li :class="[ \'r1-r2-r3\' === contentEditor.contents.layout ? \'active\' : \'\' ]"><a href="#" v-on:click="setLayout($event, \'r1-r2-r3\')"><span class="column-layout layout-r1-r2-r3">&nbsp;</span></a></li></ul></div></div></div></div>';
  templates['content-editor-image-caption'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><div class="editor-list-tab" v-if="activeColumns > 1"><ul class="list-inline"><li v-for="$index in activeColumns" :class="[$index === currentColumn ? \'active\' : \'\']"><a href="#" v-on:click="setCurrentColumn($event, $index)">{{ i18n.caption }} {{ $index + 1 }}</a></li></ul></div><div v-for="group in groups" track-by="$index"><table v-if="$index === currentColumn" class="image-editor-table fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="cell-3-1 image-preview">{{{ printImage(group.image) }}}</td><td class="cell-3-2"><div v-if="!group.image.image"><h3><strong>{{ i18n.uploadAnImage }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" class="image-editor-upload-btn" v-on:click="browseImage($event, $index)">{{ i18n.browseImage }}</a></li></ul></div><div v-else=""><h3><strong>{{ group.image.alt }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" v-on:click="browseImage($event, $index)">{{ i18n.replace }}</a></li><li><a href="#link" v-on:click="openAttrEditor($event, $index, \'link\')">{{ i18n.link }}</a></li><li><a href="#alt" v-on:click="openAttrEditor($event, $index, \'alt\')">{{ i18n.alt }}</a></li></ul><div class="image-attr-editor" v-if="group.image.openAttrEditor == \'link\'"><strong>{{ i18n.setImageLink }}</strong><input class="form-control" type="text" v-model="group.image.link"><label><small>{{ i18n.openLinkInNewWindow }}<input type="checkbox" v-model="group.image.openLinkInNewWindow"></small></label><p><button type="button" class="button button-small" v-on:click="group.image.openAttrEditor = \'\'">{{ i18n.close }}</button></p></div><div class="image-attr-editor" v-if="group.image.openAttrEditor == \'alt\'"><strong>{{ i18n.setImageAltText }}</strong><input class="form-control" type="text" v-model="group.image.alt"><p><button type="button" class="button button-small" v-on:click="group.image.openAttrEditor = \'\'">{{ i18n.close }}</button></p></div></div></td></tr><tr><td colspan="2" class="no-padding"><text-editor :content="group.text" :tinymce-settings="tinymceSettings"></text-editor></td></tr></table></div></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.backgroundColor }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.color ? contentEditor.contents.style.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ contentEditor.contents.style.fontSize ? contentEditor.contents.style.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.fontSize" :min="10" :max="80"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.align }} <span class="property-value">{{ contentEditor.contents.style.textAlign ? classifyStr(contentEditor.contents.style.textAlign) : \'Left\' }}</span></h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" class="button" v-on:click="setTextAlign(\'left\')"><i class="fa fa-align-left"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'center\')"><i class="fa fa-align-center"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'right\')"><i class="fa fa-align-right"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'justify\')"><i class="fa fa-align-justify"></i></button></div></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ paddingTopBottom ? paddingTopBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingTopBottom" :min="0" :max="50"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.left }}-{{ i18n.right }} <span class="property-value">{{ paddingLeftRight ? paddingLeftRight : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingLeftRight" :min="0" :max="150"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.numberOfImages }} <span class="property-value">{{ contentEditor.contents.activeColumns }}</span></h4><div class="property"><select class="form-control" v-model="contentEditor.contents.activeColumns"><option value="1">1</option><option value="2">2</option></select></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.captionPosition }} <span class="property-value">{{ i18n[contentEditor.contents.capPosition] }}</span></h4><div class="property"><select class="form-control" v-model="contentEditor.contents.capPosition"><option value="top">{{ i18n.top }}</option><option value="bottom">{{ i18n.bottom }}</option><option value="left">{{ i18n.left }}</option><option value="right">{{ i18n.right }}</option></select></div></div></div></div>';
  templates['content-editor-button'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.button }} {{ i18n.text }}</h4><div class="property"><input type="text" class="form-control" v-model="contentEditor.contents.text"></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.button }} {{ i18n.link }}</h4><div class="property"><input type="text" class="form-control" v-model="contentEditor.contents.link"></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.title }} {{ i18n.attribute }}</h4><div class="property"><input type="text" class="form-control" v-model="contentEditor.contents.title"></div></div></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.backgroundColor ? contentEditor.contents.style.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.color ? contentEditor.contents.style.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ contentEditor.contents.style.fontSize ? contentEditor.contents.style.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.fontSize" :min="10" :max="80"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ paddingTopBottom ? paddingTopBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingTopBottom" :min="8" :max="50"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.left }}-{{ i18n.right }} <span class="property-value">{{ paddingLeftRight ? paddingLeftRight : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingLeftRight" :min="8" :max="150"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} {{ i18n.radius }} <span class="property-value">{{ contentEditor.contents.style.borderRadius ? contentEditor.contents.style.borderRadius : \'3px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.borderRadius" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.upperCase }}</h4><div class="property"><label class="fix-checkbox"><input type="checkbox" v-model="uppercase">{{ i18n.yes }}</label></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.margin }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ marginTopBottom ? marginTopBottom : \'15px\' }}</span></h4><div class="property"><input-range :model="marginTopBottom" :min="0" :max="30"></input-range></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.width }}</h4><div class="property"><ul class="list-inline"><li><label class="fix-radio"><input type="radio" value="default" v-model="buttonWidth">{{ i18n.default }}</label><label class="fix-radio"><input type="radio" value="block" v-model="buttonWidth">{{ i18n.block }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.container }} {{ i18n.background }} <span class="property-value">{{ contentEditor.contents.containerStyle.backgroundColor ? contentEditor.contents.containerStyle.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.containerStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.align }} <span class="property-value">{{ contentEditor.contents.containerStyle.textAlign ? classifyStr(contentEditor.contents.containerStyle.textAlign) : \'Left\' }}</span></h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === contentEditor.contents.containerStyle.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerStyle.textAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === contentEditor.contents.containerStyle.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerStyle.textAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === contentEditor.contents.containerStyle.textAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerStyle.textAlign = \'right\'"><i class="fa fa-align-right"></i></button></div></div></div></div></div>';
  templates['content-editor-social-follow'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><div class="editor-social-icons" v-for="icon in contentEditor.contents.icons"><div class="icon-selector clearfix"><div class="selector-icon alignleft"><img :src="imageUrls[icon.site]" alt=""></div><div class="selector-list alignleft"><p><vselect name="icon_dropdown" id="icon-dropdown-{{ $index }}" :i18n="i18n" :data="iconsDropdowns[$index]" :width="\'210px\'"></vselect><a v-if="contentEditor.contents.icons.length > 1" href="#remove" class="remove-icon" v-on:click="removeService($event, $index)"><i class="fa fa-minus"></i></a></p><p><label><strong>{{ i18n.page }} {{ i18n.link }}</strong><input type="text" class="form-control" v-model="icon.link"></label></p><p><label><strong>{{ i18n.link }} {{ i18n.text }}</strong><input type="text" class="form-control" v-model="icon.text"></label></p></div></div></div><div class="editor-social-icons"><button type="button" class="button button-primary button-block" v-on:click="addNewService">{{ i18n.addMoreService }}</button></div></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.backgroundColor ? contentEditor.contents.style.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.color ? contentEditor.contents.style.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ contentEditor.contents.style.fontSize ? contentEditor.contents.style.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.fontSize" :min="10" :max="30"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.icon }} {{ i18n.margin }} <span class="property-value">{{ contentEditor.contents.iconMargin ? contentEditor.contents.iconMargin : \'14px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.iconMargin" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ contentEditor.contents.style.padding ? contentEditor.contents.style.padding : \'0px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.padding" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.upperCase }}</h4><div class="property"><label class="fix-checkbox"><input type="checkbox" v-model="uppercase">{{ i18n.yes }}</label></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.weight }}</h4><div class="property"><ul class="list-inline"><li><label class="fix-radio"><input type="radio" value="normal" v-model="contentEditor.contents.style.fontWeight">{{ i18n.normal }}</label></li><li><label class="fix-radio"><input type="radio" value="bold" v-model="contentEditor.contents.style.fontWeight">{{ i18n.bold }}</label></li></ul></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.display }}</h4><div class="property"><select class="form-control" v-model="contentEditor.contents.display" v-on:change="switchDisplayMode"><option value="icon">{{ i18n.iconOnly }}</option><option value="text">{{ i18n.textOnly }}</option><option value="both">{{ i18n.bothIconAndText }}</option></select></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.align }} <span class="property-value">{{ contentEditor.contents.containerAlign ? classify(contentEditor.contents.containerAlign) : \'Center\' }}</span></h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === contentEditor.contents.containerAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === contentEditor.contents.containerAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === contentEditor.contents.containerAlign ? \'active\' : \'\']" v-on:click="contentEditor.contents.containerAlign = \'right\'"><i class="fa fa-align-right"></i></button></div></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.layout }}</h4><div class="property"><ul class="list-inline social-follow-layout social-follow-layout-both" v-if="\'both\' === contentEditor.contents.display"><li :class="[isLayoutActive(\'verticle\', \'default\') ? \'active\' : \'\']"><a class="verticle-default" href="#" v-on:click="setLayout($event, \'verticle\', \'default\')"><span>&nbsp;</span></a></li><li :class="[isLayoutActive(\'horizontal\', \'default\') ? \'active\' : \'\']"><a class="horizontal-default" href="#" v-on:click="setLayout($event, \'horizontal\', \'default\')"><span>&nbsp;</span></a></li></ul><ul class="list-inline social-follow-layout social-follow-layout-both" v-if="\'both\' === contentEditor.contents.display"><li :class="[isLayoutActive(\'verticle\', \'large\') ? \'active\' : \'\']"><a class="verticle-large" href="#" v-on:click="setLayout($event, \'verticle\', \'large\')"><span>&nbsp;</span></a></li><li :class="[isLayoutActive(\'horizontal\', \'large\') ? \'active\' : \'\']"><a class="horizontal-large" href="#" v-on:click="setLayout($event, \'horizontal\', \'large\')"><span>&nbsp;</span></a></li></ul><ul class="list-inline social-follow-layout social-follow-layout-icon" v-if="\'icon\' === contentEditor.contents.display"><li :class="[isLayoutActive(\'verticle\', \'default\') ? \'active\' : \'\']"><a class="verticle-default" href="#" v-on:click="setLayout($event, \'verticle\', \'default\')"><span>&nbsp;</span></a></li><li :class="[isLayoutActive(\'horizontal\', \'default\') ? \'active\' : \'\']"><a class="horizontal-default" href="#" v-on:click="setLayout($event, \'horizontal\', \'default\')"><span>&nbsp;</span></a></li></ul><ul class="list-inline social-follow-layout social-follow-layout-icon" v-if="\'icon\' === contentEditor.contents.display"><li :class="[isLayoutActive(\'verticle\', \'large\') ? \'active\' : \'\']"><a class="verticle-large" href="#" v-on:click="setLayout($event, \'verticle\', \'large\')"><span>&nbsp;</span></a></li><li :class="[isLayoutActive(\'horizontal\', \'large\') ? \'active\' : \'\']"><a class="horizontal-large" href="#" v-on:click="setLayout($event, \'horizontal\', \'large\')"><span>&nbsp;</span></a></li></ul><ul class="list-inline social-follow-layout social-follow-layout-text" v-if="\'text\' === contentEditor.contents.display"><li :class="[isLayoutActive(\'verticle\', \'default\') ? \'active\' : \'\']"><a class="verticle-default" href="#" v-on:click="setLayout($event, \'verticle\', \'default\')"><span>&nbsp;</span></a></li><li :class="[isLayoutActive(\'horizontal\', \'default\') ? \'active\' : \'\']"><a class="horizontal-default" href="#" v-on:click="setLayout($event, \'horizontal\', \'default\')"><span>&nbsp;</span></a></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.iconStyle }}</h4><div class="property"><ul class="list-inline text-center"><li v-for="(style, variants) in socialIcons.iconTypes"><label class="fix-radio"><input type="radio" value="{{ style }}" v-model="iconStyle">{{ i18n[style] }}</label></li></ul><hr></div></div><div class="control-property"><div class="property"><ul class="variant-list text-center"><li v-for="variant in socialIcons.iconTypes[iconStyle]"><a href="#" v-on:click="setVariant($event, variant)"><ul class="list-inline editor-social-icon-list" :style="{ backgroundColor: iconBG[iconStyle][variant] }"><li v-for="site in [\'facebook\', \'twitter\', \'googleplus\', \'instagram\', \'youtube\', \'link\']"><img :src="pluginUrl + \'/assets/images/social-icons/\' + iconStyle + \'-\' + variant + \'-\' + site + \'.png\'"></li></ul></a></li></ul></div></div></div></div>';
  templates['content-editor-divider'] = '<div class="sidebar-container"><div class="editor-tab-content" v-if="\'content\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.dividerType }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'line\' === dividerType ? \'active\' : \'\']" v-on:click="dividerType = \'line\'">{{ i18n.line }}</button> <button type="button" :class="[\'button\', \'image\' === dividerType ? \'active\' : \'\']" v-on:click="dividerType = \'image\'">{{ i18n.image }}</button></div></div></div><div class="control-divider" v-if="\'line\' === dividerType"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.height }} <span class="property-value">{{ contentEditor.contents.style.borderTopWidth ? contentEditor.contents.style.borderTopWidth : \'1px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.borderTopWidth" :min="1" :max="30"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.width }} <span class="property-value">{{ contentEditor.contents.style.width ? contentEditor.contents.style.width : \'600px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.width" :min="100" :max="600"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.color }} <span class="property-value">{{ contentEditor.contents.style.borderTopColor ? contentEditor.contents.style.borderTopColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.style.borderTopColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.style }}</h4><div class="property"><ul class="border-style-switcher"><li v-for="style in borderStyles" :class="[style === contentEditor.contents.style.borderTopStyle ? \'active\': \'\']"><a href="#switch" class="border-style-switcher-style clearfix" v-on:click="setBorderStyle($event, style)">{{ style | classify }} <span :style="{borderTopStyle: style}">&nbsp;</span></a></li></ul></div></div></div><div class="control-divider-image" v-else=""><div v-if="!displayGallery"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.image }}<ul class="list-inline-dots property-value"><li><a href="#gallery" v-on:click="setDisplayGallery">{{ i18n.gallery }}</a></li><li><a href="#upload" v-on:click="browseImage">{{ i18n.browse }}</a></li></ul></h4><div class="property"><img :src="previewImage"></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.width }} <span class="property-value">{{ contentEditor.contents.image.style.width ? contentEditor.contents.image.style.width : \'600px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.image.style.width" :min="50" :max="600"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.height }} <span class="property-value">{{ contentEditor.contents.image.style.height ? contentEditor.contents.image.style.height : \'30px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.image.style.height" :min="1" :max="100"></input-range></div></div></div><div class="control-property" v-else=""><h4 class="property-title clearfix">{{ i18n.chooseDivider }}<ul class="list-inline-dots property-value"><li><a href="#gallery" v-on:click="hideGallery">{{ i18n.cancel }}</a></li></ul></h4><div class="property"><ul class="list-image"><li v-for="imageName in customizerData.dividers.images"><a href="#select" v-on:click="selectPresetDivider($event, imageName)" v-on:mouseover="setTempDivider($event, imageName)" v-on:mouseout="resetTempDivider"><img :src="presetImages[$index]"></a></li></ul></div></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.containerStyle.backgroundColor ? contentEditor.contents.containerStyle.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.containerStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.margin }} <span class="property-value">{{ marginTopBottom ? marginTopBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="marginTopBottom" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ paddingTopBottom ? paddingTopBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="paddingTopBottom" :min="0" :max="100"></input-range></div></div></div></div>';
  templates['content-editor-wp-posts'] = '<div class="sidebar-container"><div class="editor-tab-content" id="editor-wp-posts-editor-tab" v-if="\'content\' === contentEditor.tab"><div v-if="!isLatestPosts" class="control-property"><div class="property"><select class="form-control" id="editor-wp-posts-post-type"></select></div><div class="property"><select class="form-control" id="editor-wp-posts-tax-terms"><option></option></select></div><div class="property"><select class="form-control" id="editor-wp-posts-status"><option></option><option value="publish" selected>{{ i18n.publish }}</option><option value="draft">{{ i18n.draft }}</option><option value="pending">{{ i18n.pending }}</option><option value="future">{{ i18n.future }}</option><option value="private">{{ i18n.private }}</option></select></div><div class="property"><input type="search" class="form-control" v-model="search" placeholder="Search"></div><div class="property"><table v-if="posts.length" class="wp-list-table widefat fixed striped wp-posts-editor-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr v-for="post in posts"><td class="select-post"><input type="checkbox" id="wp-selected-post-{{ $index }}" value="{{ post.postId }}" v-model="checkedPosts"></td><td class="post-title"><label v-on:click="triggerClickCheckbox($index)">{{ post.title }}<br><small><em>{{ post.postType }}</em> - <em>{{ i18n[post.postStatus] }}</em></small></label></td></tr></table></div><div class="property clearfix"><button type="button" class="button button-primary" v-if="checkedPosts.length" v-on:click="insertPosts">{{ i18n.insertSelected }}</button> <button type="button" class="alignright button button-primary" v-if="showLoadMore" v-on:click="getPagedPosts">{{ i18n.loadMore }}</button></div></div><div v-else=""><div class="property"><select class="form-control" id="editor-wp-posts-post-type"></select></div><div class="property"><select class="form-control" id="editor-wp-posts-tax-terms" multiple><option></option></select></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.maxPostsToShow }} <span class="property-value">{{ maxPostsToShow }}</span></h4><div class="property text-center"><input-range :model="maxPostsToShow" :min="1" :max="100" no-px="true"></input-range></div></div><div class="property clearfix"><button type="button" class="alignright button button-primary" v-on:click="insertLatestPosts">{{ i18n.done }}</button></div></div></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="editor-list-tab"><ul class="list-inline"><li v-for="tab in styleTabs" :class="[tab === currentStyleTab ? \'active\' : \'\']"><a href="#" v-on:click="setStyleTab($event, tab)">{{ i18n[tab] }}</a></li></ul></div><div v-if="\'title\' === currentStyleTab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ titleStyle.fontSize ? titleStyle.fontSize : \'30px\' }}</span></h4><div class="property"><input-range :model="titleStyle.fontSize" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.color }} <span class="property-value">{{ titleStyle.color ? titleStyle.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="titleStyle.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.align }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === titleStyle.textAlign ? \'active\' : \'\']" v-on:click="titleStyle.textAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === titleStyle.textAlign ? \'active\' : \'\']" v-on:click="titleStyle.textAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === titleStyle.textAlign ? \'active\' : \'\']" v-on:click="titleStyle.textAlign = \'right\'"><i class="fa fa-align-right"></i></button></div></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.weight }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" v-model="titleStyle.fontWeight" value="normal">{{ i18n.normal }}</label></li><li><label class="fix-radio"><input type="radio" v-model="titleStyle.fontWeight" value="bold">{{ i18n.bold }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.upperCase }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" v-model="titleStyle.textTransform" value="upperCase">{{ i18n.upperCase }}</label></li><li><label class="fix-radio"><input type="radio" v-model="titleStyle.textTransform" value="none">{{ i18n.no }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.line }} {{ i18n.height }} <span class="property-value">{{ titleContainerStyle.lineHeight ? titleContainerStyle.lineHeight : \'30px\' }}</span></h4><div class="property"><input-range :model="titleContainerStyle.lineHeight" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.bottom }} {{ i18n.margin }} <span class="property-value">{{ titleContainerStyle.marginBottom ? titleContainerStyle.marginBottom : \'10px\' }}</span></h4><div class="property"><input-range :model="titleContainerStyle.marginBottom" :min="0" :max="100"></input-range></div></div></div><div v-if="\'image\' === currentStyleTab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ imageStyle.backgroundColor ? imageStyle.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="imageStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ imageStyle.padding ? imageStyle.padding : \'0px\' }}</span></h4><div class="property"><input-range :model="imageStyle.padding" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ imageStyle.borderWidth ? imageStyle.borderWidth : \'0px\' }} &nbsp; {{ imageStyle.borderColor ? imageStyle.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="imageStyle.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="imageStyle.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.bottom }} {{ i18n.margin }} <span class="property-value">{{ imageStyle.marginBottom ? imageStyle.marginBottom : \'10px\' }}</span></h4><div class="property"><input-range :model="imageStyle.marginBottom" :min="0" :max="100"></input-range></div></div></div><div v-if="\'content\' === currentStyleTab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ contentStyle.fontSize ? contentStyle.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="contentStyle.fontSize" :min="10" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.color }} <span class="property-value">{{ contentStyle.color ? contentStyle.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentStyle.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.align }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === contentStyle.textAlign ? \'active\' : \'\']" v-on:click="contentStyle.textAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === contentStyle.textAlign ? \'active\' : \'\']" v-on:click="contentStyle.textAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === contentStyle.textAlign ? \'active\' : \'\']" v-on:click="contentStyle.textAlign = \'right\'"><i class="fa fa-align-right"></i></button> <button type="button" :class="[\'button\', \'justify\' === contentStyle.textAlign ? \'active\' : \'\']" v-on:click="contentStyle.textAlign = \'justify\'"><i class="fa fa-align-justify"></i></button></div></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.bottom }} {{ i18n.margin }} <span class="property-value">{{ contentContainerStyle.marginBottom ? contentContainerStyle.marginBottom : \'10px\' }}</span></h4><div class="property"><input-range :model="contentContainerStyle.marginBottom" :min="0" :max="100"></input-range></div></div></div><div v-if="\'button\' === currentStyleTab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.display }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" value="display" v-model="contentEditor.contents.readMore.show">{{ i18n.display }}</label></li><li><label class="fix-radio"><input type="radio" value="hide" v-model="contentEditor.contents.readMore.show">{{ i18n.hide }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.button }} {{ i18n.text }}</h4><div class="property"><input type="text" class="form-control" v-model="contentEditor.contents.readMore.text"></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.align }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'left\' === readMoreContainerStyle.textAlign ? \'active\' : \'\']" v-on:click="readMoreContainerStyle.textAlign = \'left\'"><i class="fa fa-align-left"></i></button> <button type="button" :class="[\'button\', \'center\' === readMoreContainerStyle.textAlign ? \'active\' : \'\']" v-on:click="readMoreContainerStyle.textAlign = \'center\'"><i class="fa fa-align-center"></i></button> <button type="button" :class="[\'button\', \'right\' === readMoreContainerStyle.textAlign ? \'active\' : \'\']" v-on:click="readMoreContainerStyle.textAlign = \'right\'"><i class="fa fa-align-right"></i></button></div></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.background }} {{ i18n.color }} <span class="property-value">{{ readMoreStyle.backgroundColor ? readMoreStyle.backgroundColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="readMoreStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ readMoreStyle.color ? readMoreStyle.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="readMoreStyle.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ readMoreStyle.fontSize ? readMoreStyle.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="readMoreStyle.fontSize" :min="10" :max="80"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.top }}-{{ i18n.bottom }} <span class="property-value">{{ readMorePaddingTopBottom ? readMorePaddingTopBottom : \'0px\' }}</span></h4><div class="property"><input-range :model="readMorePaddingTopBottom" :min="0" :max="50"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} {{ i18n.left }}-{{ i18n.right }} <span class="property-value">{{ readMorePaddingLeftRight ? readMorePaddingLeftRight : \'0px\' }}</span></h4><div class="property"><input-range :model="readMorePaddingLeftRight" :min="0" :max="150"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ readMoreStyle.borderWidth ? readMoreStyle.borderWidth : \'0px\' }} &nbsp; {{ readMoreStyle.borderColor ? readMoreStyle.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="readMoreStyle.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="readMoreStyle.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} {{ i18n.radius }} <span class="property-value">{{ readMoreStyle.borderRadius ? readMoreStyle.borderRadius : \'3px\' }}</span></h4><div class="property"><input-range :model="readMoreStyle.borderRadius" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.upperCase }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" v-model="readMoreStyle.textTransform" value="upperCase">{{ i18n.upperCase }}</label></li><li><label class="fix-radio"><input type="radio" v-model="readMoreStyle.textTransform" value="none">{{ i18n.no }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.underline }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" v-model="readMoreStyle.textDecoration" value="underline">{{ i18n.underline }}</label></li><li><label class="fix-radio"><input type="radio" v-model="readMoreStyle.textDecoration" value="none">{{ i18n.no }}</label></li></ul></div></div></div><div v-if="\'divider\' === currentStyleTab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.dividerType }}</h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" :class="[\'button\', \'line\' === dividerType ? \'active\' : \'\']" v-on:click="dividerType = \'line\'">{{ i18n.line }}</button> <button type="button" :class="[\'button\', \'image\' === dividerType ? \'active\' : \'\']" v-on:click="dividerType = \'image\'">{{ i18n.image }}</button></div></div></div><div class="control-divider" v-if="\'line\' === dividerType"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.height }} <span class="property-value">{{ dividerStyle.borderTopWidth ? dividerStyle.borderTopWidth : \'1px\' }}</span></h4><div class="property"><input-range :model="dividerStyle.borderTopWidth" :min="1" :max="30"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.width }} <span class="property-value">{{ dividerStyle.width ? dividerStyle.width : \'580px\' }}</span></h4><div class="property"><input-range :model="dividerStyle.width" :min="100" :max="580"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.color }} <span class="property-value">{{ dividerStyle.borderTopColor ? dividerStyle.borderTopColor : \'######\' }}</span></h4><div class="property"><colorpicker :color="dividerStyle.borderTopColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.style }}</h4><div class="property"><ul class="border-style-switcher"><li v-for="style in borderStyles" :class="[style === dividerStyle.borderTopStyle ? \'active\': \'\']"><a href="#switch" class="border-style-switcher-style clearfix" v-on:click="setBorderStyle($event, style)">{{ style | classify }} <span :style="{borderTopStyle: style}">&nbsp;</span></a></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.hideDivider }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" v-model="contentEditor.contents.divider.display" value="show">{{ i18n.no }}</label></li><li><label class="fix-radio"><input type="radio" v-model="contentEditor.contents.divider.display" value="hide">{{ i18n.yes }}</label></li></ul></div></div></div><div v-else="" class="control-divider-image"><div v-if="!displayGallery"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.image }}<ul class="list-inline-dots property-value"><li><a href="#gallery" v-on:click="setDisplayGallery">{{ i18n.gallery }}</a></li><li><a href="#upload" v-on:click="browseImage">{{ i18n.browse }}</a></li></ul></h4><div class="property"><img :src="previewImage"></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.width }} <span class="property-value">{{ dividerImageStyle.width ? dividerImageStyle.width : \'600px\' }}</span></h4><div class="property"><input-range :model="dividerImageStyle.width" :min="50" :max="600"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.height }} <span class="property-value">{{ dividerImageStyle.height ? dividerImageStyle.height : \'30px\' }}</span></h4><div class="property"><input-range :model="dividerImageStyle.height" :min="1" :max="100"></input-range></div></div></div><div class="control-property" v-else=""><h4 class="property-title clearfix">{{ i18n.chooseDivider }}<ul class="list-inline-dots property-value"><li><a href="#gallery" v-on:click="hideGallery">{{ i18n.cancel }}</a></li></ul></h4><div class="property"><ul class="list-image"><li v-for="imageName in customizerData.dividers.images"><a href="#select" v-on:click="selectPresetDivider($event, imageName)" v-on:mouseover="setTempDivider($event, imageName)" v-on:mouseout="resetTempDivider"><img :src="presetImages[$index]"></a></li></ul></div></div></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ contentEditor.contents.style.padding ? contentEditor.contents.style.padding : \'0px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.padding" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.display }}</h4><div class="property"><ul class="list-inline text-center"><li><label class="fix-radio"><input type="radio" value="excerpt" v-model="contentEditor.contents.postContent.length">{{ i18n.excerpt }}</label></li><li><label class="fix-radio"><input type="radio" value="full" v-model="contentEditor.contents.postContent.length">{{ i18n.fullPost }}</label></li><li><label class="fix-radio"><input type="radio" value="title_and_image" v-model="contentEditor.contents.postContent.length">{{ i18n.titleAndImage }}</label></li></ul></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.column }}</h4><div class="property"><select v-model="contentEditor.contents.column" class="form-control" v-on:change="setLayout($event, \'t1-i2-c3\')"><option value="1">1</option><option value="2">2</option></select></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.layout }}</h4><div class="property"><ul class="list-inline wp-post-layout"><li v-for="layout in layouts" :class="[ (layout === contentEditor.contents.layout) ? \'active\' : \'\' ]"><a href="#set-layout" v-on:click="setLayout($event, layout)" class="layout-{{ layout }}">&nbsp;</a></li></ul></div></div></div></div>';
  templates['content-editor-video'] = '<div class="sidebar-container"><div class="editor-tab-content" id="editor-video-editor-tab" v-if="\'content\' === contentEditor.tab"><table class="image-editor-table fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="video-link" colspan="2"><h3>Video URL</h3><input type="text" class="form-control" v-model="contentEditor.contents.video.link"><p class="text-fade">{{{ i18n.videoLinkTips }}}</p><p class="text-error" v-if="showLinkError">{{ i18n.videoNoThumbError }}</p></td></tr></table><table class="image-editor-table fixed-layout-table" id="editor-video-img-text-tbl" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr v-if="isValidVideoLink"><td class="cell-3-1 image-preview">{{{ printImage(contentEditor.contents.video) }}}</td><td class="cell-3-2"><div v-if="!contentEditor.contents.video.image"><h3><strong>{{ i18n.uploadAnImage }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" class="image-editor-upload-btn" v-on:click="browseImage">{{ i18n.browseImage }}</a></li></ul></div><div v-else=""><h3><strong>{{ contentEditor.contents.video.alt }}</strong></h3><ul class="list-inline-dots"><li><a href="#upload" v-on:click="browseImage">{{ i18n.replace }}</a></li><li><a href="#alt" v-on:click="openAttrEditor($event, \'alt\')">{{ i18n.alt }}</a></li></ul><div class="image-attr-editor" v-if="contentEditor.contents.video.openAttrEditor == \'alt\'"><strong>{{ i18n.setImageAltText }}</strong><input class="form-control" type="text" v-model="contentEditor.contents.video.alt"><p><button type="button" class="button button-small" v-on:click="contentEditor.contents.video.openAttrEditor = \'\'">{{ i18n.close }}</button></p></div></div></td></tr><tr><td colspan="2" class="no-padding"><text-editor :content="contentEditor.contents.text" :tinymce-settings="tinymceSettings"></text-editor></td></tr></table></div><div class="editor-tab-content" v-if="\'style\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.padding }} <span class="property-value">{{ contentEditor.contents.style.padding ? contentEditor.contents.style.padding : \'0px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.style.padding" :min="0" :max="100"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.border }} <span class="property-value">{{ contentEditor.contents.style.borderWidth ? contentEditor.contents.style.borderWidth : \'0px\' }} &nbsp; {{ contentEditor.contents.style.borderColor ? contentEditor.contents.style.borderColor : \'######\' }}</span></h4><div class="property"><table class="table-default" cellspacing="0" cellpadding="0"><tr><td class="cell-3-1"><input-range :model="contentEditor.contents.style.borderWidth" :min="0" :max="10"></input-range></td><td class="cell-3-2 padding-left-15 colorpicker-grouped"><colorpicker :color="contentEditor.contents.style.borderColor"></colorpicker></td></tr></table></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.background }} <span class="property-value">{{ contentEditor.contents.textStyle.backgroundColor }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.textStyle.backgroundColor"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.color }} <span class="property-value">{{ contentEditor.contents.textStyle.color ? contentEditor.contents.textStyle.color : \'######\' }}</span></h4><div class="property"><colorpicker :color="contentEditor.contents.textStyle.color"></colorpicker></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.font }} {{ i18n.size }} <span class="property-value">{{ contentEditor.contents.textStyle.fontSize ? contentEditor.contents.textStyle.fontSize : \'14px\' }}</span></h4><div class="property"><input-range :model="contentEditor.contents.textStyle.fontSize" :min="10" :max="80"></input-range></div></div><div class="control-property"><h4 class="property-title clearfix">{{ i18n.text }} {{ i18n.align }} <span class="property-value">{{ contentEditor.contents.textStyle.textAlign ? classifyStr(contentEditor.contents.textStyle.textAlign) : \'Left\' }}</span></h4><div class="property text-center"><div class="text-align-button-group button-group"><button type="button" class="button" v-on:click="setTextAlign(\'left\')"><i class="fa fa-align-left"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'center\')"><i class="fa fa-align-center"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'right\')"><i class="fa fa-align-right"></i></button> <button type="button" class="button" v-on:click="setTextAlign(\'justify\')"><i class="fa fa-align-justify"></i></button></div></div></div></div><div class="editor-tab-content" v-if="\'settings\' === contentEditor.tab"><div class="control-property"><h4 class="property-title clearfix">{{ i18n.captionPosition }} <span class="property-value">{{ i18n[contentEditor.contents.capPosition] }}</span></h4><div class="property"><select class="form-control" v-model="contentEditor.contents.capPosition"><option value="top">{{ i18n.top }}</option><option value="bottom">{{ i18n.bottom }}</option><option value="left">{{ i18n.left }}</option><option value="right">{{ i18n.right }}</option></select></div></div></div></div>';
  templates['content-text'] = '<table class="content-table content-text" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td :class="[\'content-table-column\', columnClasses[0]]" valign="{{ content.valign }}"><div class="content-table-content content-text-content" :style="content.style">{{{ content.texts[0] }}}</div></td><td v-if="activeColumns > 1" :class="[\'content-table-column\', columnClasses[1]]" valign="{{ content.valign }}"><div class="content-table-content content-text-content" :style="content.style">{{{ content.texts[1] }}}</div></td></tr></table>';
  templates['content-image'] = '<table v-if="!group" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-1-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td></tr></table><table v-if="group" class="content-image-group" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td><table v-if="\'r1-r1\' === layout" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-2-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td><td class="content-table-column cell-2-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(1, images[1]) }}}</div></td></tr></table><table v-if="\'r1-r2\' === layout" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-1-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td></tr><tr><td class="content-table-column cell-1-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(1, images[1]) }}}</div></td></tr></table><table v-if="\'r1-r2-r2\' === layout" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-1-1" colspan="2" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td></tr><tr><td class="content-table-column cell-2-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(1, images[1]) }}}</div></td><td class="content-table-column cell-2-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(2, images[2]) }}}</div></td></tr></table><table v-if="\'r1-r1-r2\' === layout" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-2-1" align="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td><td class="content-table-column cell-2-1" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(1, images[1]) }}}</div></td></tr><tr><td class="content-table-column cell-1-1" colspan="2" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(2, images[2]) }}}</div></td></tr></table><table v-if="\'r1-r2-r3\' === layout" class="content-table content-image" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column cell-1-1" colspan="2" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(0, images[0]) }}}</div></td></tr><tr><td class="content-table-column cell-1-1" colspan="2" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(1, images[1]) }}}</div></td></tr><tr><td class="content-table-column cell-1-1" colspan="2" valign="top"><div class="content-table-image-content" :style="content.style">{{{ printImage(2, images[2]) }}}</div></td></tr></table></td></tr></table>';
  templates['content-image-caption'] = '<table class="content-table content-image-caption fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td v-for="column in activeColumns" :class="[\'content-table-column\', classNames[$index]]" valign="top"><div class="content-table-image-caption" :style="content.style"><div v-if="\'top\' === content.capPosition" class="image-caption-container caption-top"><div class="image-caption-text">{{{ groups[$index].text }}}</div><div class="image-caption-image">{{{ printImage(groups[$index].image) }}}</div></div><div v-if="\'bottom\' === content.capPosition" class="image-caption-container caption-bottom"><div class="image-caption-image">{{{ printImage(groups[$index].image) }}}</div><div class="image-caption-text">{{{ groups[$index].text }}}</div></div><div v-if="\'left\' === content.capPosition" class="image-caption-container caption-left"><table class="content-table content-image-caption fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td valign="top"><div class="image-caption-text">{{{ groups[$index].text }}}</div></td><td valign="top"><div class="image-caption-image">{{{ printImage(groups[$index].image) }}}</div></td></tr></table></div><div v-if="\'right\' === content.capPosition" class="image-caption-container caption-right"><table class="content-table content-image-caption fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td valign="top"><div class="image-caption-image">{{{ printImage(groups[$index].image) }}}</div></td><td valign="top"><div class="image-caption-text">{{{ groups[$index].text }}}</div></td></tr></table></div></div></td></tr></table>';
  templates['content-button'] = '<table class="content-table content-button" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td valign="top"><div class="content-table-content" :style="content.containerStyle"><a :href="href" :class="classNames" :title="title" :style="content.style">{{ content.text }}</a></div></td></tr></table>';
  templates['content-social-follow'] = '<div class="content-table-column"><div class="content-table-social-follow" :style="content.style"><table border="0" cellpadding="0" cellspacing="0" :width="wrapperWidth" :align="wrapperAlign" :style="wrapperStyle"><tr><td valign="top" :align="containerAlign"><table v-for="icon in content.icons" :class="iconClasses" :style="containerTableStyles[$index]" border="0" cellpadding="0" cellspacing="0"><tr><td valign="middle" v-if="\'both\' === content.display || \'icon\' === content.display" class="image-container-td"><a href="{{ icon.link ? icon.link : \'#\' }}"><img :src="imageUrls[icon.site]" alt="{{ icon.text }}"></a></td><td valign="middle" v-if="\'both\' === content.display || \'text\' === content.display"><a href="{{ icon.link ? icon.link : \'#\' }}" :style="textCss">{{ icon.text }}</a></td></tr></table></td></tr></table></div></div>';
  templates['content-divider'] = '<div class="content-table-content" :style="content.containerStyle"><div v-if="!content.useImage" class="ecamp-hr" :style="content.style">&nbsp;</div><div v-else="" style="text-align: center"><img class="ecamp-hr-image" :src="content.image.image" alt="" :style="content.image.style"></div></div>';
  templates['content-wp-posts'] = '<div v-if="!content.postIds.length" class="wp-post-dummy"><p v-if="!isLatestPosts" class="text-center">{{ i18n.wordpressPosts }}</p><p v-else="" class="text-center">{{ i18n.autoLatestContent }}</p></div><div v-else="" :style="content.style"><table v-if="1 === parseInt(content.column)" class="content-table content-wp-post" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr v-for="post in postContents"><td class="content-table-column"><h2 v-if="showTitleOnTop" class="post-title" :style="content.title.container.style"><a :style="titleStyle" :href="post.url">{{ post.title }}</a></h2><div class="post-content"><a :href="post.url" v-if="showImage && post.image"><img :style="imageStyle" :src="post.image" alt="{{ post.image.alt }}"></a><h2 v-if="!showTitleOnTop" class="post-title" :style="content.title.container.style"><a :style="titleStyle" :href="post.url">{{ post.title }}</a></h2><article v-if="\'title_and_image\' !== content.postContent.length" :style="contentStyle"><div v-if="\'full\' === content.postContent.length" class="wp-post-content" :style="content.postContent.containerStyle">{{{ post.content }}}</div><div v-else="" class="wp-post-content" :style="content.postContent.containerStyle">{{{ post.excerpt }}}</div><p v-if="showAuthorAtBottom" class="post-author">Author: Author Name</p><p v-if="showCatAtBottom" class="post-categories">Categories: Cat1, Cat2</p><div v-if="\'display\' === content.readMore.show" :style="content.readMore.containerStyle"><a :href="post.url" class="read-more-btn" :style="content.readMore.style">{{ content.readMore.text }}</a></div></article></div><table v-if="\'show\' === divider.display" class="content-table content-wp-post-divider" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td><div :style="divider.containerStyle"><div v-if="!divider.useImage" class="ecamp-hr" :style="divider.style">&nbsp;</div><div v-else="" style="text-align: center"><img class="ecamp-hr-image" :src="divider.image.image" alt="" :style="divider.image.style"></div></div></td></tr></table></td></tr></table><table v-else="" class="content-table content-wp-post" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr v-for="group in twoColumnPostContents"><td v-if="group.length" v-for="post in group" class="content-table-column cell-2-1" valign="top" :style="twoColumnPadding[$index]"><h2 v-if="showTitleOnTop" class="post-title" :style="content.title.container.style"><a :style="titleStyle" :href="post.url">{{ post.title }}</a></h2><div class="post-content"><a :href="post.url" v-if="showImage && post.image"><img :style="imageStyle" :src="post.image" alt="{{ post.image.alt }}"></a><h2 v-if="!showTitleOnTop" class="post-title" :style="content.title.container.style"><a :style="titleStyle" :href="post.url">{{ post.title }}</a></h2><article v-if="\'title_and_image\' !== content.postContent.length" :style="contentStyle"><div v-if="\'full\' === content.postContent.length" class="wp-post-content" :style="content.postContent.containerStyle">{{{ post.content }}}</div><div v-else="" class="wp-post-content" :style="content.postContent.containerStyle">{{{ post.excerpt }}}</div><p v-if="showAuthorAtBottom" class="post-author">Author: Author Name</p><p v-if="showCatAtBottom" class="post-categories">Categories: Cat1, Cat2</p><div v-if="\'display\' === content.readMore.show" :style="content.readMore.containerStyle"><a :href="post.url" class="read-more-btn" :style="content.readMore.style">{{ content.readMore.text }}</a></div></article></div></td><td v-if="!group.length" colspan="2"><table class="content-table content-wp-post-divider" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td v-if="\'show\' === divider.display" class="content-table-column"><div :style="divider.containerStyle"><div v-if="!divider.useImage" class="ecamp-hr" :style="divider.style">&nbsp;</div><div v-else="" style="text-align: center"><img class="ecamp-hr-image" :src="divider.image.image" alt="" :style="divider.image.style"></div></div></td></tr></table></td></tr></table></div>';
  templates['content-video'] = '<table class="content-table content-video fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="content-table-column" valign="top"><div class="content-table-content-video" :style="content.style"><div v-if="\'top\' === content.capPosition" class="content-video-container caption-top"><div v-if="content.text" class="content-video-text" :style="content.textStyle">{{{ content.text }}}</div><div class="content-video-image">{{{ printImage(content.video) }}}</div></div><div v-if="\'bottom\' === content.capPosition" class="content-video-container caption-bottom"><div class="content-video-image">{{{ printImage(content.video) }}}</div><div v-if="content.text" class="content-video-text" :style="content.textStyle">{{{ content.text }}}</div></div><div v-if="\'left\' === content.capPosition" class="content-video-container caption-left"><table class="content-table content-video fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td valign="top"><div v-if="content.text" class="content-video-text" :style="content.textStyle">{{{ content.text }}}</div></td><td valign="top"><div class="content-video-image">{{{ printImage(content.video) }}}</div></td></tr></table></div><div v-if="\'right\' === content.capPosition" class="content-video-container caption-right"><table class="content-table content-video fixed-layout-table" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td valign="top"><div class="content-video-image">{{{ printImage(content.video) }}}</div></td><td valign="top"><div v-if="content.text" class="content-video-text" :style="content.textStyle">{{{ content.text }}}</div></td></tr></table></div></div></td></tr></table>';
  templates['content'] = '<div class="template-content"><content-text v-if="\'text\' === content.type" :content="content.content"></content-text><content-image v-if="\'image\' === content.type" :content="content.content" :dummy-image="customizerData.dummyImage"></content-image><content-image v-if="\'imageGroup\' === content.type" :content="content.content" :dummy-image="customizerData.dummyImage" :group="true"></content-image><content-image-caption v-if="\'imageCaption\' === content.type" :content="content.content" :dummy-image="customizerData.dummyImage" :default-text="customizerData.contentTypes.imageCaption.defaultText"></content-image-caption><content-button v-if="\'button\' === content.type" :content="content.content"></content-button><content-social-follow v-if="\'socialFollow\' === content.type" :content="content.content" :social-icons="customizerData.socialIcons" :plugin-url="customizerData.pluginURL"></content-social-follow><content-divider v-if="\'divider\' === content.type" :content="content.content" :dividers="customizerData.dividers"></content-divider><content-wp-posts v-if="\'wpPosts\' === content.type" :i18n="i18n" :content="content.content" :customizer-data="customizerData"></content-wp-posts><content-wp-posts v-if="\'wpLatestPosts\' === content.type" :i18n="i18n" :content="content.content" :customizer-data="customizerData" :is-latest-posts="true"></content-wp-posts><content-video v-if="\'video\' === content.type" :content="content.content" :dummy-video-image="customizerData.dummyVideoImage" :default-text="customizerData.contentTypes.video.text"></content-video><content-text v-if="\'footer\' === content.type" :content="content.content"></content-text></div>';
  templates['email-template'] = '<div id="email-template-container"><div v-if="!emailTemplate.text_only" id="email-template" :style="emailTemplate.globalCss"><div v-for="(secIndex, section) in emailTemplate.sections" track-by="$index" :class="[\'wrapper\', this.emailTemplate.sections[secIndex].title]" :style="section.style"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td align="center" valign="top"><div :style="fullWidthContainerStyle[secIndex]"><div style="max-width: 600px; margin: 0 auto"><table class="section-row" v-for="(rowIndex, row) in section.rows" track-by="$index" data-row-id="{{ secIndex }}-{{ rowIndex }}" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td class="section-row-container"><div :style="defaultContainerStyle[secIndex]"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tr><td v-for="columnIndex in parseInt(row.activeColumns)" track-by="$index" colspan="1" :class="[\'content-cell\', true ? getColumnClass(secIndex, columnIndex) : \'\', showDropZone(row, columnIndex) ? \'show-dropzone\' : \'\']" data-column-id="{{ secIndex }}-{{ rowIndex }}-{{ columnIndex }}" v-sortable-content=""><div class="content-container" v-for="(contentIndex, content) in row.columns[columnIndex].contents" track-by="$index" :style="content.style" data-type="{{ content.type }}" data-content-id="{{ secIndex }}-{{ rowIndex }}-{{ columnIndex }}-{{ contentIndex }}"><content :i18n="i18n" :content="content" :customizer-data="customizerData"></content><ul v-if="!isPreview" class="remove-el-on-save list-inline control-buttons"><li><p><i class="fa fa-arrows move" data-content="moveThisContent" v-tiptip=""></i> <i class="fa fa-pencil" data-content="editThisContent" v-tiptip="" v-on:click="openContentEditor(content.type, [secIndex, rowIndex, columnIndex, contentIndex])"></i> <i class="fa fa-clone" data-content="copyThisContent" v-tiptip="" v-on:click="cloneContent(content.type, [secIndex, rowIndex, columnIndex, contentIndex])"></i> <i class="fa fa-trash-o" data-content="deleteThisContent" v-tiptip="" v-on:click="deleteContent([secIndex, rowIndex, columnIndex, contentIndex])"></i></p></li></ul></div><span v-if="!isPreview" class="remove-el-on-save ignore-elements column-title">{{ printColumnTitle(secIndex, columnIndex) }}</span></td></tr></table></div></td></tr></table></div></div></td></tr></table></div><style>{{ globalElementStyles }}</style></div><div v-else="" id="email-template" class="email-template-text-only"><div style="max-width: 600px; margin: 0 auto; padding: 20px 0">{{{ emailTemplate.content }}}</div></div></div>';
  templates['customizer'] = '<table id="ecamp-customizer"><tr v-if="!showTemplateChooser"><td id="customizer-content"><div class="customizer-content-inside" :style="{ backgroundColor: emailTemplate.text_only ? \'#fff\' : \'\' }"><email-template :i18n="i18n" :email-template="emailTemplate" :customizer-data="customizerData"></email-template><div id="content-highlighter" :style="highlighter">&nbsp;</div></div></td><td id="customizer-controls"><div v-if="!emailTemplate.text_only" class="customizer-controls-inside"><div class="customizer-sidebar primary-sidebar" v-if="\'primary\' === currentSidebar"><div class="control-header"><div class="button-group"><button type="button" :class="[ \'button\', \'content\' === primaryTab ? \'active\' : \'\' ]" v-on:click="primaryTab = \'content\'">{{ i18n.content }}</button> <button type="button" :class="[ \'button\', \'design\' === primaryTab ? \'active\' : \'\' ]" v-on:click="primaryTab = \'design\'">{{ i18n.design }}</button> </div></div><div class="control-settings"><div class="tab-content primary-sidebar-content clearfix" v-show="\'content\' === primaryTab"><content-tab :i18n="i18n" :content-types="customizerData.contentTypes" :plugin-url="customizerData.pluginURL"></content-tab></div><div class="control-settings-tab-content" v-show="\'design\' === primaryTab"><design-tab :i18n="i18n" :sections="sections" :template-type="emailTemplate.templateType"></design-tab></div><div class="control-settings-tab-content" v-show="\'themes\' === primaryTab">themes</div></div></div><div class="customizer-sidebar page-sidebar" v-if="\'page\' === currentSidebar"><div class="control-header has-title"><div class="control-title"><h3>{{ i18n.page }}</h3><button type="button" class="button button-link" v-on:click="currentSidebar = \'primary\'"><i class="fa fa-angle-left"></i></button></div></div><page-editor :i18n="i18n" :email-template="emailTemplate" :current-sidebar="currentSidebar"></page-editor></div><div class="customizer-sidebar row-sidebar" v-if="\'design\' === currentSidebar"><div class="control-header has-title"><div class="control-title"><h3>{{ i18n[sections[secIndex].title] }}</h3><button type="button" class="button button-link" v-on:click="currentSidebar = \'primary\'"><i class="fa fa-angle-left"></i></button></div></div><design-editor :i18n="i18n" :section="sections[secIndex]" :current-sidebar="currentSidebar" :global-css="emailTemplate.globalCss"></design-editor></div><div class="customizer-sidebar row-sidebar customizer-element-editor" v-if="\'content-editor\' === currentSidebar"><div :class="contentEditor.headerClass"><div class="control-title"><h3>{{ i18n[contentEditor.type] }}</h3><div class="button-group" v-if="contentEditor.headerClass.indexOf(\'has-button-group\') >= 0"><button type="button" :class="[ \'button\', \'content\' === contentEditor.tab ? \'active\' : \'\' ]" v-on:click="contentEditor.tab = \'content\'">{{ i18n.content }}</button> <button type="button" :class="[ \'button\', \'beforeStyleTab\' === contentEditor.tab ? \'active\' : \'\' ]" v-on:click="contentEditor.tab = \'beforeStyleTab\'" v-if="contentEditor.headerClass.indexOf(\'has-tab-before-style\') >= 0">{{ i18n[customizerData.contentTypes[contentEditor.type].beforeStyleTab] }}</button> <button type="button" :class="[ \'button\', \'style\' === contentEditor.tab ? \'active\' : \'\' ]" v-on:click="contentEditor.tab = \'style\'" v-if="contentEditor.headerClass.indexOf(\'no-style-tab\') < 0">{{ i18n.style }}</button> <button type="button" :class="[ \'button\', \'settings\' === contentEditor.tab ? \'active\' : \'\' ]" v-on:click="contentEditor.tab = \'settings\'" v-if="contentEditor.headerClass.indexOf(\'no-settings-tab\') < 0">{{ i18n.settings }}</button></div></div></div><content-editor-text v-if="\'text\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :tinymce-settings="tinymceSettings"></content-editor-text><content-editor-image v-if="\'image\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :dummy-image="customizerData.dummyImage"></content-editor-image><content-editor-image v-if="\'imageGroup\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :dummy-image="customizerData.dummyImage" :group="true"></content-editor-image><content-editor-image-caption v-if="\'imageCaption\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :dummy-image="customizerData.dummyImage" :default-text="customizerData.contentTypes.imageCaption.defaultText" :tinymce-settings="tinymceSettings"></content-editor-image-caption><content-editor-button v-if="\'button\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor"></content-editor-button><content-editor-social-follow v-if="\'socialFollow\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :social-icons="customizerData.socialIcons" :plugin-url="customizerData.pluginURL"></content-editor-social-follow><content-editor-divider v-if="\'divider\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :customizer-data="customizerData"></content-editor-divider><content-editor-wp-posts v-if="\'wpPosts\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :customizer-data="customizerData"></content-editor-wp-posts><content-editor-wp-posts v-if="\'wpLatestPosts\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :customizer-data="customizerData" :is-latest-posts="true"></content-editor-wp-posts><content-editor-video v-if="\'video\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :customizer-data="customizerData" :tinymce-settings="tinymceSettings"></content-editor-video><content-editor-text v-if="\'footer\' === contentEditor.type" :i18n="i18n" :content-editor="contentEditor" :tinymce-settings="tinymceSettings"></content-editor-text><div class="sidebar-bottom-btns"><button type="button" class="button" v-on:click="saveAndClose">{{ i18n.saveAndClose }}</button></div></div></div><div v-else="" class="customizer-controls-inside"><div class="control-header has-title"><div class="control-title"><h3>{{ i18n.emailContent }}</h3></div></div><div class="sidebar-container"><div class="editor-tab-content"><text-editor :content="emailTemplate.content" :tinymce-settings="tinymceSettings"></text-editor></div></div></div></td></tr><tr v-else=""><td id="template-chooser-container"><div v-if="!showPresetPreview" id="template-chooser" class="clearfix"><div class="editor-list-tab-container"><div class="editor-list-tab"><ul class="list-inline"><li :class="[\'basic\' === templateChooserTab ? \'active\' : \'\']"><a href="#" v-on:click="setTemplateChooserTab($event, \'basic\')">{{ i18n.basic }}</a></li><li :class="[\'themes\' === templateChooserTab ? \'active\' : \'\']"><a href="#" v-on:click="setTemplateChooserTab($event, \'themes\')">{{ i18n.themes }}</a></li></ul></div></div><div v-if="\'basic\' === templateChooserTab" v-for="template in customizerData.baseTemplates" class="template-base-container" id="template-preset-container-{{ template.id }}"><div class="template-base"><div class="row"><div class="col-3 no-right-padding"><a href="#preview" v-on:click="previewPreset($event, template.id, template.title)"><img :src="baseTemplateImages[$index]" alt=""></a></div><div class="col-3"><h4>{{ template.title }}</h4><button type="button" class="button button-primary" v-on:click="setBaseTemplate(template.id)">{{ i18n.selectTemplate }}</button></div></div></div></div><div v-if="\'themes\' === templateChooserTab" class="theme-chooser-container"><div class="theme-filter"><div class="theme-filter-inside"><div class="row"><div class="col-2"><select class="form-control" v-model="categoryFilter"><option selected value="">{{ i18n.all }}</option><option v-for="(categoryId, category) in customizerData.themes" value="{{ category.title }}">{{ category.title }} ({{ category.themes.length }})</option></select></div><div class="col-2"><input type="text" class="form-control" placeholder="{{ i18n.searchThemes }}" v-model="themeFilter"></div></div></div></div><div v-for="(categoryId, category) in customizerData.themes | filterBy categoryFilter in \'title\'" :class="[\'clearfix\', hideCategory[categoryId] ? \'hidden\' : \'\']"><h4 class="category-title">{{ category.title }}</h4><div v-for="theme in category.themes | filterBy themeFilter in \'title\'" class="template-base-container" id="template-preset-container-{{ theme.id }}"><div class="template-base"><div class="row"><div class="col-3 no-right-padding"><a href="#preview" v-on:click="previewPreset($event, theme.id, theme.title)"><img :src="themesImages[theme.name]" alt=""></a></div><div class="col-3"><h4>{{ theme.title }}</h4><button type="button" class="button button-primary" v-on:click="setBaseTemplate(theme.id)">{{ i18n.selectTheme }}</button></div></div></div></div></div></div></div><div v-if="showPresetPreview" id="preset-preview"><div class="preset-preview-header clearfix"><h2 class="alignleft">{{ i18n.preview }} : {{ presetPreviewTitle }}</h2><div class="alignright"><button type="button" class="button button-primary" v-on:click="setBaseTemplate(showPresetPreview)">{{ i18n.selectTemplate }}</button> <button type="button" class="button" v-on:click="closePreview">{{ i18n.close }}</button></div></div><div id="preview-preset-window" :class="[\'mobile\' === previewDevice ? \'mobile-view\' : \'\']"><email-template v-if="!previewPresetURL" :email-template="emailTemplate" :customizer-data="customizerData" is-preview="true"></email-template><div v-else="" class="preset-preview-iframe-container" :style="previewIframeStyle"><iframe v-else="" :src="previewPresetURL" frameborder="0"></iframe></div></div></div></td></tr></table>';
})(this.ecampVueTemplates = this.ecampVueTemplates || {});
;(function($) {
    'use strict';

    /**
     * v-tiptip - custom tooltip directive using Tiptip js
     */
    Vue.directive('tiptip', {
        params: ['defaultPosition'],
    
        bind: function () {
            var settings = $.extend({
                defaultPosition: 'top',
                edgeOffset: 10,
                fadeIn: 100,
                fadeOut: 100,
                content: this.vm.i18n[this.el.dataset.content]
            }, this.params);
    
            $(this.el).tipTip(settings);
        }
    });
    
    /**
     * v-sortable-content directive definition
     *
     * Binds Sortable when the element is in DOM
     */
    Vue.directive('sortable-content', {
        bind: function () {
            var directive = this;
    
            new Sortable.create(this.el, {
                group: {
                    name: 'shared',
                    pull: 'clone',
                    put: true
                },
    
                handle: '.move',
                chosenClass: 'content-chosen',
                ghostClass: 'content-ghost',
    
                setData: function (dataTransfer, dragEl) {
                    // Firefox requires this line. Without this line element disappears
                    // after clicking the move icon
                    dataTransfer.setData('text/html', dragEl.innerHTML);
    
                    var pluginURL = directive.vm.$parent.customizerData.pluginURL,
                        imageSrc = directive.vm.$parent.customizerData.contentTypes[dragEl.dataset.type].image;
    
                    var img = new Image();
                    img.src = pluginURL + '/assets/images/content-type/' + imageSrc;
                    dataTransfer.setDragImage(img, 0, 0);
                },
    
                onStart: function () {
                    $('#tiptip_holder').hide();
                    $('body').addClass('dragging');
                },
    
                onAdd: function (e) {
                    e.preventDefault();
    
                    var type = '';
    
                    if (e.clone.dataset.hasOwnProperty('contentType')) {
                        var addContentTo = Array.prototype.indexOf.call(e.target.children, e.item);
    
                        // Even if we drag into template stage but do not drop into drop zone,
                        // contentDropOperation method will add new content. This condtion
                        // fix this issue
                        if ($(e.target).children('.content-type').length) {
                            // remove added content by Sortable, we'll re-render the template content
                            $(e.target).children().eq(addContentTo).remove();
    
                            type = 'add';
    
                            directive.vm.$parent.contentDropOperation(e, type, addContentTo);
                        }
    
                    } else if (e.clone.dataset.hasOwnProperty('contentId')) {
                        $('.content-container.content-chosen').remove();
                        type = 'sort';
    
                        directive.vm.$parent.contentDropOperation(e, type);
                    }
                },
    
                onMove: function (e) {
                    $('.dragging-in').removeClass('dragging-in');
                    $(e.to).addClass('dragging-in');
                },
    
                onEnd: function () {
                    $('body').removeClass('dragging');
                    $('.dragging-in').removeClass('dragging-in');
                }
            });
        }
    });
    
    Vue.component('datepicker', {
        template: ecampVueTemplates.datepicker,
    
        props: ['date', 'exclude'],
    
        data: function () {
            return {
                dateFormat: ecampGlobal.date.format,
                placeholder: ecampGlobal.date.placeholder
            };
        },
    
        ready: function () {
            var settings = {
                dateFormat: this.dateFormat,
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            };
    
            switch(this.exclude) {
                case 'prev':
                    settings.minDate = 0;
                    break;
    
                case 'next':
                    settings.maxDate = 0;
                    break;
            }
    
            $(this.$el).datepicker(settings);
        }
    });
    
    Vue.component('timepicker', {
        template: ecampVueTemplates.timepicker,
    
        props: ['time', 'step'],
    
        data: function () {
            return {
                timeFormat: ecampGlobal.time.format,
                placeholder: ecampGlobal.time.placeholder
            };
        },
    
        ready: function () {
            var self = this;
    
            $(this.$el).timepicker({
                scrollDefault: self.scroll ? self.scroll : 'now',
                step: self.step ? self.step : 30,
                timeFormat: self.timeFormat
            });
        }
    });
    
    Vue.component('colorpicker', {
        template: ecampVueTemplates.colorpicker,
    
        props: ['color', 'default'],
    
        ready: function () {
            var component = this;
    
            $(this.$el).wpColorPicker({
                color: component.color,
    
                change: function (e, ui) {
                    component.color = ui.color.toString();
                },
    
                clear: function () {
                    component.color = '';
                }
            });
        }
    });
    
    Vue.component('input-range', {
        template: ecampVueTemplates['input-range'],
    
        props: ['i18n', 'model', 'min', 'max', 'step', 'noPx', 'forcedSynced', 'modelName'],
    
        data: function () {
            return {
                value: 0,
            };
        },
    
        ready: function () {
            if (this.model && !this.noPx) {
                this.value = this.model.replace(/px/, '');
            } else {
                this.value = this.model;
            }
        },
    
        watch: {
            value: function (newVal) {
                var model = this.noPx ? newVal : newVal + 'px';
    
                this.$set('model', model);
    
                // v-model cannot set the range handler properly
                $(this.$el).find('input').val(newVal);
            },
    
            forcedSynced: function (isSynced) {
                if (!isSynced) {
                    var value = this.$parent[this.modelName];
    
                    if (this.model && !this.noPx) {
                        value = value.replace(/px/, '');
                    } else {
                        value = value;
                    }
    
                    this.$set('value', value);
                    this.$set('forcedSynced', true);
    
                    // v-model cannot set the range handler properly
                    $(this.$el).find('value').val(value);
                }
            }
        }
    });
    
    Vue.component('text-editor', {
        template: ecampVueTemplates['text-editor'],
    
        props: ['content', 'tinymceSettings'],
    
        data: function () {
            return {
                editorId: this._uid
            };
        },
    
        computed: {
            shortcodes: function () {
                return this.tinymceSettings.shortcodes;
            },
    
            pluginURL: function () {
                return this.tinymceSettings.pluginURL;
            }
        },
    
        ready: function () {
            var component = this;
    
            window.tinymce.init({
                selector: 'textarea#vue-text-editor-' + this.editorId,
                height: 300,
                menubar: false,
                convert_urls: false,
                theme: 'modern',
                skin: 'lightgray',
                content_css: component.pluginURL + '/assets/css/text-editor.css',
                branding: false,
                setup: function (editor) {
                    var shortcodeMenuItems = [];
                    $.each(component.shortcodes, function (ShortcodeType) {
                        shortcodeMenuItems.push({
                            text: this.title,
                            classes: 'menu-section-title'
                        });
    
                        $.each(this.codes, function (shortcode) {
                            var shortcodeDetails = this;
    
                            shortcodeMenuItems.push({
                                text: this.title,
                                onclick: function () {
                                    var code = '[' + ShortcodeType + ':' + shortcode + ']';
    
                                    if (shortcodeDetails.default) {
                                        code = '[' + ShortcodeType + ':' + shortcode + ' default="' + shortcodeDetails.default + '"]';
                                    }
    
                                    if (shortcodeDetails.text) {
                                        code = '[' + ShortcodeType + ':' + shortcode + ' text="' + shortcodeDetails.text + '"]';
                                    }
    
                                    if (shortcodeDetails.plain_text && shortcodeDetails.text) {
                                        code = shortcodeDetails.text;
                                    }
    
                                    editor.insertContent(code);
                                }
                            });
                        });
                    });
    
                    editor.addButton('shortcodes', {
                        type: 'menubutton',
                        icon: 'shortcode',
                        tooltip: 'Shortcodes',
                        menu: shortcodeMenuItems
                    });
    
                    editor.addButton('image', {
                        icon: 'image',
                        onclick: function () {
                            component.browseImage(editor);
                        }
                    });
    
                    // editor change triggers
                    editor.on('change', function () {
                        component.$set('content', editor.getContent());
                    });
                    editor.on('keyup', function () {
                        component.$set('content', editor.getContent());
                    });
                    editor.on('NodeChange', function () {
                        component.$set('content', editor.getContent());
                    });
                },
                fontsize_formats: '10px 11px 13px 14px 16px 18px 22px 25px 30px 36px 40px 45px 50px 60px 65px 70px 75px 80px',
                font_formats : 'Arial=arial,helvetica,sans-serif;'+
                    'Comic Sans MS=comic sans ms,sans-serif;'+
                    'Courier New=courier new,courier;'+
                    'Georgia=georgia,palatino;'+
                    'Lucida=Lucida Sans Unicode, Lucida Grande, sans-serif;'+
                    'Tahoma=tahoma,arial,helvetica,sans-serif;'+
                    'Times New Roman=times new roman,times;'+
                    'Trebuchet MS=trebuchet ms,geneva;'+
                    'Verdana=verdana,geneva;',
                plugins: 'textcolor colorpicker wplink wordpress code hr wpeditimage',
                toolbar: [
                    'shortcodes bold italic bullist numlist alignleft aligncenter alignjustify alignright link image wp_adv',
                    'formatselect forecolor backcolor underline strikethrough blockquote hr code',
                    'fontselect fontsizeselect removeformat undo redo'
                ]
            });
        },
    
        methods: {
            browseImage: function (editor) {
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.insertImage(editor, image);
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.insertImage(editor, image);
                    }
                });
    
                fileFrame.open();
            },
    
            insertImage: function (editor, image) {
                var img = '<img' +
                        ' src="' + image.url + '"' +
                        ' alt="' + image.alt + '"' +
                        ' title="' + image.title + '"' +
                        ' style="max-width: 100%; height: auto;"' +
                        '>';
    
                editor.insertContent(img);
            }
        }
    });
    
    Vue.component('vselect', {
        template: ecampVueTemplates.vselect,
        props: ['i18n', 'data', 'placeholder', 'width', 'templateResult'],
    
        ready: function () {
            this.init();
        },
    
        methods: {
            init: function () {
                var self = this;
    
                // set placeholder if we provide placeholder via attribute.
                // we can also provide placeholder with data object
                if (this.placeholder) {
                    this.data.placeholder = this.placeholder;
                }
    
                // first reset any previous binding
                $(this.$el).select2().select2('destroy').off('change');
                this.$el.options.length = 0;
    
                var settings = {
                    placeholder: this.data.placeholder ? this.data.placeholder : this.i18n.selectAnOption,
                    multiple: this.data.multiple,
                    data: this.data.options,
                    containerCssClass: 'v-select-container',
                    dropdownCssClass: 'v-select-dropdown',
                    width: this.width ? this.width : '100%',
                    minimumResultsForSearch: this.data.hideSearch ? -1 : 0,
                };
    
                if (this.templateResult) {
                    settings.templateResult = this.templateResult;
                }
    
                // select2 instance
                $(this.$el).select2(settings);
    
                // on change the selected option, we will dispatch some data
                // and this component object itself to the parent
                $(this.$el).on('change', function () {
                    var newVal = $(this).val(),
                        name = $(this).attr('name'),
                        id = $(this).attr('id'),
                        classNames = $(this).attr('class');
    
                    if (self.data.hasOwnProperty('selected') && newVal !== self.data.selected) {
                        self.$set('data.selected', $(this).val());
    
                        var selectData = {
                            selected: newVal,
                            name: name,
                            id: id,
                            classNames: classNames
                        };
    
                        self.$dispatch('vselect-change', selectData, self);
                    }
                });
    
                if (this.data.hasOwnProperty('options')) {
                    this.isSelectedInOptions();
                }
    
                $(this.$el).val(this.data.selected);
                $(this.$el).trigger('change');
            },
    
            isSelectedInOptions: function () {
                var i = 0,
                    contains = false;
    
                for (i = 0; i < this.data.options.length; i++) {
                    if (this.data.selected === this.data.options[i].id) {
                        contains = true;
                        break;
                    }
                }
    
                if (!contains) {
                    this.data.selected = null;
                }
            }
        },
    
        watch: {
            data: {
                deep: true,
                handler: function (newData) {
                    this.$set('data', newData);
    
                    // re-initialize
                    this.init();
                }
            }
        }
    });
    
    Vue.component('campaign-form', {
        template: ecampVueTemplates['campaign-form'],
    
        props: ['i18n', 'formData', 'automaticActions', 'shortcodes'],
    
        data: function () {
            return {
                subMaxLength: 150
            };
        },
    
        created: function () {
            if (!this.formData.event.action) {
                this.formData.event.action = Object.keys(this.automaticActions)[0];
            }
    
            if (!this.event.scheduleType) {
                this.event.scheduleType = 'immediately';
            }
    
            $('.has-floating-info input').on('focus', function () {
                $(this).parent().addClass('focused');
            }).on('blur', function () {
                $(this).parent().removeClass('focused');
            });
        },
    
        ready: function () {
            $(this.$el).find('.floating-info > input').each(function () {
                var chars = $(this).val().length;
    
                if (chars) {
                    $(this).parent().addClass('has-length');
                }
    
            });
    
        },
    
        computed: {
            event: function () {
                return this.formData.event;
            },
    
            action: function () {
                return this.event.action;
            },
    
            actionLists: function () {
                var lists = [];
                if ('erp_crm_create_contact_subscriber' === this.action) {
                    if (this.formData.lists.contact_groups.lists.length) {
                        lists = $.extend([], this.formData.lists.contact_groups.lists);
                    }
    
                } else if ('erp_create_new_people' === this.action) {
                    lists = $.map(this.formData.peopleTypes, function (value) {
                        return { id: value, name: Vue.util.classify(value) };
                    });
                } else if ('erp_matches_segment' === this.action) {
                    if (this.formData.lists.save_searches.lists.length) {
                        lists = $.map(this.formData.lists.save_searches.lists, function (search) {
                            return { id: search.id, name: search.name };
                        });
                    }
                }
    
                return lists;
            },
    
            subRemainingChar: function () {
                var remainingChar = this.subMaxLength;
    
                if (this.formData.subject) {
                    remainingChar -= this.formData.subject.length;
                }
    
                return (remainingChar < 0) ? 0 : remainingChar;
            },
    
            subRemainingCharClass: function () {
                var className = '';
    
                if (this.subRemainingChar < 0) {
                    className = 'error';
                }
    
                return (this.subRemainingChar <= 0) ? 'error' : '';
            },
    
            campaignType: {
                get: function () {
                    if ('automatic' !== this.formData.send) {
                        return 'standard';
                    } else {
                        return 'automatic';
                    }
                },
    
                set: function (newVal) {
                    if ('automatic' === newVal) {
                        this.formData.send = 'automatic';
                    } else if (this.formData.isScheduled) {
                        this.formData.send = 'scheduled';
                    } else {
                        this.formData.send = 'immediately';
                    }
                }
            },
    
            shortcodeList: function () {
                var list = [];
    
                $.each(this.shortcodes, function (shortcodeType, shortcodeObj) {
                    list.push({title: shortcodeObj.title, parent: true});
    
                    $.each(shortcodeObj.codes, function (codeName, codeObj) {
                        list.push({title: codeObj.title, parent: false, code: '[' + shortcodeType + ':' + codeName + ']'});
                    });
                });
    
                return list;
            }
        },
    
        watch: {
            action: function () {
                this.event.argVal = this.actionLists.length ? this.actionLists[0].id : null;
            },
        },
    
        methods: {
            focusFloatingInfoInput: function (e) {
                $(e.target).parent().addClass('has-length');
    
                if (e.target.dataset.hasOwnProperty('validation')) {
                    this.removeInvalidaClasses(e);
                }
            },
    
            blurFloatingInfoInput: function (e) {
                var input = $(e.target),
                    inputVal = input.val(),
                    chars = inputVal.length;
    
                if (!chars) {
                    input.parent().removeClass('has-length');
                }
    
                // validation after blur
                if (e.target.dataset.hasOwnProperty('validation') && this[e.target.dataset.validation].call(this, inputVal)) {
                    $(e.target).addClass('invalid-form-control');
                    $('.validation-error[data-validtion-msg="' + e.target.dataset.validationMsg + '"').addClass('show');
    
                } else if (e.target.dataset.hasOwnProperty('validation')) {
                    this.removeInvalidaClasses(e);
                }
            },
    
            removeInvalidaClasses: function (e) {
                $(e.target).removeClass('invalid-form-control');
                $('.validation-error[data-validtion-msg="' + e.target.dataset.validationMsg + '"').removeClass('show');
            },
    
            isEmptyInput: function (inputVal) {
                return !inputVal;
            },
    
            isInvalidEmail: function (email) {
                return !(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));
            },
    
            addSubjectShortcode: function (e, shortcode) {
                e.preventDefault();
    
                if (!shortcode.parent) {
                    this.formData.subject += ' ' + shortcode.code;
                    $('#email-subject').focus();
                }
            }
        }
    });
    
    Vue.component('content-tab', {
        template: ecampVueTemplates['content-tab'],
    
        props: ['i18n', 'contentTypes', 'pluginUrl'],
    
        methods: {
            getBackgroundImage: function (image) {
                return 'url("' + this.pluginUrl + '/assets/images/content-type/' + image + '")';
            }
        },
    
        ready: function () {
            var component = this;
    
            new Sortable.create(this.$el, {
                sort: false,
                group: {
                    name: 'shared',
                    pull: 'clone',
                    put: false
                },
                chosenClass: 'type-chosen',
                ghostClass: 'type-ghost',
                filter: '.ignore-elements',
    
                setData: function (dataTransfer, dragEl) {
                    // Firefox requires this line. Without this line element disappears
                    // after clicking the move icon
                    dataTransfer.setData('text/html', dragEl.innerHTML);
    
                    var imageSrc = component.contentTypes[dragEl.dataset.contentType].image;
    
                    var img = new Image();
                    img.src = component.pluginUrl + '/assets/images/content-type/' + imageSrc;
                    dataTransfer.setDragImage(img, 0, 0);
                },
    
                onStart: function () {
                    $('body').addClass('dragging');
                },
    
                onMove: function (e) {
                    $('.dragging-in').removeClass('dragging-in');
                    $(e.to).addClass('dragging-in');
                },
    
                onEnd: function () {
                    $('body').removeClass('dragging');
                    $('.dragging-in').removeClass('dragging-in');
                }
            });
        }
    });
    
    Vue.component('design-tab', {
        template: ecampVueTemplates['design-tab'],
    
        props: ['i18n', 'sections', 'templateType'],
    
        // events are handled in customizer component
        methods: {
            openPageEditor: function (e) {
                e.preventDefault();
    
                this.$dispatch('open-page-editor');
                this.$dispatch('hide-highlighter');
            },
    
            openEditor: function (e, secIndex) {
                e.preventDefault();
    
                this.$dispatch('open-design-editor', secIndex, 0);
                this.$dispatch('hide-highlighter');
            },
    
            showHighlighter: function (secIndex) {
                if ('full-width' === this.templateType) {
                    this.$dispatch('highlight-section', secIndex);
                } else {
                    this.$dispatch('highlight-row', secIndex, 0);
                }
            },
    
            hideHighlighter: function () {
                this.$dispatch('hide-highlighter');
            }
        }
    });
    
    Vue.component('content-text', {
        template: ecampVueTemplates['content-text'],
    
        props: ['content'],
    
        computed: {
            activeColumns: function () {
                return parseInt(this.content.activeColumns);
            },
    
            columnClasses: function () {
                var classes = [];
    
                if (this.activeColumns < 2) {
                    classes = ['cell-1-1'];
                } else if ('1-1' === this.content.columnSplit) {
                    classes = ['cell-2-1', 'cell-2-1'];
                } else if ('1-2' === this.content.columnSplit) {
                    classes = ['cell-3-1', 'cell-3-2'];
                } else if ('2-1' === this.content.columnSplit) {
                    classes = ['cell-3-2', 'cell-3-1'];
                }
    
                return classes;
            }
        }
    });
    
    Vue.component('content-image', {
        template: ecampVueTemplates['content-image'],
    
        props: ['content', 'group', 'dummyImage'],
    
        computed: {
            images: function () {
                var images = [];
    
                if (this.content.images.length) {
                    images = this.content.images;
    
                } else if (!this.group) {
                    images = [{image: null, alt: null}];
    
                } else {
                    var count = this.content.layout.split('-').length,
                        i = 0;
    
                    for(i = 0; i < count; i++) {
                        images.push({image: null, alt: null});
                    }
                }
    
                return images;
            },
    
            layout: function () {
                return this.content.layout;
            },
        },
    
        methods: {
            printImage: function (index, imgObj) {
                if (!imgObj.image) {
                    return '<div class="content-dummy-image"><img src="' + this.dummyImage + '" alt=""></div>';
    
                } else {
                    var img = document.createElement('img');
                    img.src = imgObj.image;
                    img.alt = imgObj.alt;
    
                    $.extend(img.style, {width: this.content.widths[index]});
    
                    if (imgObj.link) {
                        var anc = document.createElement('a');
    
                        anc.classNames = 'block-content';
                        anc.href = imgObj.link;
    
                        if (imgObj.openLinkInNewWindow) {
                            anc.target = '_blank';
                        }
    
                        img = anc.appendChild(img);
                    }
    
                    var div = document.createElement('div');
                    div.appendChild(img);
    
                    return div.innerHTML;
                }
            },
        }
    });
    
    Vue.component('content-image-caption', {
        template: ecampVueTemplates['content-image-caption'],
    
        props: ['content', 'dummyImage', 'defaultText'],
    
        computed: {
            groups: function () {
                var i = 0,
                    defaultGroup = {
                        image: {image: null, alt: null, openAttrEditor: ''},
                        text: this.defaultText
                    };
    
                if (!this.content.groups.length) {
                    this.content.groups.push($.extend(true, {}, defaultGroup));
                    this.content.groups.push($.extend(true, {}, defaultGroup));
                } else {
                    for (i = 0; i < this.content.groups.length; i++) {
    
                        if ($.isEmptyObject(this.content.groups[i].image)) {
                            this.content.groups[i].image = $.extend({}, {image: null, alt: null, openAttrEditor: ''});
                        }
    
                    }
                }
    
                return this.content.groups;
            },
    
            activeColumns: function () {
                return parseInt(this.content.activeColumns);
            },
    
            classNames: function () {
                if (this.activeColumns === 1 ) {
                    return ['cell-1-1'];
                } else {
                    return ['cell-2-1', 'cell-2-1'];
                }
            }
        },
    
        methods: {
            printImage: function (imgObj) {
                if (!imgObj.image) {
                    return '<div class="content-dummy-image"><img src="' + this.dummyImage + '" alt=""></div>';
    
                } else {
                    var img = '<img src="' + imgObj.image + '" alt="' + imgObj.alt + '">';
    
                    if (imgObj.link) {
                        if (imgObj.openLinkInNewWindow) {
                            img = '<a class="block-content" href="' + imgObj.link + '" target="_blank">' + img + '</a>';
                        } else {
                            img = '<a class="block-content" href="' + imgObj.link + '">' + img + '</a>';
                        }
                    }
    
                    return img;
                }
            }
        }
    });
    
    Vue.component('content-button', {
        template: ecampVueTemplates['content-button'],
    
        props: ['content'],
    
        computed: {
            href: function () {
                return this.content.link ? this.content.link : '#';
            },
    
            classNames: function () {
                return this.content.className ? this.content.className : 'ecamp-btn';
            },
    
            title: function () {
                return this.content.title ? this.content.title : '';
            }
        }
    });
    
    Vue.component('content-social-follow', {
        template: ecampVueTemplates['content-social-follow'],
    
        props: ['content', 'socialIcons', 'pluginUrl'],
    
        computed: {
            imageUrls: function () {
                var component = this,
                    urls = {};
    
                $.each(this.content.icons, function () {
                    urls[this.site] = component.pluginUrl + '/assets/images/social-icons/' + component.content.iconStyle + '-' + this.site + '.png';
                });
    
                return urls;
            },
    
            iconClasses: function () {
                var classNames = ['social-follow-table'];
    
                if ('verticle' === this.content.layout) {
                    classNames.push('verticle-icons');
                } else {
                    classNames.push('horizontal-icons');
                }
    
                if ('large' === this.content.layoutSize) {
                    classNames.push('large-icons');
                } else {
                    classNames.push('default-icons');
                }
    
                if (this.content.containerAlign) {
                    classNames.push('align-' + this.content.containerAlign);
                }
    
                return classNames;
            },
    
            containerTableStyles: function () {
                var margin = {},
                    styles = [],
                    i = 0,
                    totalIcons = this.content.icons.length;
    
                if ('verticle' === this.content.layout) {
                    margin = { marginBottom: this.content.iconMargin };
                } else {
                    margin = { marginRight: this.content.iconMargin };
                }
    
                for (i = 0; i < totalIcons; i++) {
                    if (i < (totalIcons - 1)) {
                        styles.push(margin);
                    } else {
                        styles.push({});
                    }
                }
    
                return styles;
            },
    
            textCss: function () {
                return {
                    color: this.content.style.color,
                    textTransform: this.content.style.textTransform ? this.content.style.textTransform : 'none',
                    fontSize: this.content.style.fontSize ? this.content.style.fontSize : '14px',
                };
            },
    
            wrapperWidth: function () {
                if ('verticle' === this.content.layout && 'center' === this.content.containerAlign && 'default' === this.content.layoutSize) {
                    return 'auto';
                } else if ('verticle' === this.content.layout && 'center' !== this.content.containerAlign) {
                    return 'auto';
                } else {
                    return '100%';
                }
            },
    
            wrapperAlign: function () {
                if ('verticle' === this.content.layout && 'right' === this.content.containerAlign) {
                    return 'right';
                } else {
                    return '';
                }
            },
    
            containerAlign: function () {
                if ('verticle' === this.content.layout && 'center' === this.content.containerAlign && 'default' === this.content.layoutSize) {
                    return 'left';
                } else {
                    return this.content.containerAlign;
                }
            },
    
            wrapperStyle: function () {
                if ('verticle' === this.content.layout && 'center' === this.content.containerAlign && 'default' === this.content.layoutSize) {
                    return {margin: '0 auto'};
                } else {
                    return {};
                }
            }
        }
    });
    
    Vue.component('content-divider', {
        template: ecampVueTemplates['content-divider'],
    
        props: ['content', 'dividers']
    });
    
    Vue.component('content-wp-posts', {
        template: ecampVueTemplates['content-wp-posts'],
    
        props: ['i18n', 'content', 'customizerData', 'isLatestPosts'],
    
        data: function () {
            return {
                postContents: []
            };
        },
    
        created: function () {
            if (this.content.postIds.length) {
                this.getPosts();
            }
        },
    
        computed: {
            // t1-i2-c3
            // i1-t2-c3
            // i1-tc1
            // tc1-i1
            // t1-ic2
            // t1-ci2
    
            postIds: function () {
                return this.content.postIds;
            },
    
            showTitleOnTop: function () {
                var layouts = ['t1-i2-c3', 't1-ic2', 't1-ci2'];
                return (layouts.indexOf(this.content.layout) >= 0);
            },
    
            titleStyle: function () {
                return this.content.title.style;
            },
    
            showImage: function () {
                return this.content.image.active;
            },
    
            imageStyle: function () {
                // float layouts
                switch(this.content.layout) {
                    case 'i1-tc1':
                    case 't1-ic2':
                        this.content.image.style.width = (parseInt(this.content.image.style.width) <= 50) ? this.content.image.style.width : '50%';
                        this.content.image.style.float = 'left';
                        this.content.image.style.marginLeft = '0px';
                        this.content.image.style.marginRight = parseInt(this.content.image.style.marginRight) ? this.content.image.style.marginRight : '10px';
                        this.content.image.style.marginBottom = parseInt(this.content.image.style.marginBottom) ? this.content.image.style.marginBottom : '10px';
                        break;
    
                    case 'tc1-i1':
                    case 't1-ci2':
                        this.content.image.style.width = (parseInt(this.content.image.style.width) <= 50) ? this.content.image.style.width : '50%';
                        this.content.image.style.float = 'right';
                        this.content.image.style.marginLeft = parseInt(this.content.image.style.marginLeft) ? this.content.image.style.marginLeft : '10px';
                        this.content.image.style.marginRight = '0px';
                        this.content.image.style.marginBottom = parseInt(this.content.image.style.marginBottom) ? this.content.image.style.marginBottom : '10px';
                        break;
                }
    
                return this.content.image.style;
            },
    
            contentStyle: function () {
                var style = this.content.postContent.style;
    
                if ('show' !== this.divider.display) {
                    style.marginBottom = this.content.style.padding;
                }
    
                return style;
            },
    
            divider: function () {
                return this.content.divider;
            },
    
            twoColumnPostContents: function () {
                var i = 0,
                    j = 0,
                    contents = [];
    
                for (i = 0; i < this.postContents.length; i += 2) {
                    contents[j] = [];
    
                    contents[j].push(this.postContents[i]);
    
                    if (this.postContents[i+1]) {
                        contents[j].push(this.postContents[i+1]);
                    }
    
                    contents[j+1] = [];
    
                    j += 2;
                }
    
                return contents;
            },
    
            twoColumnPadding: function () {
                return [
                    { paddingRight: this.content.style.padding },
                    { paddingLeft: this.content.style.padding },
                ];
            }
        },
    
        methods: {
            getPosts: function () {
                var component = this;
    
                $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'get',
                    dataType: 'json',
                    data: {
                        action: 'get_posts_for_template',
                        _wpnonce: ecampGlobal.nonce,
                        args: {
                            post_type: Object.keys(this.customizerData.postTypes),
                            post__in: this.postIds,
                        }
                    },
                    beforeSend: function () {
                        NProgress.start();
                    }
                }).done(function (response) {
                    NProgress.done();
    
                    if (response.success && response.data.posts.length) {
                        component.$set('postContents', response.data.posts);
                    }
                });
            }
        },
    
        watch: {
            postIds: function () {
                this.getPosts();
            }
        }
    });
    
    Vue.component('content-video', {
        template: ecampVueTemplates['content-video'],
    
        props: ['content', 'dummyVideoImage', 'defaultText'],
    
        computed: {
        },
    
        methods: {
            printImage: function (video) {
                if (!video.image) {
                    return '<div class="content-dummy-image dummy-video"><img src="' + this.dummyVideoImage + '" alt=""></div>';
    
                } else {
                    var img = '<img src="' + video.image + '" alt="' + video.alt + '">';
    
                    if (video.link) {
                        if (video.openLinkInNewWindow) {
                            img = '<a class="block-content" href="' + video.link + '" target="_blank">' + img + '<span class="player-icon">&nbsp;</span></a>';
                        } else {
                            img = '<a class="block-content" href="' + video.link + '">' + img + '<span class="player-icon">&nbsp;</span></a>';
                        }
                    }
    
                    return img;
                }
            }
        }
    
    });
    
    Vue.component('content', {
        template: ecampVueTemplates.content,
    
        props: ['i18n', 'content', 'customizerData']
    });
    
    Vue.component('email-template', {
        template: ecampVueTemplates['email-template'],
    
        props: ['i18n', 'emailTemplate', 'customizerData', 'isPreview'],
    
        computed: {
            defaultContainerStyle: function () {
                var component = this;
    
                return this.emailTemplate.sections.map(function (section) {
    
                    if ('full-width' === component.emailTemplate.templateType) {
                        return {};
                    } else {
                        return section.rowContainerStyle;
                    }
    
                });
            },
    
            fullWidthContainerStyle: function () {
                var component = this;
    
                return this.emailTemplate.sections.map(function (section) {
    
                    if ('full-width' === component.emailTemplate.templateType) {
                        return section.rowContainerStyle;
                    } else {
                        return {};
                    }
    
                });
            },
    
            globalElementStyles: function () {
                var elStyles = '';
    
                $.each(this.emailTemplate.globalElementStyles, function (tag, styles) {
                    elStyles += '#email-template ' + tag + ' { ';
    
                    $.each(styles, function(prop, val) {
                        elStyles += Vue.util.hyphenate(prop) + ': ' + val + '; ';
                    });
    
                    elStyles += '}';
                });
    
                return elStyles;
            }
        },
    
        methods: {
            openContentEditor: function(type, contentId) {
                this.$dispatch('open-content-editor', type, contentId);
            },
    
            cloneContent: function (contentType, contentId) {
                this.$dispatch('clone-content', contentType, contentId);
            },
    
            deleteContent: function (contentId) {
                var component = this;
    
                swal({
                    title: '<small>' + this.i18n.confirmDeleteMsg + '<small>',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d54e21',
                    confirmButtonText: this.i18n.confirmDeleteBtn,
                    cancelButtonText: this.i18n.confirmCancelBtn,
                    html: true,
                }, function(isConfirm){
                    if (isConfirm) {
                        component.$dispatch('delete-content', contentId);
                } });
            },
    
            // rowIndex is always zero in present case. So, we don't
            // have to worry about this right now.
            printColumnTitle: function (secIndex, columnIndex) {
                var noOfColumns = parseInt(this.emailTemplate.sections[secIndex].rows[0].activeColumns),
                    title = this.i18n[this.emailTemplate.sections[secIndex].title];
    
                switch(columnIndex) {
                    case 0:
                        switch(noOfColumns) {
                            case 2:
                            case 3:
                                title += ' - ' + this.i18n.left;
                                break;
                        }
                        break;
    
                    case 1:
                        switch(noOfColumns) {
                            case 2:
                                title += ' - ' + this.i18n.right;
                                break;
    
                            case 3:
                                title += ' - ' + this.i18n.center;
                                break;
                        }
                        break;
    
                    case 2:
                        title += ' - ' + this.i18n.right;
                        break;
                }
    
                return title;
            },
    
            // It is not possible to use a function in class binding prop
            // when two way binding is active. So we have to tricky to use
            // true ? getColumnClass(secIndex, columnIndex) : ''
            // statement ;)
            getColumnClass: function (secIndex, columnIndex) {
                var noOfColumns = parseInt(this.emailTemplate.sections[secIndex].rows[0].activeColumns),
                    columnClass = 'cell-1-1';
    
                switch(columnIndex) {
                    case 0:
                    case 1:
                        switch(noOfColumns) {
                            case 2:
                                columnClass = 'cell-2-1';
                                break;
    
                            case 3:
                                columnClass = 'cell-3-1';
                                break;
                        }
                        break;
    
                    case 2:
                        columnClass = 'cell-3-1';
                        break;
                }
    
                return columnClass;
            },
    
            showDropZone: function (row, columnIndex) {
                return !this.isPreview && row.columns[columnIndex].contents.length === 1;
            }
        }
    });
    
    Vue.component('page-editor', {
        template: ecampVueTemplates['page-editor'],
    
        props: ['i18n', 'emailTemplate', 'currentSidebar'],
    
        data: function () {
            return {
                totalSections: 0,
                emailBorder: '0px none #dddddd',
                emailBorderWidth: '0px',
                emailBorderStyle: 'none',
                emailBorderColor: '#dddddd',
                emailBorderLabel: '0px #dddddd',
                ignoreTop: false,
                ignoreBottom: false,
                linkColor: '',
                fontFamilies: [
                    {
                        id: 'arial,helvetica,sans-serif',
                        text: 'Arial'
                    }, {
                        id: 'comic sans ms,sans-serif',
                        text: 'Comic Sans MS'
                    }, {
                        id: 'courier new,courier',
                        text: 'Courier New'
                    }, {
                        id: 'georgia,palatino',
                        text: 'Georgia'
                    }, {
                        id: 'Lucida Sans Unicode, Lucida Grande, sans-serif',
                        text: 'Lucida'
                    }, {
                        id: 'tahoma,arial,helvetica,sans-serif',
                        text: 'Tahoma'
                    }, {
                        id: 'times new roman,times',
                        text: 'Times New Roman'
                    }, {
                        id: 'trebuchet ms,geneva',
                        text: 'Trebuchet MS'
                    }, {
                        id: 'verdana,geneva',
                        text: 'Verdana'
                    },
                ]
            };
        },
    
        created: function() {
            this.totalSections = this.emailTemplate.sections.length;
    
            var borderTop = this.emailTemplate.sections[0].rowContainerStyle.borderTop.split(' '),
                borderBottom = this.emailTemplate.sections[0].rowContainerStyle.borderBottom.split(' '),
                borderLeft = this.emailTemplate.sections[0].rowContainerStyle.borderLeft.split(' ');
    
    
            if (borderTop[0] !== borderLeft[0] || borderTop[3] !== borderLeft[3]) {
                this.ignoreTop = true;
            }
    
            if (borderBottom[0] !== borderLeft[0] || borderBottom[3] !== borderLeft[3]) {
                this.ignoreBottom= true;
            }
    
            this.emailBorderWidth = borderLeft[0];
            this.emailBorderStyle = borderLeft[1];
            this.emailBorderColor = borderLeft[2];
    
            // global link color
            if (!this.emailTemplate.globalElementStyles.a.color) {
                this.$set('emailTemplate.globalElementStyles.a.color', 'inherit');
            }
    
            if ('inherit' !== this.emailTemplate.globalElementStyles.a.color) {
                this.linkColor = this.emailTemplate.globalElementStyles.a.color;
            }
        },
    
        computed: {
            emailBorderLabel: function () {
                if (this.emailBorderColor.trim()) {
                    return this.emailBorder.replace(/solid|none/, ' ');
                } else {
                    return this.emailBorder.replace(/solid|none/, ' ') + ' ######';
                }
            },
    
            getFontFamiliesForSelect2: function () {
                if (!this.emailTemplate.globalCss.fontFamily) {
                    this.emailTemplate.globalCss.fontFamily = 'arial,helvetica,sans-serif';
                }
    
                return {
                    hideSearch: true,
                    selected: this.emailTemplate.globalCss.fontFamily,
                    options: this.fontFamilies
                };
            }
        },
    
        methods: {
            setBorders: function () {
                var i = 0;
    
                this.emailBorder = this.emailBorderWidth + ' ' + this.emailBorderStyle + ' ' + this.emailBorderColor;
    
                if (!this.ignoreTop) {
                    this.emailTemplate.sections[0].rowContainerStyle.borderTop = this.emailBorder;
                }
    
                for (i = 0; i < this.totalSections; i++) {
                    this.emailTemplate.sections[i].rowContainerStyle.borderLeft = this.emailBorder;
                    this.emailTemplate.sections[i].rowContainerStyle.borderRight = this.emailBorder;
                }
    
                if (!this.ignoreBottom) {
                    this.emailTemplate.sections[this.totalSections - 1].rowContainerStyle.borderBottom = this.emailBorder;
                }
            },
    
            saveAndClose: function () {
                this.currentSidebar = 'primary';
    
                this.$dispatch('save-campaign-silently');
            },
    
            fontFamilyTemplate: function (fontFamily) {
                return $('<span>').text(fontFamily.text).css('fontFamily', fontFamily.id).get(0);
            }
        },
    
        watch: {
            emailBorderWidth: function () {
                this.setBorders();
            },
    
            emailBorderColor: function () {
                this.setBorders();
            },
    
            linkColor: function (newColor) {
                if (newColor) {
                    this.emailTemplate.globalElementStyles.a.color = newColor;
                } else {
                    this.emailTemplate.globalElementStyles.a.color = 'inherit';
                }
            }
        },
    
        events: {
            'vselect-change': function (selectData) {
                if ('page_editor_font_family' === selectData.name) {
                    this.emailTemplate.globalCss.fontFamily = selectData.selected;
                }
            }
        },
    });
    
    Vue.component('design-editor', {
        template: ecampVueTemplates['design-editor'],
    
        props: ['i18n', 'section', 'currentSidebar', 'globalCss'],
    
        data: function () {
            return {
                borderTopWidth: '0px',
                borderTopStyle: 'solid',
                borderTopColor: '#dddddd',
                borderBottomWidth: '0px',
                borderBottomStyle: 'solid',
                borderBottomColor: '#dddddd',
                paddingTopBottom: '0px',
                paddingLeftRight: '0px',
                fontFamilies: [
                    {
                        id: 'inherit',
                        text: this.i18n.inherit
                    }, {
                        id: 'arial,helvetica,sans-serif',
                        text: 'Arial'
                    }, {
                        id: 'comic sans ms,sans-serif',
                        text: 'Comic Sans MS'
                    }, {
                        id: 'courier new,courier',
                        text: 'Courier New'
                    }, {
                        id: 'georgia,palatino',
                        text: 'Georgia'
                    }, {
                        id: 'Lucida Sans Unicode, Lucida Grande, sans-serif',
                        text: 'Lucida'
                    }, {
                        id: 'tahoma,arial,helvetica,sans-serif',
                        text: 'Tahoma'
                    }, {
                        id: 'times new roman,times',
                        text: 'Times New Roman'
                    }, {
                        id: 'trebuchet ms,geneva',
                        text: 'Trebuchet MS'
                    }, {
                        id: 'verdana,geneva',
                        text: 'Verdana'
                    },
                ],
                fontSizeForcedSynced: true,
            };
        },
    
        created: function () {
            var borderTop = this.section.rowContainerStyle.borderTop.split(' '),
                borderBottom = this.section.rowContainerStyle.borderBottom.split(' ');
    
            this.borderTopWidth = borderTop[0];
            this.borderTopColor = borderTop[2] ? borderTop[2] : ' ';
            this.borderBottomWidth = borderBottom[0];
            this.borderBottomColor = borderBottom[2] ? borderBottom[2] : ' ';
    
            this.paddingTopBottom = this.section.rowContainerStyle.paddingTop;
            this.paddingLeftRight = this.section.rowContainerStyle.paddingLeft;
        },
    
        computed: {
            borderTopLabel: function () {
                if (this.borderTopColor.trim()) {
                    return this.borderTopWidth + ' ' + this.borderTopColor;
                } else {
                    return this.borderTopWidth + ' ######';
                }
            },
    
            borderBottomLabel: function () {
                if (this.borderBottomColor.trim()) {
                    return this.borderBottomWidth + ' ' + this.borderBottomColor;
                } else {
                    return this.borderBottomWidth + ' ######';
                }
            },
    
            getFontFamiliesForSelect2: function () {
                if (!this.section.rowContainerStyle.fontFamily) {
                    this.section.rowContainerStyle.fontFamily = 'arial,helvetica,sans-serif';
                }
    
                return {
                    hideSearch: true,
                    selected: this.section.rowContainerStyle.fontFamily,
                    options: this.fontFamilies
                };
            },
    
            sectionFontSize: {
                get: function () {
                    if ('inherit' === this.section.rowContainerStyle.fontSize) {
                        return this.globalCss.fontSize;
                    } else {
                        return this.section.rowContainerStyle.fontSize;
                    }
                },
    
                set: function (newVal) {
                    if (newVal === this.globalCss.fontSize) {
                        this.section.rowContainerStyle.fontSize = 'inherit';
                    } else {
                        this.section.rowContainerStyle.fontSize = newVal;
                    }
                }
            },
    
            sectionColor: {
                get: function () {
                    return this.section.rowContainerStyle.color;
                },
    
                set: function (newVal) {
                    if (!newVal) {
                        this.section.rowContainerStyle.color = 'inherit';
                    } else {
                        this.section.rowContainerStyle.color = newVal;
                    }
                }
            }
        },
    
        watch: {
            borderTopWidth: function (newVal) {
                this.section.rowContainerStyle.borderTop = newVal + ' solid ' + this.borderTopColor;
            },
    
            borderTopColor: function (newVal) {
                this.section.rowContainerStyle.borderTop = this.borderTopWidth + ' solid ' + newVal;
            },
    
            borderBottomWidth: function (newVal) {
                this.section.rowContainerStyle.borderBottom = newVal + ' solid ' + this.borderBottomColor;
            },
    
            borderBottomColor: function (newVal) {
                this.section.rowContainerStyle.borderBottom = this.borderBottomWidth + ' solid ' + newVal;
            },
    
            paddingTopBottom: function (newVal) {
                this.section.rowContainerStyle.paddingTop = newVal;
                this.section.rowContainerStyle.paddingBottom = newVal;
            },
    
            paddingLeftRight: function (newVal) {
                this.section.rowContainerStyle.paddingLeft = newVal;
                this.section.rowContainerStyle.paddingRight = newVal;
            }
        },
    
        methods: {
            saveAndClose: function () {
                this.currentSidebar = 'primary';
    
                this.$dispatch('save-campaign-silently');
            },
    
            fontFamilyTemplate: function (fontFamily) {
                return $('<span>').text(fontFamily.text).css('fontFamily', fontFamily.id).get(0);
            },
    
            resetFontSize: function (e) {
                e.preventDefault();
                this.sectionFontSize = 'inherit';
                this.fontSizeForcedSynced = false;
            }
        },
    
        events: {
            'vselect-change': function (selectData) {
                console.log('page_editor_font_family_' + this._uid);
                if ('page_editor_font_family_' + this._uid === selectData.name) {
                    this.section.rowContainerStyle.fontFamily = selectData.selected;
                }
            }
        },
    });
    
    Vue.component('content-editor-text', {
        template: ecampVueTemplates['content-editor-text'],
    
        props: ['i18n', 'contentEditor', 'tinymceSettings'],
    
        data: function () {
            return {
                activeColumns: 1,
                currentColumn: 0,
                paddingTopBottom: '0px',
                paddingLeftRight: '0px',
            };
        },
    
        created: function () {
            this.paddingTopBottom = this.contentEditor.contents.style.paddingTop;
            this.paddingLeftRight = this.contentEditor.contents.style.paddingLeft;
    
            if (!this.contentEditor.contents.style.hasOwnProperty('color')) {
                this.$set('contentEditor.contents.style.color', '');
            }
        },
    
        computed: {
            activeColumns: function () {
                return parseInt(this.contentEditor.contents.activeColumns);
            }
        },
    
        ready: function () {
            if (!parseInt(this.contentEditor.contents.style.borderWidth)) {
                this.contentEditor.contents.style.borderStyle = 'none';
            } else {
                this.contentEditor.contents.style.borderStyle = 'solid';
            }
        },
    
        watch: {
            contentEditor: {
                deep: true,
                handler: function (newObj) {
                    if (!parseInt(newObj.contents.style.borderWidth)) {
                        this.contentEditor.contents.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.style.borderStyle = 'solid';
                    }
                }
            },
    
            paddingTopBottom: function (newVal) {
                this.contentEditor.contents.style.paddingTop = newVal;
                this.contentEditor.contents.style.paddingBottom = newVal;
            },
    
            paddingLeftRight: function (newVal) {
                this.contentEditor.contents.style.paddingLeft = newVal;
                this.contentEditor.contents.style.paddingRight = newVal;
            }
        },
    
        methods: {
            setCurrentColumn: function (e, index) {
                e.preventDefault();
                this.currentColumn = parseInt(index);
            },
    
            setColumnSplit: function(e, split) {
                e.preventDefault();
                this.$set('contentEditor.contents.columnSplit', split);
            }
        }
    });
    
    Vue.component('content-editor-image', {
        template: ecampVueTemplates['content-editor-image'],
    
        props: ['i18n', 'contentEditor', 'dummyImage', 'group'],
    
        data: function () {
            return {
                edgeToEdge: false
            };
        },
    
        computed: {
            images: function () {
                var i = 0;
    
                if (!this.contentEditor.contents.images.length) {
    
                    if (!this.group) {
                        this.contentEditor.contents.images = [{image: null, alt: null, openAttrEditor: ''}];
                    } else {
                        var count = this.contentEditor.contents.layout.split('-').length;
    
                        for(i = 0; i < count; i++) {
                            this.contentEditor.contents.images.push({image: null, alt: null, openAttrEditor: ''});
                        }
                    }
    
                // if single image and has group images, we will truncate them
                } else if (!this.group) {
                    this.contentEditor.contents.images = [this.contentEditor.contents.images.shift()];
                }
    
                return this.contentEditor.contents.images;
            },
        },
    
        ready: function () {
            if (!parseInt(this.contentEditor.contents.style.borderWidth)) {
                this.contentEditor.contents.style.borderStyle = 'none';
            } else {
                this.contentEditor.contents.style.borderStyle = 'solid';
            }
    
    
            if ('-10px' === this.contentEditor.contents.style.marginLeft) {
                this.edgeToEdge = true;
            } else {
                this.edgeToEdge = false;
            }
        },
    
        watch: {
            contentEditor: {
                deep: true,
                handler: function (newObj) {
                    if (!parseInt(newObj.contents.style.borderWidth)) {
                        this.contentEditor.contents.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.style.borderStyle = 'solid';
                    }
                }
            },
    
            edgeToEdge: function (set) {
                if (set) {
                    this.contentEditor.contents.style.padding = '0px';
                } else {
                    this.contentEditor.contents.style.padding = '15px';
                }
            }
        },
    
        methods: {
            printImage: function (imgObj) {
                if (!imgObj.image) {
                    return '<img src="' + this.dummyImage + '">';
    
                } else {
                    return '<img src="' + imgObj.image + '" alt="' + imgObj.alt + '">';
                }
            },
    
            browseImage: function (e, index) {
                e.preventDefault();
    
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.images[index].image = image.url;
                    component.images[index].alt = image.alt ? image.alt : image.url.split('/').pop();
    
                    var width = (image.width > 600) ? 600 : image.width;
                    component.contentEditor.contents.widths[index] = width + 'px';
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.images[index].image = image.url;
                        component.images[index].alt = image.alt ? image.alt : image.url.split('/').pop();
    
                        var width = (image.width > 600) ? 600 : image.width;
                        component.contentEditor.contents.widths[index] = width + 'px';
                    }
                });
    
                fileFrame.open();
            },
    
            openAttrEditor: function (e, index, attr) {
                e.preventDefault();
                this.contentEditor.contents.images[index].openAttrEditor = attr;
            },
    
            addNewImage: function (e) {
                e.preventDefault();
    
                if (this.contentEditor.contents.images.length < 3) {
                    this.switchDefaultLayout(this.contentEditor.contents.images.length + 1);
                    this.contentEditor.contents.images.push({image: null, alt: null, openAttrEditor: ''});
                }
            },
    
            removeImage: function (e, index) {
                e.preventDefault();
    
                this.switchDefaultLayout(this.contentEditor.contents.images.length - 1);
    
                this.contentEditor.contents.images.splice(index, 1);
    
            },
    
            switchDefaultLayout: function (count) {
                switch(count) {
                    case 3:
                        this.contentEditor.contents.layout = 'r1-r2-r2';
                        break;
                    default:
                        this.contentEditor.contents.layout = 'r1-r1';
                        break;
                }
            },
    
            setLayout: function(e, layout) {
                e.preventDefault();
                this.$set('contentEditor.contents.layout', layout);
            }
        },
    });
    
    Vue.component('content-editor-image-caption', {
        template: ecampVueTemplates['content-editor-image-caption'],
    
        props: ['i18n', 'contentEditor', 'dummyImage', 'defaultText', 'tinymceSettings'],
    
        data: function () {
            return {
                paddingTopBottom: '15px',
                paddingLeftRight: '15px',
                edgeToEdge: false,
                currentColumn: 0,
            };
        },
    
        computed: {
            groups: function () {
                var i = 0,
                    defaultGroup = {
                        image: {image: null, alt: null, openAttrEditor: ''},
                        text: this.defaultText
                    };
    
                if (!this.contentEditor.contents.groups.length) {
                    this.contentEditor.contents.groups.push($.extend(true, {}, defaultGroup));
                    this.contentEditor.contents.groups.push($.extend(true, {}, defaultGroup));
                } else {
                    for (i = 0; i < this.contentEditor.contents.groups.length; i++) {
    
                        if ($.isEmptyObject(this.contentEditor.contents.groups[i].image)) {
                            this.contentEditor.contents.groups[i].image = $.extend({}, {image: null, alt: null, openAttrEditor: ''});
                        }
    
                    }
                }
    
                return this.contentEditor.contents.groups;
            },
    
            activeColumns: function () {
                var column = parseInt(this.contentEditor.contents.activeColumns);
                return column ? column : 1;
            }
        },
    
        created: function () {
            this.paddingTopBottom = this.contentEditor.contents.style.padding.split(' ')[0];
            this.paddingLeftRight = this.contentEditor.contents.style.padding.split(' ')[1];
        },
    
        ready: function () {
            if (!parseInt(this.contentEditor.contents.style.borderWidth)) {
                this.contentEditor.contents.style.borderStyle = 'none';
            } else {
                this.contentEditor.contents.style.borderStyle = 'solid';
            }
    
            if ('-10px' === this.contentEditor.contents.style.marginLeft) {
                this.edgeToEdge = true;
            } else {
                this.edgeToEdge = false;
            }
        },
    
        watch: {
            contentEditor: {
                deep: true,
                handler: function (newObj) {
                    if (!parseInt(newObj.contents.style.borderWidth)) {
                        this.contentEditor.contents.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.style.borderStyle = 'solid';
                    }
                }
            },
    
            edgeToEdge: function (set) {
                if (set) {
                    this.contentEditor.contents.style.marginLeft = '-10px';
                    this.contentEditor.contents.style.marginRight = '-10px';
                } else {
                    this.contentEditor.contents.style.marginLeft = '0px';
                    this.contentEditor.contents.style.marginRight = '0px';
                }
            },
    
            paddingTopBottom: function (newVal) {
                this.contentEditor.contents.style.padding = newVal + ' ' + this.paddingLeftRight;
            },
    
            paddingLeftRight: function (newVal) {
                this.contentEditor.contents.style.padding = this.paddingTopBottom + ' ' + newVal;
            },
        },
    
        methods: {
            printImage: function (imgObj) {
                if (!imgObj.image) {
                    return '<img src="' + this.dummyImage + '">';
    
                } else {
                    return '<img src="' + imgObj.image + '" alt="' + imgObj.alt + '">';
                }
            },
    
            browseImage: function (e, index) {
                e.preventDefault();
    
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.groups[index].image.image = image.url;
                    component.groups[index].image.alt = image.alt ? image.alt : image.url.split('/').pop();
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.groups[index].image.image = image.url;
                        component.groups[index].image.alt = image.alt ? image.alt : image.url.split('/').pop();
                    }
                });
    
                fileFrame.open();
            },
    
            openAttrEditor: function (e, index, attr) {
                e.preventDefault();
                this.contentEditor.contents.groups[index].image.openAttrEditor = attr;
            },
    
            setCurrentColumn: function (e, index) {
                e.preventDefault();
                this.currentColumn = parseInt(index);
            },
    
            setTextAlign: function (align) {
                this.$set('contentEditor.contents.style.textAlign', align);
            },
    
            classifyStr: function (str) {
                return Vue.util.classify(str);
            }
        },
    });
    
    Vue.component('content-editor-button', {
        template: ecampVueTemplates['content-editor-button'],
    
        props: ['i18n', 'contentEditor'],
    
        data: function () {
            return {
                paddingTopBottom: '18px',
                paddingLeftRight: '65px',
                marginTopBottom: '18px',
                uppercase: false,
                buttonWidth: 'default'
            };
        },
    
        created: function () {
            this.paddingTopBottom = this.contentEditor.contents.style.padding.split(' ')[0];
            this.paddingLeftRight = this.contentEditor.contents.style.padding.split(' ')[1];
    
            this.marginTopBottom = this.contentEditor.contents.style.margin.split(' ')[0];
    
            if ('uppercase' === this.contentEditor.contents.style.textTransform) {
                this.uppercase = true;
            }
        },
    
        watch: {
            contentEditor: {
                deep: true,
                handler: function (newObj) {
                    if (!parseInt(newObj.contents.style.borderWidth)) {
                        this.contentEditor.contents.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.style.borderStyle = 'solid';
                    }
                }
            },
    
            paddingTopBottom: function (newVal) {
                this.contentEditor.contents.style.padding = newVal + ' ' + this.paddingLeftRight;
            },
    
            paddingLeftRight: function (newVal) {
                this.contentEditor.contents.style.padding = this.paddingTopBottom + ' ' + newVal;
            },
    
            marginTopBottom: function (newVal) {
                this.contentEditor.contents.style.margin = newVal + ' ' + '15px';
            },
    
            uppercase: function (newVal) {
                if (newVal) {
                    this.contentEditor.contents.style.textTransform = 'uppercase';
                } else {
                    this.contentEditor.contents.style.textTransform = 'none';
                }
            },
    
            buttonWidth: function (newVal) {
                if ('block' === newVal) {
                    this.contentEditor.contents.style.display = 'block';
                } else {
                    this.contentEditor.contents.style.display = 'inline-block';
                }
            }
        },
    
        methods: {
            setContainerAlign: function (align) {
                this.contentEditor.contents.containerStyle.textAlign = align;
            },
    
            classifyStr: function (str) {
                return Vue.util.classify(str);
            }
        }
    });
    
    Vue.component('content-editor-social-follow', {
        template: ecampVueTemplates['content-editor-social-follow'],
    
        props: ['i18n', 'contentEditor', 'socialIcons', 'pluginUrl'],
    
        data: function () {
            return {
                uppercase: false,
                iconStyle: 'solid',
                iconStyleVariant: 'color'
            };
        },
    
        created: function () {
            if ('uppercase' === this.contentEditor.contents.style.textTransform) {
                this.uppercase = true;
            }
    
            if (this.contentEditor.contents.iconStyle) {
                this.iconStyle = this.contentEditor.contents.iconStyle.split('-')[0];
                this.iconStyleVariant = this.contentEditor.contents.iconStyle.replace(this.iconStyle + '-', '');
            }
        },
    
        computed: {
            imageUrls: function () {
                var component = this,
                    urls = {};
    
                $.each(this.socialIcons.sites, function (slug) {
                    urls[slug] = component.pluginUrl + '/assets/images/social-icons/solid-color-' + slug + '.png';
                });
    
                return urls;
            },
    
            iconsDropdowns: function () {
                var dropdownData = [],
                    component = this;
    
                $.each(this.contentEditor.contents.icons, function () {
                    dropdownData.push(component.setIconsDropdownData(this.site));
                });
    
                return dropdownData;
            },
    
            iconBG: function () {
                return this.socialIcons.iconBGs;
            }
        },
    
        events: {
            'vselect-change': function (selectData) {
                if ('icon_dropdown' === selectData.name) {
                    var index = selectData.id.split('-').pop();
    
                    this.contentEditor.contents.icons[index].site = selectData.selected;
    
                    this.switchIconSelection(index);
                }
            }
        },
    
        methods: {
            setIconsDropdownData: function (selected) {
                var data = {
                        hideSearch: false,
                        selected: selected,
                        options: []
                    };
    
                $.each(this.socialIcons.sites, function (site, icon) {
                    data.options.push({id: site, text: icon.title});
                });
    
                return data;
            },
    
            switchIconSelection: function (index) {
                var site = this.contentEditor.contents.icons[index].site;
    
                this.contentEditor.contents.icons[index].link = this.socialIcons.sites[site].link;
                this.contentEditor.contents.icons[index].text = this.socialIcons.sites[site].title;
            },
    
            addNewService: function () {
                var newService = {
                    site: 'link',
                    link: this.socialIcons.sites.link.link,
                    text: this.socialIcons.sites.link.title
                };
    
                this.contentEditor.contents.icons.push(newService);
            },
    
            removeService: function (e, index) {
                e.preventDefault();
    
                this.contentEditor.contents.icons.splice(index, 1);
            },
    
            setLayout: function (e, layout, layoutSize) {
                e.preventDefault();
    
                this.contentEditor.contents.layout = layout;
                this.contentEditor.contents.layoutSize = layoutSize;
            },
    
            isLayoutActive: function (layout, layoutSize) {
                return ((layout === this.contentEditor.contents.layout) && (layoutSize === this.contentEditor.contents.layoutSize));
            },
    
            switchDisplayMode: function () {
                if ('text' === this.contentEditor.contents.display) {
                    this.contentEditor.contents.layoutSize = 'default';
                }
            },
    
            classify: function (str) {
                return Vue.util.classify(str);
            },
    
            setVariant: function(e, variant) {
                e.preventDefault();
    
                this.iconStyleVariant = variant;
            }
        },
    
        watch: {
            contentEditor: {
                deep: true,
                handler: function (newObj) {
                    if (!parseInt(newObj.contents.style.borderWidth)) {
                        this.contentEditor.contents.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.style.borderStyle = 'solid';
                    }
                }
            },
    
            uppercase: function (newVal) {
                if (newVal) {
                    this.contentEditor.contents.style.textTransform = 'uppercase';
                } else {
                    this.contentEditor.contents.style.textTransform = 'none';
                }
            },
    
            iconStyle: function (newStyle) {
                this.contentEditor.contents.iconStyle = newStyle + '-' + this.iconStyleVariant;
    
                this.iconStyleVariant = this.socialIcons.iconTypes[newStyle][0];
            },
    
            iconStyleVariant: function (newVariant) {
                this.contentEditor.contents.iconStyle = this.iconStyle + '-' + newVariant;
            }
        }
    });
    
    Vue.component('content-editor-divider', {
        template: ecampVueTemplates['content-editor-divider'],
    
        props: ['i18n', 'contentEditor', 'customizerData'],
    
        data: function () {
            return {
                dividerType: 'line',
                borderStyles: ['solid', 'dashed', 'dotted', 'double', 'groove', 'ridge'],
                paddingTopBottom: '10px',
                marginTopBottom: '0px',
                displayGallery: false,
                choosenImage: null,
            };
        },
    
        created: function () {
            if (this.contentEditor.contents.useImage) {
                this.dividerType = 'image';
            }
        },
    
        computed: {
            dividers: function () {
                return this.customizerData.dividers;
            },
    
            previewImage: function () {
                var image = this.contentEditor.contents.image.image;
    
                if (!image) {
                    var firstImage = this.customizerData.dividers.images[0].name,
                        e = {
                            preventDefault: function () {}
                        };
    
                    image = this.customizerData.dividers.baseURL + firstImage;
    
                    this.contentEditor.contents.image.style.height = this.customizerData.dividers.images[0].height;
    
                    this.selectPresetDivider( e, firstImage );
                }
    
                return image;
            },
    
            presetImages: function () {
                var images = [],
                    i = 0;
    
                for (i = 0; i < this.customizerData.dividers.images.length; i++) {
    
                    if (Vue.util.isObject(this.customizerData.dividers.images[i])) {
                        images[i] = this.customizerData.dividers.baseURL + this.customizerData.dividers.images[i].name;
                    } else {
                        images[i] = this.customizerData.dividers.baseURL + this.customizerData.dividers.images[i];
                    }
                }
    
                return images;
            }
        },
    
        watch: {
            dividerType: function (newType) {
                this.contentEditor.contents.useImage = ('image' === newType);
                this.displayGallery = false;
            },
    
            paddingTopBottom: function (newVal) {
                this.contentEditor.contents.containerStyle.paddingTop = newVal;
                this.contentEditor.contents.containerStyle.paddingBottom = newVal;
            },
    
            marginTopBottom: function (newVal) {
                this.contentEditor.contents.containerStyle.marginTop = newVal;
                this.contentEditor.contents.containerStyle.marginBottom = newVal;
            }
        },
    
        methods: {
            setBorderStyle: function (e, style) {
                e.preventDefault();
    
                this.contentEditor.contents.style.borderTopStyle = style;
            },
    
            browseImage: function (e) {
                e.preventDefault();
    
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.contentEditor.contents.image.image = image.url;
                    // component.contentEditor.contents.image.style.backgroundImage = 'url(' + image.url + ')';
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.contentEditor.contents.image.image = image.url;
                        // component.contentEditor.contents.image.style.backgroundImage = 'url(' + image.url + ')';
                    }
                });
    
                fileFrame.open();
            },
    
            setDisplayGallery: function (e) {
                e.preventDefault();
    
                this.displayGallery = true;
            },
    
            selectPresetDivider: function (e, imageName) {
                e.preventDefault();
    
                var url = '';
    
                if (Vue.util.isObject(imageName)) {
                    url = this.customizerData.dividers.baseURL + imageName.name;
                    this.contentEditor.contents.image.style.height = imageName.height;
                } else {
                    url = this.customizerData.dividers.baseURL + imageName;
                    this.contentEditor.contents.image.style.height = '20px';
                }
    
                this.contentEditor.contents.image.image = url;
                // this.contentEditor.contents.image.style.backgroundImage = 'url(' + url + ')';
    
                this.displayGallery = false;
            },
    
            setTempDivider: function (e, imageName) {
                e.preventDefault();
    
                var url = '';
    
                this.choosenImage = this.contentEditor.contents.image.image;
    
                if (Vue.util.isObject(imageName)) {
                    url = this.customizerData.dividers.baseURL + imageName.name;
                    this.contentEditor.contents.image.style.height = imageName.height;
                } else {
                    url = this.customizerData.dividers.baseURL + imageName;
                    this.contentEditor.contents.image.style.height = '20px';
                }
    
                this.contentEditor.contents.image.image = url;
                // this.contentEditor.contents.image.style.backgroundImage = 'url(' + url + ')';
            },
    
            resetTempDivider: function (e) {
                e.preventDefault();
    
                var url = this.choosenImage;
    
                this.contentEditor.contents.image.image = url;
                // this.contentEditor.contents.image.style.backgroundImage = 'url(' + url + ')';
            },
    
            hideGallery: function (e) {
                e.preventDefault();
    
                this.displayGallery = false;
            }
        },
    
        filters: {
            classify: function (str) {
                return Vue.util.classify(str);
            }
        }
    });
    
    Vue.component('content-editor-wp-posts', {
        template: ecampVueTemplates['content-editor-wp-posts'],
    
        props: ['i18n', 'contentEditor', 'customizerData', 'isLatestPosts'],
    
        data: function () {
            return {
                postTaxTerms: {},
                selected: {
                    postType: '',
                    taxTerm: '',
                    postStatus: 'publish',
                },
                paged: 1,
                search: '',
                ajaxHandler: {
                    abort: function () {} // required for the first time call
                },
                showLoadMore: false,
                posts: [],
                checkedPosts: [],
                styleTabs: ['title', 'image', 'content', 'button', 'divider'],
                currentStyleTab: 'title',
                readMorePaddingTopBottom: '0px',
                readMorePaddingLeftRight: '0px',
                maxPostsToShow: 1,
                dividerType: 'line',
                borderStyles: ['solid', 'dashed', 'dotted', 'double', 'groove', 'ridge'],
                displayGallery: false,
                choosenImage: null,
            };
        },
    
        created: function () {
            this.readMorePaddingTopBottom = this.contentEditor.contents.readMore.style.padding.split(' ')[0];
            this.readMorePaddingLeftRight = this.contentEditor.contents.readMore.style.padding.split(' ')[1];
    
            if (this.contentEditor.contents.divider.useImage) {
                this.dividerType = 'image';
            }
        },
    
        computed: {
            tab: function () {
                return this.contentEditor.tab;
            },
    
            postTypes: function () {
                var types = [];
    
                $.each(this.customizerData.postTypes, function (name, title) {
                    types.push({id: name, text: title});
                });
    
                return types;
            },
    
            taxTerms: function () {
                var taxTerms = [];
    
                $.each(this.postTaxTerms, function (tax, taxObj) {
                    $.each(taxObj.terms, function (termId, termTitle) {
                        taxTerms.push({id: tax + '_' + termId, text: taxObj.title + ': ' + termTitle});
                    });
                });
    
                return taxTerms;
            },
    
            titleContainerStyle: function () {
                return this.contentEditor.contents.title.container.style;
            },
    
            titleStyle: function () {
                return this.contentEditor.contents.title.style;
            },
    
            imageStyle: function () {
                return this.contentEditor.contents.image.style;
            },
    
            contentContainerStyle: function () {
                return this.contentEditor.contents.postContent.containerStyle;
            },
    
            contentStyle: function () {
                return this.contentEditor.contents.postContent.style;
            },
    
            readMoreContainerStyle: function () {
                return this.contentEditor.contents.readMore.containerStyle;
            },
    
            readMoreStyle: function () {
                return this.contentEditor.contents.readMore.style;
            },
    
            dividerStyle: function () {
                return this.contentEditor.contents.divider.style;
            },
    
            dividerImageStyle: function () {
                return this.contentEditor.contents.divider.image.style;
            },
    
            previewImage: function () {
                var image = this.contentEditor.contents.divider.image.image;
    
                if (!image) {
                    var firstImage = this.customizerData.dividers.images[0].name,
                        e = {
                            preventDefault: function () {}
                        };
    
                    image = this.customizerData.dividers.baseURL + firstImage;
    
                    this.contentEditor.contents.divider.image.style.height = this.customizerData.dividers.images[0].height;
    
                    this.selectPresetDivider( e, firstImage );
                }
    
                return image;
            },
    
            presetImages: function () {
                var images = [],
                    i = 0;
    
                for (i = 0; i < this.customizerData.dividers.images.length; i++) {
    
                    if (Vue.util.isObject(this.customizerData.dividers.images[i])) {
                        images[i] = this.customizerData.dividers.baseURL + this.customizerData.dividers.images[i].name;
                    } else {
                        images[i] = this.customizerData.dividers.baseURL + this.customizerData.dividers.images[i];
                    }
                }
    
                return images;
            },
    
            layouts: function () {
                if (1 === parseInt(this.contentEditor.contents.column)) {
                    return ['i1-t2-c3', 't1-i2-c3', 'i1-tc1', 'tc1-i1', 't1-ic2', 't1-ci2'];
                } else {
                    return ['i1-t2-c3', 't1-i2-c3'];
                }
            }
        },
    
        ready: function () {
            // this will trigger the watch:selected method
            // so that, latest posts will fetch for the list table
            this.selected.postType = 'post';
    
            // bind all select elements
            this.bindPostTypesDropDown();
            this.bindTaxTermsDropDown();
            this.bindStatusDropDown();
    
            // fetch the categories for the first time
            $('#editor-wp-posts-post-type').trigger('change');
        },
    
        methods: {
            bindPostTypesDropDown: function () {
                var component = this;
    
                $('#editor-wp-posts-post-type').select2({
                    placeholder: this.i18n.filterByPostType,
                    data: this.postTypes,
                    width: '100%',
    
                }).on('change', function () {
                    var postType = $(this).val();
    
                    component.selected = {
                        postType: postType,
                        taxTerm: '',
                        postStatus: 'publish',
                    };
    
                    if (postType) {
                        $.ajax({
                            url: ecampGlobal.ajaxurl,
                            method: 'get',
                            dataType: 'json',
                            data: {
                                action: 'get_post_type_tax_terms',
                                _wpnonce: ecampGlobal.nonce,
                                post_type: postType,
                            },
                            beforeSend: function () {
                                NProgress.configure({parent: '#editor-wp-posts-editor-tab'});
                                NProgress.start();
                                window.setDefaultNProgressParent();
                            }
    
                        }).done(function (response) {
                            NProgress.done();
    
                            if (response.success && !$.isEmptyObject(response.data)) {
                                component.postTaxTerms = response.data;
                            } else {
                                component.postTaxTerms = {};
                            }
    
                            component.bindTaxTermsDropDown();
                        });
    
                    } else {
                        component.postTaxTerms = {};
                        component.bindTaxTermsDropDown();
                    }
                });
            },
    
            bindTaxTermsDropDown: function () {
                var component = this;
    
                // reset
                component.selected.taxTerm = '',
                $('#editor-wp-posts-tax-terms').off('change');
                $('#editor-wp-posts-tax-terms').select2().select2('destroy');
                $('#editor-wp-posts-tax-terms').html('<option></option>');
    
                // bind
                $('#editor-wp-posts-tax-terms').select2({
                    placeholder: component.i18n.categoriesTags,
                    data: component.taxTerms,
                    width: '100%',
                    allowClear: true,
    
                }).on('change', function () {
                    component.selected = {
                        postType: component.selected.postType,
                        taxTerm: $(this).val(),
                        postStatus: 'publish',
                    };
                });
            },
    
            bindStatusDropDown: function () {
                var component = this;
    
                $('#editor-wp-posts-status').select2({
                    placeholder: this.i18n.filterByPostStatus,
                    width: '100%',
                    allowClear: true,
                    minimumResultsForSearch: -1,
    
                }).on('change', function () {
                    component.selected = {
                        postType: component.selected.postType,
                        taxTerm: component.selected.taxTerm,
                        postStatus: $(this).val(),
                    };
                });
            },
    
            getFilteredPosts: function (params) {
                var component = this,
                    args = {
                        paged: params.paged
                    },
                    taxTerm = '',
                    termId = 0;
    
                // set post type arg
                if (params.postType) {
                    args.post_type = params.postType;
                }
    
                // set tax_query arg. For non latest posts it's taxTerm (singular),
                // for latest posts it's taxTerms (plural)
                if (!this.isLatestPosts && params.taxTerm) {
                    taxTerm = params.taxTerm.split('_');
                    termId = parseInt(taxTerm.pop());
    
                    args.tax_query = [
                        {
                            taxonomy: taxTerm.join('_'),
                            field: 'term_id',
                            terms: parseInt(termId)
                        }
                    ];
    
                } else if (params.taxTerms && params.taxTerms.length) {
                    var i = 0;
    
                    args.tax_query = {relation: 'AND'};
    
                    for(i = 0; i < params.taxTerms.length; i++) {
                        taxTerm = params.taxTerms[i].split('_');
                        termId = parseInt(taxTerm.pop());
    
                        args.tax_query[i] = {
                            taxonomy: taxTerm.join('_'),
                            field: 'term_id',
                            terms: parseInt(termId)
                        };
                    }
                }
    
                if (params.postStatus) {
                    args.post_status = params.postStatus;
                }
    
                args.s = params.search;
    
                if (this.isLatestPosts) {
                    args.posts_per_page = (this.maxPostsToShow <= 6) ? this.maxPostsToShow : 6;
                    args.fields = 'ids';
                }
    
                this.ajaxHandler = $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'get',
                    dataType: 'json',
                    data: {
                        action: 'get_posts_for_wp_posts_editor',
                        _wpnonce: ecampGlobal.nonce,
                        args: args
                    },
                    beforeSend: function () {
                        NProgress.configure({parent: '#editor-wp-posts-editor-tab'});
                        NProgress.start();
                        window.setDefaultNProgressParent();
                    }
                }).done(function (response) {
                    NProgress.done();
    
                    // reset selected posts
                    if (!params.appendResult) {
                        component.posts = [];
                        component.checkedPosts = [];
                    }
    
                    if (response.success && response.data.posts.length) {
                        if (params.appendResult) {
                            // only for pagination
                            component.$set('posts', component.posts.concat(response.data.posts));
                        } else {
                            component.$set('posts', response.data.posts);
                        }
    
                        component.$set('showLoadMore', ((parseInt(response.data.totalPages) - parseInt(params.paged)) >= 1));
    
                        // latest contents
                        if (component.isLatestPosts) {
                            component.contentEditor.contents.postIds = $.extend([], response.data.posts);
                        }
                    } else {
                        component.$set('showLoadMore', false);
                    }
                });
            },
    
            triggerClickCheckbox: function (index) {
                $('#wp-selected-post-' + index).trigger('click');
            },
    
            getPagedPosts: function () {
                var params = {
                    postType: this.selected.postType,
                    taxTerm: this.selected.taxTerm,
                    postStatus: this.selected.postStatus,
                    paged: ++this.paged,
                    search: this.search,
                    appendResult: true
                };
    
                this.ajaxHandler.abort();
                this.getFilteredPosts(params);
            },
    
            insertPosts: function () {
                this.contentEditor.contents.postIds = $.extend([], this.checkedPosts);
            },
    
            // layouts: 't1-i2-c3', 'i1-t2-c3', 'i1-tc1', 'tc1-i1', 't1-ic2', 't1-ci2'
            setLayout: function (e, layout) {
                e.preventDefault();
                this.contentEditor.contents.layout = layout;
    
                switch(this.contentEditor.contents.layout) {
                    case 't1-i2-c3':
                    case 'i1-t2-c3':
                        this.contentEditor.contents.image.style.width = '100%';
                        this.contentEditor.contents.image.style.float = 'none';
                        this.contentEditor.contents.image.style.marginLeft = '0px';
                        this.contentEditor.contents.image.style.marginRight = '0px';
                        this.contentEditor.contents.image.style.marginBottom = '0px';
                        break;
                }
    
            },
    
            setStyleTab: function (e, tab) {
                e.preventDefault();
    
                this.currentStyleTab = tab;
            },
    
            setBorderStyle: function (e, style) {
                e.preventDefault();
    
                this.dividerStyle.borderTopStyle = style;
            },
    
            insertLatestPosts: function () {
                var params = {
                        postType: this.selected.postType,
                        taxTerms: this.selected.taxTerm,
                        postStatus: 'publish',
                        paged: 1,
                        search: '',
                        appendResult: false
                    };
    
                this.getFilteredPosts(params);
    
                this.contentEditor.contents.postType = this.selected.postType;
                this.contentEditor.contents.taxTerms = this.selected.taxTerm;
            },
    
            browseImage: function (e) {
                e.preventDefault();
    
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.contentEditor.contents.divider.image.image = image.url;
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.contentEditor.contents.divider.image.image = image.url;
                    }
                });
    
                fileFrame.open();
            },
    
            setDisplayGallery: function (e) {
                e.preventDefault();
    
                this.displayGallery = true;
            },
    
            selectPresetDivider: function (e, imageName) {
                e.preventDefault();
    
                var url = '';
    
                if (Vue.util.isObject(imageName)) {
                    url = this.customizerData.dividers.baseURL + imageName.name;
                    this.dividerImageStyle.height = imageName.height;
                } else {
                    url = this.customizerData.dividers.baseURL + imageName;
                    this.dividerImageStyle.height = '20px';
                }
    
                this.contentEditor.contents.divider.image.image = url;
    
                this.displayGallery = false;
            },
    
            setTempDivider: function (e, imageName) {
                e.preventDefault();
    
                var url = '';
    
                this.choosenImage = this.contentEditor.contents.divider.image.image;
    
                if (Vue.util.isObject(imageName)) {
                    url = this.customizerData.dividers.baseURL + imageName.name;
                    this.dividerImageStyle.height = imageName.height;
                } else {
                    url = this.customizerData.dividers.baseURL + imageName;
                    this.dividerImageStyle.height = '20px';
                }
    
                this.contentEditor.contents.divider.image.image = url;
            },
    
            resetTempDivider: function (e) {
                e.preventDefault();
    
                var url = this.choosenImage;
    
                this.contentEditor.contents.divider.image.image = url;
            },
    
            hideGallery: function (e) {
                e.preventDefault();
    
                this.displayGallery = false;
            }
    
        },
    
        watch: {
            // when editor reveals for the first time, this method doesn't trigger.
            // so, while its open and we get back to content tab
            // from style or settings tab, we'll have to trigger the ready
            // state changes again.
            tab: function (newTab) {
                if ('content' === newTab) {
                    this.selected.postType = 'post';
                    this.bindPostTypesDropDown();
                    this.bindTaxTermsDropDown();
                    this.bindStatusDropDown();
                }
            },
    
            selected: {
                deep: true,
                handler: function (newSelectedObj) {
                    this.paged = 1;
                    this.search = '';
    
                    var params = {
                        postType: newSelectedObj.postType,
                        taxTerm: newSelectedObj.taxTerm,
                        postStatus: newSelectedObj.postStatus,
                        paged: this.paged,
                        search: this.search
                    };
    
                    if (!this.isLatestPosts) {
                        this.ajaxHandler.abort();
                        this.getFilteredPosts(params);
                    }
                }
            },
    
            search: function (newString) {
                this.paged = 1;
    
                var params = {
                    postType: this.selected.postType,
                    taxTerm: this.selected.taxTerm,
                    postStatus: this.selected.postStatus,
                    paged: this.paged,
                    search: newString,
                };
    
                this.ajaxHandler.abort();
                this.getFilteredPosts(params);
            },
    
            imageStyle: {
                deep: true,
                handler: function (newStyle) {
                    if (!parseInt(newStyle.borderWidth)) {
                        this.contentEditor.contents.image.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.image.style.borderStyle = 'solid';
                    }
                }
            },
    
            readMorePaddingTopBottom: function (newVal) {
                this.contentEditor.contents.readMore.style.padding = newVal + ' ' + this.readMorePaddingLeftRight;
            },
    
            readMorePaddingLeftRight: function (newVal) {
                this.contentEditor.contents.readMore.style.padding = this.readMorePaddingTopBottom + ' ' + newVal;
            },
    
            readMoreStyle: {
                deep: true,
                handler: function (newStyle) {
                    if (!parseInt(newStyle.borderWidth)) {
                        this.contentEditor.contents.readMore.style.borderStyle = 'none';
                    } else {
                        this.contentEditor.contents.readMore.style.borderStyle = 'solid';
                    }
                }
            },
    
            dividerType: function (newType) {
                this.contentEditor.contents.divider.useImage = ('image' === newType);
                this.displayGallery = false;
            },
        },
    
        filters: {
            classify: function (str) {
                return Vue.util.classify(str);
            }
        }
    
    });
    
    Vue.component('content-editor-video', {
        template: ecampVueTemplates['content-editor-video'],
    
        props: ['i18n', 'contentEditor', 'customizerData', 'tinymceSettings'],
    
        data: function () {
            return {
                edgeToEdge: false,
                showLinkError: false,
                ajaxHandler: {
                    abort: function () {}
                }
            };
        },
    
        computed: {
            videoLink: function () {
                return this.contentEditor.contents.video.link;
            },
    
            isValidVideoLink: function () {
                return (this.videoLink.match(/^(http|https)\:\/\/(.*?)\.([a-z]{2})/)) || false;
            }
        },
    
        watch: {
            videoLink: function (newLink) {
                if (this.isValidVideoLink) {
                    var source = '',
                        match = null,
                        videoId = 0,
                        component = this;
    
                    if (newLink.indexOf('youtube') >= 0) {
                        source = 'youtube';
                        match = newLink.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/);
                        videoId = (match&&match[7].length===11)? match[7] : false;
                    } else if (newLink.indexOf('vimeo') >= 0) {
                        source = 'vimeo';
                        match = newLink.match(/vimeo.com\/.*?(\d+)/);
    
                        if (match) {
                            videoId = match[1];
                        }
                    }
    
                    if (source && videoId) {
                        this.showLinkError = true;
                        this.contentEditor.contents.video.image = '';
                        this.contentEditor.contents.video.alt = '';
                        this.ajaxHandler.abort();
    
                        this.ajaxHandler = $.ajax({
                            url: ecampGlobal.ajaxurl,
                            method: 'get',
                            dataType: 'json',
                            data: {
                                action: 'get_video_thumb_from_url',
                                _wpnonce: ecampGlobal.nonce,
                                source: source,
                                videoId: videoId
                            },
                            beforeSend: function () {
                                NProgress.configure({parent: '#editor-video-editor-tab'});
                                NProgress.start();
                                window.setDefaultNProgressParent();
                            }
    
                        }).done(function (response) {
                            NProgress.done();
    
                            if (response.success && response.data) {
                                component.contentEditor.contents.video.image = response.data;
                                component.contentEditor.contents.video.alt = source;
                                component.showLinkError = false;
                            }
                        });
                    }
                } else {
                    this.contentEditor.contents.video.image = '';
                    this.contentEditor.contents.video.alt = '';
                    this.showLinkError = false;
                }
            }
        },
    
        methods: {
            printImage: function (imgObj) {
                if (!imgObj.image) {
                    return '<img src="' + this.customizerData.dummyVideoImage + '">';
    
                } else {
                    return '<img src="' + imgObj.image + '" alt="' + imgObj.alt + '">';
                }
            },
    
            browseImage: function (e) {
                e.preventDefault();
    
                var component = this;
    
                var fileFrame, image;
    
                fileFrame = wp.media.frames.fileFrame = wp.media({
                    frame:    'post',
                    state:    'insert',
                    multiple: false
                });
    
                // insert uploaded file
                fileFrame.on('insert', function() {
    
                    if (fileFrame.state().get('selection')) {
                        image = fileFrame.state().get('selection').first().toJSON();
    
                    } else if (fileFrame.state().get('image')) {
                        image = fileFrame.state().get('image').attributes;
                    }
    
                    component.contentEditor.contents.video.image = image.url;
                    component.contentEditor.contents.video.alt = image.alt ? image.alt : image.url.split('/').pop();
                });
    
                // insert link
                fileFrame.state('embed').on('select', function () {
                    var state = fileFrame.state(),
                    type = state.get('type'),
                    image = state.props.toJSON();
    
                    if ('image' === type) {
                        component.contentEditor.contents.video.image = image.url;
                        component.contentEditor.contents.video.alt = image.alt ? image.alt : image.url.split('/').pop();
                    }
                });
    
                fileFrame.open();
            },
    
            openAttrEditor: function (e, attr) {
                e.preventDefault();
                this.contentEditor.contents.video.openAttrEditor = attr;
            },
    
            setTextAlign: function (align) {
                this.$set('contentEditor.contents.textStyle.textAlign', align);
            },
    
            classifyStr: function (str) {
                return Vue.util.classify(str);
            }
        },
    });
    
    Vue.component('customizer', {
        template: ecampVueTemplates.customizer,
    
        props: ['i18n', 'customizerData', 'emailTemplate'],
    
        data: function () {
            return {
                presets: {},
                showPresetPreview: 0,
                presetPreviewTitle: '',
                previewPresetURL: null,
                previewDevice: 'desktop',
                currentSidebar: 'primary',
                previousSidebar: '',
                primaryTab: 'content',
                secIndex: 0,
                rowIndex: 0,
                highlighter: {
                    top: '50%', left: '50%', width: '0px', height: '0px', opacity: '0', zIndex: '-1'
                },
                contentEditor: {
                    type: '',
                    id: [],
                    headerClass: [],
                    tab: 'content'
                },
                filterBy: Vue.filter('filterBy'),
                templateChooserTab: 'basic',
                categoryFilter: '',
                themeFilter: '',
            };
        },
    
        created: function () {
            if (!this.emailTemplate.text_only && !this.emailTemplate.globalElementStyles.a.color) {
                this.$set('emailTemplate.globalElementStyles.a.color', 'inherit');
            }
        },
    
        computed: {
            sections: function () {
                return this.emailTemplate.sections;
            },
    
            globalCss: function () {
                return this.emailTemplate.globalCss;
            },
    
            tinymceSettings: function () {
                return {
                    shortcodes: this.customizerData.shortcodes,
                    pluginURL: this.customizerData.pluginURL
                };
            },
    
            showTemplateChooser: function () {
                return !(parseInt(this.customizerData.campaignId) || this.customizerData.templateSelected);
            },
    
            baseTemplateImages: function () {
                var images = [],
                    i = 0;
    
                for (i = 0; i < this.customizerData.baseTemplates.length; i++) {
                    var name = this.customizerData.baseTemplates[i].name;
    
                    images.push( this.customizerData.pluginURL + '/assets/images/template-bases/' + name + '.png' );
                }
    
                return images;
            },
    
            themesImages: function () {
                var images = {},
                    component = this;
    
                $.each(this.customizerData.themes, function () {
                    $.each(this.themes, function () {
                        images[this.name] = component.customizerData.pluginURL + '/assets/images/template-themes/' + this.name + '.png';
                    });
                });
    
                return images;
            },
    
            hideCategory: function () {
                var hideCategory = {},
                    component = this;
    
                $.each(this.customizerData.themes, function (categoryId) {
                    hideCategory[categoryId] = false;
    
                    var themes = component.filterBy(this.themes, component.themeFilter, 'title');
    
                    if (!themes.length) {
                        hideCategory[categoryId] = true;
                    }
                });
    
                return hideCategory;
            },
    
            previewIframeStyle: function() {
                return {
                    width: ('mobile' === this.previewDevice) ? '273px' : '100%'
                };
            }
    
        },
    
        watch: {
            currentSidebar: function (newVal, oldVal) {
                this.previousSidebar = oldVal;
            },
    
            'contentEditor.tab': function () {
                $('.sidebar-container').get(0).scrollTop = 0;
            }
        },
    
        events: {
            'open-page-editor': function () {
                this.currentSidebar = 'page';
            },
    
            'open-design-editor': function (secIndex, rowIndex) {
                this.currentSidebar = 'design';
                this.secIndex = secIndex;
                this.rowIndex = rowIndex;
            },
    
            'open-content-editor': function (type, contentId) {
                this.openContentEditor(type, contentId);
            },
    
            'highlight-section': function (secIndex) {
                var section = $('#email-template').children('.wrapper').eq(secIndex),
                    position = section.position(),
                    width = section.outerWidth(true),
                    height = section.outerHeight(true);
    
                this.$set('highlighter', {
                    top: (position.top + 'px') , left: (position.left + 'px'), zIndex: '1',
                    width: (width + 'px'), height: (height + 'px'), opacity: '1'
                });
            },
    
            'highlight-row': function (secIndex, rowIndex) {
                var row = $('#email-template').children().eq(secIndex).find('.section-row').eq(rowIndex),
                    position = row.position(),
                    width = row.outerWidth(true),
                    height = row.outerHeight(true);
    
                this.$set('highlighter', {
                    top: (position.top + 'px') , left: (position.left + 'px'), zIndex: '1',
                    width: (width + 'px'), height: (height + 'px'), opacity: '1'
                });
            },
    
            'hide-highlighter': function () {
                var highlighter = this.highlighter;
    
                this.$set('highlighter', {
                    top: highlighter.top, left: highlighter.left, zIndex: '-1',
                    width: highlighter.width, height: highlighter.height, opacity: '0'
                });
            },
    
            'clone-content': function (contentType, contentId) {
                $('#tiptip_holder').hide();
    
                var toContentIndex = parseInt(contentId[3]) + 1,
                    sections = $.extend(true, [], this.emailTemplate.sections),
                    originalContent = $.extend(true, {}, sections[contentId[0]].rows[contentId[1]].columns[contentId[2]].contents[contentId[3]]);
    
                this.$set('emailTemplate.sections', []);
    
                sections[contentId[0]].rows[contentId[1]].columns[contentId[2]].contents.splice(
                    toContentIndex, 0, originalContent
                );
    
                this.$set('emailTemplate.sections', sections);
                this.currentSidebar = 'primary';
            },
    
            'delete-content': function (contentId) {
                $('#tiptip_holder').hide();
    
                var sections = $.extend(true, [], this.emailTemplate.sections);
                this.$set('emailTemplate.sections', []);
    
                sections[contentId[0]].rows[contentId[1]].columns[contentId[2]].contents.splice(contentId[3], 1);
    
                this.$set('emailTemplate.sections', sections);
                this.currentSidebar = 'primary';
            },
    
            'show-preview-iframe': function (url) {
                this.previewPresetURL = url;
            }
    
        },
    
        methods: {
            // add/sort contents in template stage
            contentDropOperation: function (e, type, addContentTo) {
                var columnId = 0, contentType = '';
    
                var sections = $.extend(true, [], this.emailTemplate.sections);
                this.$set('emailTemplate.sections', []);
    
                // add new content from sidebar
                if ('add' === type) {
                    contentType = e.clone.dataset.contentType;
    
                    columnId = e.target.dataset.columnId.split('-');
    
                    var newDefaultContent = $.extend(true, {}, this.customizerData.contentTypes[contentType].default);
    
                    sections[columnId[0]].rows[columnId[1]].columns[columnId[2]].contents.splice(
                        addContentTo, 0, {type: contentType, content: newDefaultContent}
                    );
    
                // sort/move contents within iframe
                } else if ('sort' === type) {
    
                    var originalEl = e.clone,
                        contentId = originalEl.dataset.contentId.split('-'),
                        clone = e.item,
                        desCol = clone.parentElement,
                        destColId = desCol.dataset.columnId.split('-'),
                        toContentIndex = Array.prototype.indexOf.call(desCol.children, clone);
    
                    // grab the moved content
                    var originalContent = $.extend(true, {},
                        sections[contentId[0]].rows[contentId[1]].columns[contentId[2]].contents[contentId[3]]
                    );
    
                    // remove moved content from old position
                    sections[contentId[0]].rows[contentId[1]].columns[contentId[2]].contents.splice(contentId[3], 1);
    
                    // add moved content in new position
                    sections[destColId[0]].rows[destColId[1]].columns[destColId[2]].contents.splice(
                        toContentIndex, 0, originalContent
                    );
    
                    this.currentSidebar = 'primary';
                }
    
                this.$set('emailTemplate.sections', sections);
    
                // open content editor after adding new content
                if ('add' === type) {
                    var contentOnAddToIndex = sections[columnId[0]].rows[columnId[1]].columns[columnId[2]].contents[addContentTo],
                        newContentId = [];
    
                    if (!contentOnAddToIndex) {
                        addContentTo = sections[columnId[0]].rows[columnId[1]].columns[columnId[2]].contents.length - 1;
                    }
    
                    newContentId = columnId.concat([addContentTo]);
    
                    this.openContentEditor(contentType, newContentId);
                }
            },
    
            openContentEditor: function (type, contentId) {
                this.currentSidebar = 'content-editor';
    
                var headerClass = ['control-header', 'has-title'];
    
                if (!this.customizerData.contentTypes[type].noTabs) {
                    headerClass.push('has-button-group');
                }
    
                if (this.customizerData.contentTypes[type].noStyleSettings) {
                    headerClass.push('no-style-tab');
                }
    
                if (this.customizerData.contentTypes[type].beforeStyleTab) {
                    headerClass.push('has-tab-before-style');
                }
    
                if (this.customizerData.contentTypes[type].noSettingsTab) {
                    headerClass.push('no-settings-tab');
                }
    
                /**
                 * If we open an editor of any type with text-editor while being
                 * open another editor of the same type, tinyMCE content will
                 * not changed. To fix this, we'll first do some reset, wait for 500ms
                 * and then call this method recursively.
                 */
                if (this.contentEditor.type === type) {
                    this.currentSidebar = 'primary';
                    this.contentEditor.type = '';
    
                    NProgress.start();
    
                    var component = this;
                    setTimeout(function() {
                        NProgress.done();
                        component.openContentEditor(type, contentId);
                    }, 500);
    
                } else {
    
                    this.contentEditor = {
                        type: type,
                        id: contentId,
                        headerClass: headerClass,
                        tab: 'content',
                        contents: this.sections[contentId[0]]
                                    .rows[contentId[1]]
                                    .columns[contentId[2]]
                                    .contents[contentId[3]].content
                    };
                }
            },
    
            setBaseTemplate: function (templateId) {
                var component = this;
    
                if (this.presets['preset-' + templateId]) {
                    this.$set('emailTemplate', this.presets['preset-' + templateId]);
                    this.$set('customizerData.templateSelected', true);
                    return;
                }
    
                $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'get',
                    dataType: 'json',
                    data: {
                        action: 'get_template_preset',
                        _wpnonce: ecampGlobal.nonce,
                        templateId: templateId
                    },
                    beforeSend: function (){
                        NProgress.configure({parent: '#template-preset-container-' + templateId + ' > div'});
                        NProgress.start();
                        window.setDefaultNProgressParent();
                    }
    
                }).done(function (response) {
                    NProgress.done();
    
                    if (response.success && response.data.template) {
                        if (response.data.template.text_only) {
                            response.data.template.content = component.wpautop(response.data.template.content, true);
                        }
    
                        component.$set('emailTemplate', response.data.template);
                        component.$set('customizerData.templateSelected', true);
    
                        Vue.nextTick(function () {
                            component.$dispatch('save-campaign-silently');
                        });
                    }
                });
            },
    
            previewPreset: function (e, templateId, title) {
                e.preventDefault();
    
                var component = this;
    
                this.$set('presetPreviewTitle', title);
                if (this.presets['preset-' + templateId]) {
                    this.$set('emailTemplate', this.presets['preset-' + templateId]);
                    this.$set('showPresetPreview', templateId);
                    window.scrollTo(0,0);
                    return;
                }
    
                $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'get',
                    dataType: 'json',
                    data: {
                        action: 'get_template_preset',
                        _wpnonce: ecampGlobal.nonce,
                        templateId: templateId
                    },
                    beforeSend: function (){
                        NProgress.configure({parent: '#template-preset-container-' + templateId + ' > div'});
                        NProgress.start();
                        window.setDefaultNProgressParent();
                    }
    
                }).done(function (response) {
                    NProgress.done();
    
                    if (response.success && response.data.template) {
                        component.storePresetTemplate(templateId, response.data.template);
                        component.$set('emailTemplate', response.data.template);
                        component.$set('showPresetPreview', templateId);
                        window.scrollTo(0,0);
                    }
                });
            },
    
            closePreview: function () {
                this.showPresetPreview = 0;
                this.presetPreviewTitle = '';
                this.previewPresetURL = null;
                this.previewDevice = 'desktop';
            },
    
            // save preset so that we don't have to fetch it from server again
            storePresetTemplate: function (templateId, template) {
                if (!this.presets['preset-' + templateId]) {
                    this.presets['preset-' + templateId] = template;
                }
            },
    
            setTemplateChooserTab: function (e, tab) {
                e.preventDefault();
                this.templateChooserTab = tab;
            },
    
            saveAndClose: function () {
                this.currentSidebar = this.previousSidebar;
    
                this.$dispatch('save-campaign-silently');
            },
    
            _autop_newline_preservation_helper: function (matches) {
                return matches[0].replace( "\n", "<WPPreserveNewline />" );
            },
    
            wpautop: function(pee, br) {
                if(typeof(br) === 'undefined') {
                    br = true;
                }
    
                var pre_tags = {};
                if ( pee.trim() === '' ) {
                    return '';
                }
    
                pee = pee + "\n"; // just to make things a little easier, pad the end
                if ( pee.indexOf( '<pre' ) > -1 ) {
                    var pee_parts = pee.split( '</pre>' );
                    var last_pee = pee_parts.pop();
                    pee = '';
                    pee_parts.forEach(function(pee_part, index) {
                        var start = pee_part.indexOf( '<pre' );
    
                        // Malformed html?
                        if ( start === -1 ) {
                            pee += pee_part;
                            return;
                        }
    
                        var name = "<pre wp-pre-tag-" + index + "></pre>";
                        pre_tags[name] = pee_part.substr( start ) + '</pre>';
                        pee += pee_part.substr( 0, start ) + name;
    
                    });
    
                    pee += last_pee;
                }
    
                pee = pee.replace(/<br \/>\s*<br \/>/, "\n\n");
    
                // Space things out a little
                var allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
                pee = pee.replace( new RegExp('(<' + allblocks + '[^>]*>)', 'gmi'), "\n$1");
                pee = pee.replace( new RegExp('(</' + allblocks + '>)', 'gmi'), "$1\n\n");
                pee = pee.replace( /\r\n|\r/, "\n" ); // cross-platform newlines
    
                if ( pee.indexOf( '<option' ) > -1 ) {
                    // no P/BR around option
                    pee = pee.replace( /\s*<option'/gmi, '<option');
                    pee = pee.replace( /<\/option>\s*/gmi, '</option>');
                }
    
                if ( pee.indexOf('</object>') > -1 ) {
                    // no P/BR around param and embed
                    pee = pee.replace( /(<object[^>]*>)\s*/gmi, '$1');
                    pee = pee.replace( /\s*<\/object>/gmi, '</object>' );
                    pee = pee.replace( /\s*(<\/?(?:param|embed)[^>]*>)\s*/gmi, '$1');
                }
    
                if ( pee.indexOf('<source') > -1 || pee.indexOf('<track') > -1 ) {
                    // no P/BR around source and track
                    pee = pee.replace( /([<\[](?:audio|video)[^>\]]*[>\]])\s*/gmi, '$1');
                    pee = pee.replace( /\s*([<\[]\/(?:audio|video)[>\]])/gmi, '$1');
                    pee = pee.replace( /\s*(<(?:source|track)[^>]*>)\s*/gmi, '$1');
                }
    
                pee = pee.replace(/\n\n+/gmi, "\n\n"); // take care of duplicates
    
                // make paragraphs, including one at the end
                var pees = pee.split(/\n\s*\n/);
                pee = '';
                pees.forEach(function(tinkle) {
                    pee += '<p>' + tinkle.replace( /^\s+|\s+$/g, '' ) + "</p>\n";
                });
    
                pee = pee.replace(/<p>\s*<\/p>/gmi, ''); // under certain strange conditions it could create a P of entirely whitespace
                pee = pee.replace(/<p>([^<]+)<\/(div|address|form)>/gmi, "<p>$1</p></$2>");
                pee = pee.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1", pee); // don't pee all over a tag
                pee = pee.replace(/<p>(<li.+?)<\/p>/gmi, "$1"); // problem with nested lists
                pee = pee.replace(/<p><blockquote([^>]*)>/gmi, "<blockquote$1><p>");
                pee = pee.replace(/<\/blockquote><\/p>/gmi, '</p></blockquote>');
                pee = pee.replace(new RegExp('<p>\s*(</?' + allblocks + '[^>]*>)', 'gmi'), "$1");
                pee = pee.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*</p>', 'gmi'), "$1");
    
                if ( br ) {
                    pee = pee.replace(/<(script|style)(?:.|\n)*?<\/\\1>/gmi, this._autop_newline_preservation_helper); // /s modifier from php PCRE regexp replaced with (?:.|\n)
                    pee = pee.replace(/(<br \/>)?\s*\n/gmi, "<br />\n"); // optionally make line breaks
                    pee = pee.replace( '<WPPreserveNewline />', "\n" );
                }
    
                pee = pee.replace(new RegExp('(</?' + allblocks + '[^>]*>)\s*<br />', 'gmi'), "$1");
                pee = pee.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)/gmi, '$1');
                pee = pee.replace(/\n<\/p>$/gmi, '</p>');
    
                if ( Object.keys(pre_tags).length ) {
                    pee = pee.replace( new RegExp( Object.keys( pre_tags ).join( '|' ), "gi" ), function (matched) {
                        return pre_tags[matched];
                    });
                }
    
                return pee;
            },
        }
    });
    
    /**
     * Editor Script
     *
     * Vue.js main instance
     */
    
    // do not proceed if the current page is not the editing page
    if (!$('#erp-email-campaign-edit').length) {
        return false;
    }
    
    /**
     * Sometimes we must set the parent close to interaction
     * point. In that case we'll choose another parent and after
     * start we'll call this function to set back the default parent
     */
    window.setDefaultNProgressParent = function() {
        NProgress.configure({parent: '#wpadminbar'});
    };
    window.setDefaultNProgressParent();
    
    Vue.config.debug = ecampGlobal.debug;
    
    // make props twoWay binding default
    Vue.config._propBindingModes.ONE_WAY = 1;
    
    // the main vue instance
    window.ecampEditor = new Vue({
        el: '#erp-email-campaign-edit',
    
        data: {
            step: 0,
            formData: {
                isScheduled: false,
            },
            i18n: {},
            urlHash: '',
            customizerData: {},
            emailTemplate: {
                globalCss: {},
                sections: []
            },
            html: '',
        },
    
        ready: function () {
            var editor = this;
    
            // get the initial data via ajax
            $.ajax({
                url: ecampGlobal.ajaxurl,
                method: 'get',
                dataType: 'json',
                data: {
                    action: 'get_campaign_editor_data',
                    _wpnonce: ecampGlobal.nonce,
                    campaignId: $('[name="campaign_id"]').val(),
                    urlHash: window.location.hash
                },
                beforeSend: function () {
                    NProgress.start();
                }
    
            }).done(function (response) {
                NProgress.done();
    
                if (response.success) {
                    editor.$data = response.data;
                }
            });
        },
    
        computed: {
            urlHash: function () {
                var hash = window.location.hash.replace('#', ''),
                    hashObj = {
                        step: this.step,
                        sidebar: 'design'
                    },
                    i = 0;
    
                hash = hash.split('/');
    
                for (i = 0; i < hash.length; i += 2) {
                    hashObj[hash[i]] = hash[i+1];
                }
    
                this.step = parseInt(hashObj.step);
    
                return window.location.hash;
            },
    
            isPreviewBtnDisabled: function () {
                return !parseInt( this.customizerData.campaignId );
            },
    
            isNextBtnDisabled: function () {
                var disabled = true;
    
                if (1 === this.step) {
                    disabled = !this.isStepOneValid;
                } else if (2 === this.step) {
                    disabled = false;
                }
    
                return disabled;
            },
    
            submitBtnLabel: function () {
                if ( 'automatic' === this.formData.send) {
                    return this.i18n.activateNow;
                } else if (this.formData.isScheduled) {
                    return this.i18n.schedule;
                } else {
                    return this.i18n.send;
                }
            },
    
            showPrevBtn: function () {
                return this.step > 1;
            },
    
            showNextBtn: function () {
                return (this.customizerData.hasOwnProperty('campaignId')) && (this.step < 3);
            },
    
            showSubmitBtn: function () {
                if (this.step > 2) {
                    return true;
                }
    
                return false;
            },
    
            showDraftBtn: function () {
                if (this.step > 1) {
                    return true;
                }
    
                return false;
            },
    
            isStepOneValid: function () {
                var isValid = true,
                    anyListSelected = false;
    
                if (!this.formData.subject || !this.formData.send || !this.formData.sender.name || !this.formData.replyTo.name) {
                    isValid = false;
                }
    
                if (this.isInvalidEmail(this.formData.sender.email) || this.isInvalidEmail(this.formData.replyTo.email)) {
                    isValid = false;
                }
    
                if ('automatic' !== this.formData.send) {
                    $.each(this.formData.lists, function (i, list) {
    
                        if (list.selected.length) {
                            $.each(list.selected, function () {
                                var selectedId = parseInt(this);
    
                                $.each(list.lists, function () {
                                    if (selectedId === parseInt(this.id) && this.count) {
                                        anyListSelected = true;
                                    }
                                });
                            });
                        }
                    });
    
                    if (!anyListSelected) {
                        isValid = false;
                    }
    
                } else {
                    if (!this.formData.event.action || !this.formData.event.scheduleType) {
                        isValid = false;
                    }
    
                    if ('immediately' !== this.formData.event.scheduleType && !this.formData.event.scheduleOffset) {
                        isValid = false;
                    }
    
                    if (!this.formData.event.argVal) {
                        isValid = false;
                    }
                }
    
                return isValid;
            },
    
            hideFooterBtns: function () {
                return (2 === this.step) && !(parseInt(this.customizerData.campaignId) || this.customizerData.templateSelected);
            },
    
            automaticPhrase: function () {
                if (!this.formData.event.action) {
                    return null;
                }
    
                var editor = this,
                    title = this.automaticActions[this.formData.event.action].replace('when', ''),
                    scheduleOffset = this.formData.event.scheduleOffset,
                    scheduleType = this.formData.event.scheduleType,
                    argVal = '';
    
                if ('erp_crm_create_contact_subscriber' === this.formData.event.action) {
                    $.each(this.formData.lists.contact_groups.lists, function () {
                        if ( parseInt(this.id) === parseInt(editor.formData.event.argVal) ) {
                            return argVal = this.name;
                        }
                    });
    
                } else if ('erp_create_new_people' === this.formData.event.action) {
                    argVal = Vue.util.classify(this.formData.event.argVal);
                }
    
                var text = 'automatically send an email ';
    
                if ('immediately' === scheduleType) {
                    text += '<strong>immediately</strong> after';
                } else {
                    text += '<strong>' + scheduleOffset + ' ' + scheduleType + '(s)</strong> after';
                }
    
                text += title + ' <strong>' + argVal + '</strong>';
    
                return text;
            },
    
            currentLocalTime: function () {
                return ecampGlobal.date.placeholder + ' ' + ecampGlobal.time.placeholder;
            },
    
            isSubmitBtnDisabled: function () {
                if ('automatic' === this.formData.send && !this.formData.event.argVal) {
                    return true;
    
                } else if ('automatic' !== this.formData.send && this.formData.isScheduled && !(this.formData.schedule.date && this.formData.schedule.time)) {
                    return true;
                }
    
                return false;
            }
        },
    
        watch: {
            step: function () {
                this.scrollToTop();
                this.setURLHash();
            },
    
            'formData.isScheduled': function (newVal) {
                if ('automatic' !== this.formData.send && newVal) {
                    this.formData.send = 'scheduled';
                } else if ('automatic' !== this.formData.send && !newVal) {
                    this.formData.send = 'immediately';
                }
            }
        },
    
        methods: {
            preventFormSubmission: function (e) {
                e.preventDefault();
    
                return false;
            },
    
            scrollToTop: function () {
                window.scrollTo(0,0);
            },
    
            setURLHash: function () {
                var hash = '',
                    hashArgs = [];
    
                if (this.step) {
                    hashArgs.push('step/' + this.step);
                }
    
                if (hashArgs.length) {
                    hash = '#' + hashArgs.join('/');
                }
    
                if (hash) {
                    window.location.hash = hash;
                }
            },
    
            setCampaignHTML: function () {
                var clone = $('#email-template-container').clone();
    
                // remove dummy contents
                clone.find('[data-type="dummy"]').remove();
    
                // remove unnecessary elements
                clone.find('.remove-el-on-save').remove();
    
                // remove comments
                this.html = clone.html().replace(/<!--.*?-->/gm, '');
            },
    
            saveCampaign: function (saveAsDraft, isSilent) {
                var editor = this;
    
                var status = 'in_progress';
    
                if (saveAsDraft) {
                    status = 'draft';
    
                } else if ('automatic' === this.formData.send) {
                    status = 'active';
                }
    
                // for text-only templates remove max-width: 600px container
                if (editor.emailTemplate.text_only && 'email-template' === $(editor.html).attr('id')) {
                    editor.html = '<div>' + $(editor.html).children().html() + '</div>';
                }
    
                // get the initial data via ajax
                $.ajax({
                    url: ecampGlobal.ajaxurl,
                    method: 'post',
                    dataType: 'json',
                    data: {
                        action: 'save_campaign',
                        _wpnonce: ecampGlobal.nonce,
                        campaign_id: $('[name="campaign_id"]').val(),
                        form_data: editor.formData,
                        email_template: JSON.stringify(editor.emailTemplate),
                        html: editor.html,
                        status: status,
                        urlHash: window.location.hash,
                        log_campaign: isSilent ? 0 : 1
                    },
                    beforeSend: function () {
                        if (!isSilent) {
                            NProgress.start();
                        }
                    }
    
                }).done(function (response) {
                    if (!isSilent) {
                        NProgress.done();
                    }
    
                    if (response.success) {
                        if (response.data.redirectTo) {
                            window.location.href = response.data.redirectTo;
    
                        } else {
                            if (!isSilent) {
                                swal({
                                    title: '',
                                    text: response.data.msg,
                                    type: 'success',
                                    confirmButtonText: editor.i18n.ok,
                                    confirmButtonColor: '#0073aa'
                                });
                            }
    
                            if (response.data.campaignId) {
                                $('[name="campaign_id"]').val(response.data.campaignId);
                                history.pushState({}, null, response.data.page + window.location.hash);
                                editor.customizerData.campaignId = response.data.campaignId;
                            }
                        }
                    }
                });
            },
    
            saveCampaignSilently: function () {
                this.setCampaignHTML();
                this.saveCampaign(true, true);
            },
    
            goToNextStep: function (step) {
                if (2 === step) {
                    this.saveCampaignSilently();
                }
    
                ++this.step;
            },
    
            goToPreviewPage: function () {
                var link = this.customizerData.siteURL + '?erp-email-campaign=1&view-email-in-browser=1&campaign=' + this.customizerData.campaignId;
    
                window.open(link, '_blank');
            },
    
            sendPreviewEmail: function () {
                var editor = this;
    
                swal({
                    title: editor.i18n.sendPreview,
                    type: 'input',
                    showCancelButton: true,
                    closeOnConfirm: false,
                    inputPlaceholder: 'email@example.com',
                    showLoaderOnConfirm: true,
    
                }, function(inputValue){
    
                    if (!inputValue) {
                        swal.showInputError(editor.i18n.writeYourEmail);
                        return false;
                    }
    
                    $.ajax({
                        url: ecampGlobal.ajaxurl,
                        method: 'post',
                        dataType: 'json',
                        data: {
                            action: 'send_preview_email',
                            _wpnonce: ecampGlobal.nonce,
                            campaign_id: $('[name="campaign_id"]').val(),
                            email: inputValue
                        },
    
                    }).done(function (response) {
                        $('#test-mail-recipient').val(null);
    
                        swal({
                            title: '',
                            text: response.data,
                            type: response.success ? 'success' : 'error',
                            confirmButtonText: editor.i18n.ok,
                            confirmButtonColor: '#0073aa'
                        });
                    });
    
                });
            },
    
            isListSelected: function (type, listId) {
                return (this.formData.lists[type].selected.indexOf(listId) >= 0);
            },
    
            isInvalidEmail: function (email) {
                return !(/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));
            }
        },
    
        events: {
            'save-campaign-silently': function () {
                this.saveCampaignSilently();
            }
        }
    });
    
})(jQuery);
