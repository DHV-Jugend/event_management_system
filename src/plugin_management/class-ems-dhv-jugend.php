<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Dhv_Jugend
{
    public static function add_participant_type_meta_box(WP_Post $post)
    {
        $fieldName = "ems_participant_type";
        wp_nonce_field($fieldName . '_meta_box', $fieldName . '_meta_box_nonce');
        $event = Ems_Event::get_event($post->ID);
        $participantTypes = $event->getParticipantTypes();
        $possibleValues = [0, 1];

        $possibleTypes = [
            new Ems_Participant_Type("Gleitschirm", null, "paraglider"),
            new Ems_Participant_Type("Drachen", null, "hangglider"),
            new Ems_Participant_Type("Fußgänger", null, "pedestrian"),
        ];
        /** @var Ems_Participant_Type $possibleType */
        foreach ($possibleTypes as $possibleType) {
            $label = $possibleType->getLabel();
            $key = $possibleType->getKey();
            ?>
            <p>
                <label for="<?php echo $fieldName ?>[<?php echo $key ?>]"><?php echo $label ?></label><br>
                <select id="<?php echo $fieldName ?>[<?php echo $key ?>]"
                        name="<?php echo $fieldName ?>[<?php echo $key ?>]">
                    <?php
                    $currentValue = "";
                    if (is_object($participantTypes[$key])) {
                        $currentValue = $participantTypes[$key]->getValue();
                    }
                    foreach ($possibleValues as $possibleValue) {
                        ?>
                        <option <?php echo ($possibleValue == $currentValue) ? "selected" : ""; ?>
                                value="<?php echo $possibleValue ?>"><?php echo ($possibleValue) ? "Ja" : "Nein" ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <?php
        }
    }

    /**
     * Add participant level meta box to post
     * @param WP_Post $post
     */
    public static function add_participant_level_meta_box(WP_Post $post)
    {
        $fieldName = "ems_participant_level";
        wp_nonce_field($fieldName . '_meta_box', $fieldName . '_meta_box_nonce');
        $event = Ems_Event::get_event($post->ID);
        $participantLevels = $event->getParticipantLevels();
        $possibleValues = [0, 0.5, 1];
        $possibleLevels = [
            new Ems_Participant_Level("Anfänger", null, "beginner"),
            new Ems_Participant_Level("Fortgeschritten", null, "intermediate"),
            new Ems_Participant_Level("Profi", null, "pro"),
        ];

        /** @var Ems_Participant_Level $level */
        foreach ($possibleLevels as $level) {
            $label = $level->getLabel();
            $key = $level->getKey();
            ?>
            <p>
                <label for="<?php echo $fieldName ?>[<?php echo $key ?>]"><?php echo $label ?></label><br>
                <select id="<?php echo $fieldName ?>[<?php echo $key ?>]"
                        name="<?php echo $fieldName ?>[<?php echo $key ?>]">
                    <?php
                    $currentValue = "";
                    if (is_object($participantLevels[$key])) {
                        $currentValue = $participantLevels[$key]->getValue();
                    }
                    foreach ($possibleValues as $possibleValue) {
                        ?>
                        <option <?php echo ($possibleValue == $currentValue) ? "selected" : ""; ?>
                                value="<?php echo $possibleValue ?>"><?php echo $possibleValue ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <?php
        }
    }

    public static function remove_metabox_layout()
    {
        remove_meta_box('layout_sectionid', Ems_Event::get_post_type(), 'normal');
        remove_meta_box('slider_sectionid', Ems_Event::get_post_type(), 'normal');
    }

    /**
     * Replaces the WP Submit Meta Box in Events (removes visibility setting)
     *
     * @param WP_Post $post
     */
    public function post_submit_meta_box_fum(WP_Post $post)
    {
        global $action;


        $post_type = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);

        $args = null;
        if (post_type_supports($post_type, 'revisions') && 'auto - draft' != $post->post_status) {
            $revisions = wp_get_post_revisions($post->ID);

            // We should aim to show the revisions metabox only when there are revisions.
            if (count($revisions) > 1) {
                reset($revisions); // Reset pointer for key()
                $args = ['revisions_count' => count($revisions), 'revision_id' => key($revisions)];
                add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', null, 'normal', 'core');
            }
        }

        ?>
        <div class="submitbox" id="submitpost">

            <div id="minor-publishing">

                <?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key
                ?>
                <div style="display:none;">
                    <?php submit_button(__('Save'), 'button', 'save'); ?>
                </div>

                <div id="minor-publishing-actions">
                    <div id="save-action">
                        <?php if ('publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status) { ?>
                            <input <?php if ('private' == $post->post_status) { ?>style="display:none"<?php } ?>
                                   type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>"
                                   class="button"/>
                            <?php
                        } elseif ('pending' == $post->post_status && $can_publish) {
                            ?>
                            <input type="submit" name="save" id="save-post"
                                   value="<?php esc_attr_e('Save as Pending'); ?>" class="button"/>
                        <?php } ?>
                        <span class="spinner"></span>
                    </div>
                    <?php if ($post_type_object->public) : ?>
                        <div id="preview-action">
                            <?php
                            if ('publish' == $post->post_status) {
                                $preview_link = esc_url(get_permalink($post->ID));
                                $preview_button = __('Preview Changes');
                            } else {
                                $preview_link = set_url_scheme(get_permalink($post->ID));
                                $preview_link = esc_url(
                                    apply_filters('preview_post_link', add_query_arg('preview', 'true', $preview_link))
                                );
                                $preview_button = __('Preview');
                            }
                            ?>
                            <a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview"
                               id="post-preview"><?php echo $preview_button; ?></a>
                            <input type="hidden" name="wp-preview" id="wp-preview" value=""/>
                        </div>
                    <?php endif; // public post type
                    ?>
                    <div class="clear"></div>
                </div>
                <!-- #minor-publishing-actions -->

                <div id="misc-publishing-actions">

                    <div class="misc-pub-section misc-pub-post-status">
                        <label for="post_status"><?php _e('Status:') ?></label>
                        <span id="post-status-display">
<?php
switch ($post->post_status) {
    case 'private':
        _e('Privately Published');
        break;
    case 'publish':
        _e('Published');
        break;
    case 'future':
        _e('Scheduled');
        break;
    case 'pending':
        _e('Pending Review');
        break;
    case 'draft':
    case 'auto - draft':
        _e('Draft');
        break;
}
?>
</span>
                        <?php if ('publish' == $post->post_status || 'private' == $post->post_status || $can_publish) { ?>
                            <a href="#post_status" <?php if ('private' == $post->post_status) { ?>style="display:none;"
                               <?php } ?>class="edit-post-status hide-if-no-js"><?php _e('Edit') ?></a>

                            <div id="post-status-select" class="hide-if-js">
                                <input type="hidden" name="hidden_post_status" id="hidden_post_status"
                                       value="<?php echo esc_attr(
                                           ('auto - draft' == $post->post_status) ? 'draft' : $post->post_status
                                       ); ?>"/>
                                <select name='post_status' id='post_status'>
                                    <?php if ('publish' == $post->post_status) : ?>
                                        <option<?php selected($post->post_status, 'publish'); ?>
                                                value='publish'><?php _e('Published') ?></option>
                                    <?php elseif ('private' == $post->post_status) : ?>
                                        <option<?php selected($post->post_status, 'private'); ?>
                                                value='publish'><?php _e('Privately Published') ?></option>
                                    <?php
                                    elseif ('future' == $post->post_status) : ?>
                                        <option<?php selected($post->post_status, 'future'); ?>
                                                value='future'><?php _e('Scheduled') ?></option>
                                    <?php endif; ?>
                                    <option<?php selected($post->post_status, 'pending'); ?>
                                            value='pending'><?php _e('Pending Review') ?></option>
                                    <?php if ('auto - draft' == $post->post_status) : ?>
                                        <option<?php selected($post->post_status, 'auto - draft'); ?>
                                                value='draft'><?php _e('Draft') ?></option>
                                    <?php else : ?>
                                        <option<?php selected($post->post_status, 'draft'); ?>
                                                value='draft'><?php _e('Draft') ?></option>
                                    <?php endif; ?>
                                </select>
                                <a href="#post_status"
                                   class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
                                <a href="#post_status"
                                   class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
                            </div>

                        <?php } ?>
                    </div>
                    <!-- .misc-pub-section -->


                    <?php
                    // translators: Publish box date format, see http://php.net/date
                    $datef = __('M j, Y @ G:i');
                    if (0 != $post->ID) {
                        if ('future' == $post->post_status) { // scheduled for publishing at a future date
                            $stamp = __(
                                'Scheduled for: <
					b >%1$s </b > '
                            );
                        } else {
                            if ('publish' == $post->post_status || 'private' == $post->post_status) { // already published
                                $stamp = __('Published on: <b >%1$s </b > ');
                            } else {
                                if ('0000 - 00 - 00 00:00:00' == $post->post_date_gmt) { // draft, 1 or more saves, no date specified
                                    $stamp = __('Publish < b>immediately </b > ');
                                } else {
                                    if (time() < strtotime(
                                            $post->post_date_gmt . ' + 0000'
                                        )) { // draft, 1 or more saves, future date specified
                                        $stamp = __(
                                            'Schedule for: <
						b >%1$s </b > '
                                        );
                                    } else { // draft, 1 or more saves, date specified
                                        $stamp = __('Publish on: <b >%1$s </b > ');
                                    }
                                }
                            }
                        }
                        $date = date_i18n($datef, strtotime($post->post_date));
                    } else { // draft (no saves, and thus no date specified)
                        $stamp = __('Publish < b>immediately </b > ');
                        $date = date_i18n($datef, strtotime(current_time('mysql')));
                    }

                    if (!empty($args['args']['revisions_count'])) :
                        $revisions_to_keep = wp_revisions_to_keep($post);
                        ?>
                        <div class="misc-pub-section misc-pub-revisions">
                            <?php
                            if ($revisions_to_keep > 0 && $revisions_to_keep <= $args['args']['revisions_count']) {
                                echo ' < span title = "' . esc_attr(
                                        sprintf(
                                            __('Your site is configured to keep only the last %s revisions.'),
                                            number_format_i18n($revisions_to_keep)
                                        )
                                    ) . '" > ';
                                printf(
                                    __('Revisions: %s'),
                                    ' < b>' . number_format_i18n($args['args']['revisions_count']) . ' +</b > '
                                );
                                echo '</span > ';
                            } else {
                                printf(
                                    __('Revisions: %s'),
                                    ' < b>' . number_format_i18n($args['args']['revisions_count']) . ' </b > '
                                );
                            }
                            ?>
                            <a class="hide-if-no-js"
                               href="<?php echo esc_url(
                                   get_edit_post_link($args['args']['revision_id'])
                               ); ?>"><?php _ex('Browse', 'revisions'); ?></a>
                        </div>
                    <?php endif;

                    if ($can_publish) : // Contributors don't get to choose the date of publish
                        ?>
                        <div class="misc-pub-section curtime misc-pub-curtime">
                        <span id="timestamp">
	<?php printf($stamp, $date); ?></span>
                        <a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><?php _e('Edit') ?></a>

                        <div id="timestampdiv" class="hide-if-js"><?php touch_time(($action == 'edit'), 1); ?></div>
                        </div><?php // /misc-pub-section
                        ?><?php endif; ?>

                    <?php do_action('post_submitbox_misc_actions'); ?>
                </div>
                <div class="clear"></div>
            </div>

            <div id="major-publishing-actions">
                <?php do_action('post_submitbox_start'); ?>
                <div id="delete-action">
                    <?php
                    if (current_user_can("delete_post", $post->ID)) {
                        if (!EMPTY_TRASH_DAYS) {
                            $delete_text = __('Delete Permanently');
                        } else {
                            $delete_text = __('Move to Trash');
                        }
                        ?>
                        <a class="submitdelete deletion"
                           href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
                    } ?>
                </div>

                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php
                    if (!in_array($post->post_status, ['publish', 'future', 'private']) || 0 == $post->ID) {
                        if ($can_publish) :
                            if (!empty($post->post_date_gmt) && time() < strtotime(
                                    $post->post_date_gmt . ' +0000'
                                )) : ?>
                                <input name="original_publish" type="hidden" id="original_publish"
                                       value="<?php esc_attr_e('Schedule') ?>"/>
                                <?php submit_button(
                                    __('Schedule'),
                                    'primary button-large',
                                    'publish',
                                    false,
                                    ['accesskey' => 'p']
                                ); ?><?php else : ?>
                                <input name="original_publish" type="hidden" id="original_publish"
                                       value="<?php esc_attr_e('Publish') ?>"/>
                                <?php submit_button(
                                    __('Publish'),
                                    'primary button-large',
                                    'publish',
                                    false,
                                    ['accesskey' => 'p']
                                ); ?><?php endif;
                        else : ?>
                            <input name="original_publish" type="hidden" id="original_publish"
                                   value="<?php esc_attr_e('Submit for Review') ?>"/>
                            <?php submit_button(
                                __('Submit for Review'),
                                'primary button-large',
                                'publish',
                                false,
                                ['accesskey' => 'p']
                            ); ?><?php
                        endif;
                    } else {
                        ?>
                        <input name="original_publish" type="hidden" id="original_publish"
                               value="<?php esc_attr_e('Update') ?>"/>                        <input name="save"
                                                                                                     type="submit"
                                                                                                     class="button button-primary button-large"
                                                                                                     id="publish"
                                                                                                     accesskey="p"
                                                                                                     value="<?php esc_attr_e(
                                                                                                         'Update'
                                                                                                     ) ?>"/>
                        <?php
                    } ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }
} 