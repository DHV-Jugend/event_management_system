<?php

namespace BIT\EMS\View;

/**
 * @author Christoph Bessei
 * @version
 */
class EventHeaderView extends BaseView
{
    public function printContent()
    {
        ?>
        <p><i><?php echo $this->arguments["dateString"] ?></i></p>
        <div class="ems_single_event_icon_wrapper">
            <div class="ems_single_event_icon_column_wrapper">
                <?php
                foreach ($this->arguments["participantLevelIcons"] as $participantLevelIcon) {
                    ?><img title="<?php echo $participantLevelIcon["title"] ?>"
                           class="masterTooltip ems_event_header_icon"
                           src="<?php echo $participantLevelIcon["path"] ?>"/><?php
                }
                ?>
            </div>
            <div class="ems_single_event_icon_column_wrapper">
                <?php
                foreach ($this->arguments["participantTypeIcons"] as $participantTypeIcon) {
                    ?><img title="<?php echo $participantTypeIcon["title"] ?>"
                           class="masterTooltip ems_event_header_icon"
                           src="<?php echo $participantTypeIcon["path"] ?>"/><?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}