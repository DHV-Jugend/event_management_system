<?php
namespace BIT\EMS\Domain\Repository;

use BIT\EMS\Settings\Tab\PagesTab;

/**
 * @author Christoph Bessei
 */
class PageRepository
{
    public static function findEventListPageId()
    {
        return PagesTab::get(PagesTab::EVENT_LIST);
    }

    public static function findEventParticipantsListPageId()
    {
        return PagesTab::get(PagesTab::EVENT_PARTICIPANTS_LIST);
    }

    public static function findEventStatisticsPageId()
    {
        return PagesTab::get(PagesTab::EVENT_STATISTICS);
    }

    public static function findUserRegistrationsPageId()
    {
        return PagesTab::get(PagesTab::USER_REGISTRATIONS_LIST);
    }
}
