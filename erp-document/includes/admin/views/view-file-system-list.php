<div id="primary" class="file-primary content-area">
    <h2><?php _e( 'Employee Documents', 'erp-document' );?></h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">
            <div id="file_folder_wrapper" class="not-loaded">
                <div transition="fade" v-bind:class="[ isError ? error_notice_class : success_notice_class ]" v-show="isVisible">{{ response_message }}</div>

                <div id="dir-file-operation-control">
                    <div id="search-feature">
                        <input v-on:keyup.enter="searchNow" v-model="searchInput" type="text" name="search_input" id="search_input" placeholder="Search">
                    </div>
                    <div id="browse-location">
                        <ul id="file_browser_breadcrumb_list">
                            <li v-for="b_data in breadcurm_data">
                                <a href="#" data-id="{{ b_data.indexx }}" @click.prevent="bdir_link_clicked( b_data.indexx, $index, $event )">{{ b_data.text }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="doc-attachment-area alignright">
                        <?php erp_rec_upload_field( 'cm', []); ?>
                    </div>
                    <div class="fileUpload create_folder button alignright" @click="getPrompt">
                        <span class="fa fa-lg fa-folder-open-o"></span>
                        <span class="fu_text"><?php _e('Create Folder','wp-erp-doc');?></span>
                        <input id="btn_create_folder" type="button" name="btn_create_folder" class="upload" />
                    </div>
                    <div class="fileUpload create_folder button alignright" v-show="isDeleteMoveButtonShow" @click="moveSelectedDirFile">
                        <span class="fa fa-lg fa-cut"></span>
                        <span class="fu_text"><?php _e('Move to','wp-erp-doc');?></span>
                        <input id="btn_moveto_folder" type="button" name="btn_moveto_folder" class="upload" />
                    </div>
                    <div class="fileUpload create_folder button alignright" v-show="isDeleteMoveButtonShow" @click="deleteSelectedDirFile">
                        <span class="fa fa-lg fa-remove"></span>
                        <span class="fu_text"><?php _e('Delete','wp-erp-doc');?></span>
                        <input id="btn_delete_folder" type="button" name="btn_delete_folder" class="upload" />
                    </div>
                </div>

                <div id="loader_wrapper">
                    <span class="spinner is-active"></span>
                    <!--<div id="loader_gif"></div>-->
                </div>

                <div id="checkbox_all">
                    <label class="header_caption header_caption_checkall"><input v-model="checkall" id="checkall" type="checkbox" @click="selectAll"><?php _e('Check All', 'wp-erp-doc');?></label>
                    <label class="header_caption header_caption_mat"><?php _e( 'Modified', 'wp-erp-doc' );?></label>
                    <label class="header_caption header_caption_mat"><?php _e( 'Created by', 'wp-erp-doc' );?></label>
                    <label class="header_caption header_caption_fsize"><?php _e( 'File size', 'wp-erp-doc' );?></label>
                </div>
                <div class="ffmodal">
                    <ul id="file_folder_list">
                        <li v-for="d_data in dir_data" class="file_folder_list_item sr-{{$index}}" v-on:dblclick="renameNow('folder', $index)">
                            <div class="filename-col">
                                <input type="checkbox" value="{{ d_data.dir_id }}" class="dir_file_chkbox dir_chkbox" v-model="dir_file_checkboxx">
                                <img class="dir_icon" src="<?php echo WPERP_DOC_ASSETS . "/images/dir.png";?>" alt="Folder">
<!--                                    <a class="file_dir_link dir-link" href="#" @click.prevent="dir_link_clicked( d_data.dir_id, d_data.dir_name, $event )">{{ d_data.dir_name }}</a>-->
                                <rename-component :should-stop.sync="shouldStop" :d_data="d_data" :listcurrentindex="$index" track-by="$index"></rename-component>
                            </div>
                            <div class="modified">
                                <span class="modified-time">{{ d_data.updated_at }}</span>
                            </div>
                            <div class="modified">
                                <span class="modified-time">
                                    <a class="userlink" href="{{ d_data.user_link }}?user_id={{d_data.user_id}}">{{ d_data.user_nicename }}</a>
                                </span>
                            </div>
                        </li>

                        <li v-for="f_data in file_data" class="file_folder_list_item sr-{{$index}}" v-on:dblclick="renameNow('file', $index)">
                            <div class="filename-col">
                                <input type="checkbox" value="{{ f_data.dir_id }}" class="dir_file_chkbox file_chkbox" v-model="dir_file_checkboxx">
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'image/jpeg' || f_data.attachment_type == 'image/png' ">
                                    <div class="dir_file_icon image_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'audio/mpeg' ">
                                    <div class="dir_file_icon audio_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'video/mp4' || f_data.attachment_type == 'video/x-flv' ">
                                    <div class="dir_file_icon video_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'application/zip' ">
                                    <div class="dir_file_icon zip_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'text/plain' || f_data.attachment_type == 'application/rtf' ">
                                    <div class="dir_file_icon text_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'application/pdf' ">
                                    <div class="dir_file_icon pdf_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'application/msword' || f_data.attachment_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ">
                                    <div class="dir_file_icon office_word_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'application/vnd.ms-excel' || f_data.attachment_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ">
                                    <div class="dir_file_icon office_excel_file_icon"></div>
                                </div>
                                <div class="dir_file_icon_wrapper" v-if=" f_data.attachment_type == 'application/vnd.ms-powerpoint' || f_data.attachment_type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation' ">
                                    <div class="dir_file_icon office_powerpoint_file_icon"></div>
                                </div>
<!--                                    <a class="file_dir_link file-link" href="{{ f_data.attactment_url }}">{{ f_data.file_name }}</a>-->
                                <rename-component :should-stop.sync="shouldStop" :d_data="f_data" :listcurrentindex="$index" track-by="$index"></rename-component>
                            </div>
                            <div class="modified">
                                <span class="modified-time">{{ f_data.updated_at }}</span>
                            </div>
                            <div class="modified">
                                <span class="modified-time">
                                    <a class="userlink" href="{{ f_data.user_link }}?user_id={{f_data.user_id}}">{{ f_data.user_nicename }}</a>
                                </span>
                            </div>
                            <div class="fsize">
                                <span class="fsizek">{{ f_data.file_size }}</span>
                            </div>
                        </li>
                    </ul>
                    <div class="no_file_folder_wrapper not-loaded" v-show="dir_data.length == 0 && file_data.length == 0">
                        <span class="dashicons dashicons-portfolio"></span>
                        <h4><?php _e('This folder is empty', 'wp-erp-doc');?></h4>
                    </div>
                </div>
            </div>
        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div><!-- .content-area -->