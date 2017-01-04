<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;


use Event_Management_System;

class EventIconLegendController extends AbstractShortcodeController
{
    public function printContent($atts = [], $content = null)
    {
        $imagePath = $this->getAssetsUrl() . "img/";
        ?>
        <div id="ems_event_icon_legend" class="ems_event_icon_legend_wrapper">
            <h2>Legende</h2>

            <div class="ems_event_icon_legend_column">
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Einsteiger:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Genussflieger:</span>
                    <img src="<?php echo $imagePath ?>participant_level_intermediate_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Ambitionierte:</span>
                    <img src="<?php echo $imagePath ?>participant_level_pro_yes.png">
                </div>
            </div>
            <div class="ems_event_icon_legend_column">
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Teilweise geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_partly.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Nicht geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_no.png">
                </div>
            </div>
        </div>
        <?php
    }
}