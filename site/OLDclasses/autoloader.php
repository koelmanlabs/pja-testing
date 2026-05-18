<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * KL Events legacy class autoloader.
 * Replaces the removed addIncludePath() from Joomla 6.
 */

defined('_JEXEC') or die;

spl_autoload_register(function (string $class): void
{
    if (strpos($class, 'Planjeagenda') !== 0) {
        return;
    }

    $sitePath  = JPATH_SITE . '/components/com_planjeagenda';
    $adminPath = JPATH_ADMINISTRATOR . '/components/com_planjeagenda';

    // Detect admin context - use Joomla's application client detection
    // This is more reliable than SCRIPT_FILENAME on Windows (backslash issues)
    try {
        $isAdmin = \Joomla\CMS\Factory::getApplication()->isClient('administrator');
    } catch (\Throwable $e) {
        // Fallback to path detection if application not ready
        $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $adminPath2  = str_replace('\\', '/', JPATH_ADMINISTRATOR ?? '');
        $isAdmin = $adminPath2 && strpos($scriptFile, $adminPath2) !== false;
    }

    // Admin-only classes
    $adminOnly = [
        'PlanjeagendaControllerSettings'    => $adminPath . '/controllers/settings.php',
        'PlanjeagendaControllerSource'      => $adminPath . '/controllers/source.php',
        'PlanjeagendaControllerAttachments' => $adminPath . '/controllers/attachments.php',
        'PlanjeagendaControllerAttendee'    => $adminPath . '/controllers/attendee.php',
        'PlanjeagendaControllerAttendees'   => $adminPath . '/controllers/attendees.php',
        'PlanjeagendaControllerCategory'    => $adminPath . '/controllers/category.php',
        'PlanjeagendaControllerCategories'  => $adminPath . '/controllers/categories.php',
        'PlanjeagendaControllerCssmanager'  => $adminPath . '/controllers/cssmanager.php',
        'PlanjeagendaControllerEvents'      => $adminPath . '/controllers/events.php',
        'PlanjeagendaControllerExport'      => $adminPath . '/controllers/export.php',
        'PlanjeagendaControllerGroup'       => $adminPath . '/controllers/group.php',
        'PlanjeagendaControllerGroups'      => $adminPath . '/controllers/groups.php',
        'PlanjeagendaControllerHousekeeping'=> $adminPath . '/controllers/housekeeping.php',
        'PlanjeagendaControllerImagehandler'=> $adminPath . '/controllers/imagehandler.php',
        'PlanjeagendaControllerImport'      => $adminPath . '/controllers/import.php',
        'PlanjeagendaControllerPlugins'     => $adminPath . '/controllers/plugins.php',
        'PlanjeagendaControllerSampledata'  => $adminPath . '/controllers/sampledata.php',
        'PlanjeagendaControllerUpdatecheck' => $adminPath . '/controllers/updatecheck.php',
        'PlanjeagendaModelAdmin'            => $adminPath . '/models/admin.php',
        'PlanjeagendaModelCategoryelement'  => $adminPath . '/models/categoryelement.php',
        // PlanjeagendaModelCategory -> context-sensitive (see below)
        'PlanjeagendaModelCssmanager'       => $adminPath . '/models/cssmanager.php',
        'PlanjeagendaModelEventelement'     => $adminPath . '/models/eventelement.php',
        'PlanjeagendaModelExport'           => $adminPath . '/models/export.php',
        'PlanjeagendaModelGroup'            => $adminPath . '/models/group.php',
        'PlanjeagendaModelGroups'           => $adminPath . '/models/groups.php',
        'PlanjeagendaModelHelp'             => $adminPath . '/models/help.php',
        'PlanjeagendaModelHousekeeping'     => $adminPath . '/models/housekeeping.php',
        'PlanjeagendaModelImagehandler'     => $adminPath . '/models/imagehandler.php',
        'PlanjeagendaModelImport'           => $adminPath . '/models/import.php',
        'PlanjeagendaModelMain'             => $adminPath . '/models/main.php',
        'PlanjeagendaModelSampledata'       => $adminPath . '/models/sampledata.php',
        'PlanjeagendaModelSettings'         => $adminPath . '/models/settings.php',
        'PlanjeagendaModelSource'           => $adminPath . '/models/source.php',
        'PlanjeagendaModelUpdatecheck'      => $adminPath . '/models/updatecheck.php',
        'PlanjeagendaModelUserelement'      => $adminPath . '/models/userelement.php',
        'PlanjeagendaModelUsers'            => $adminPath . '/models/users.php',
        'PlanjeagendaModelVenueelement'     => $adminPath . '/models/venueelement.php',
        'PlanjeagendaContactelement'        => $adminPath . '/models/contactelement.php',
        'PlanjeagendaModelContactelement'   => $adminPath . '/models/contactelement.php',
    ];

    // Context-sensitive classes (admin version used in admin, site version on frontend)
    $contextSensitive = [
        'PlanjeagendaControllerEvent'       => [$adminPath . '/controllers/event.php',    $sitePath . '/controllers/event.php'],
        'PlanjeagendaControllerVenue'       => [$adminPath . '/controllers/venue.php',    $sitePath . '/controllers/venue.php'],
        'PlanjeagendaModelAttendee'         => [$adminPath . '/models/attendee.php',      $sitePath . '/models/attendee.php'],
        'PlanjeagendaModelCategory'         => [$adminPath . '/models/category.php',      $sitePath . '/models/category.php'],
        'PlanjeagendaModelAttendees'        => [$adminPath . '/models/attendees.php',     $sitePath . '/models/attendees.php'],
        'PlanjeagendaModelCategories'       => [$adminPath . '/models/categories.php',    $sitePath . '/models/categories.php'],
        'PlanjeagendaModelCategoriesFrontend' => [$sitePath . '/models/categories.php',     $sitePath . '/models/categories.php'],
        'PlanjeagendaModelEvent'            => [$adminPath . '/models/event.php',         $sitePath . '/models/event.php'],
        'PlanjeagendaModelEvents'           => [$adminPath . '/models/events.php',        $sitePath . '/models/eventslist.php'],
        'PlanjeagendaModelVenue'            => [$adminPath . '/models/venue.php',         $sitePath . '/models/venue.php'],
        'PlanjeagendaModelVenues'           => [$adminPath . '/models/venues.php',        $sitePath . '/models/venues.php'],
        'PlanjeagendaViewAttendees'         => [$adminPath . '/views/attendees/view.html.php', $sitePath . '/views/attendees/view.html.php'],
        'PlanjeagendaViewCategories'        => [$adminPath . '/views/categories/view.html.php', $sitePath . '/views/categories/view.html.php'],
        'PlanjeagendaViewCategory'          => [$adminPath . '/views/category/view.html.php',   $sitePath . '/views/category/view.html.php'],
        'PlanjeagendaViewEvent'             => [$adminPath . '/views/event/view.html.php',       $sitePath . '/views/event/view.html.php'],
        'PlanjeagendaViewVenue'             => [$adminPath . '/views/venue/view.html.php',       $sitePath . '/views/venue/view.html.php'],
    ];

    // Site-only classes
    $siteOnly = [
        'PlanjeagendaView'                  => $sitePath . '/classes/view.class.php',
        'PlanjeagendaMailtoHelper'          => $sitePath . '/helpers/mailtohelper.php',
        'PlanjeagendaCalendar'              => $sitePath . '/classes/calendar.class.php',
        'PlanjeagendaCategoryNode'          => $sitePath . '/classes/categories.class.php',
        'PlanjeagendaIcal'                  => $sitePath . '/classes/ical.class.php',
        'ActiveCalendarWeek'            => $sitePath . '/classes/activecalendarweek.php',
        'activeCalendarWeek'            => $sitePath . '/classes/activecalendarweek.php',
        'PlanjeagendaControllerForm'        => $sitePath . '/classes/controller.form.class.php',
        'PlanjeagendaControllerMyevents'    => $sitePath . '/controllers/myevents.php',
        'PlanjeagendaControllerMyvenues'    => $sitePath . '/controllers/myvenues.php',
        'PlanjeagendaControllerMailto'      => $sitePath . '/controllers/mailto.php',
        'PlanjeagendaModelCalendar'         => $sitePath . '/models/calendar.php',
        'PlanjeagendaModelCategoryCal'      => $sitePath . '/models/categorycal.php',
        'PlanjeagendaModelDay'              => $sitePath . '/models/day.php',
        'PlanjeagendaModelEditevent'        => $sitePath . '/models/editevent.php',
        'PlanjeagendaModelEditvenue'        => $sitePath . '/models/editvenue.php',
        'PlanjeagendaModelEventslist'       => $sitePath . '/models/eventslist.php',
        'PlanjeagendaModelMailto'           => $sitePath . '/models/mailto.php',
        'PlanjeagendaModelMyattendances'    => $sitePath . '/models/myattendances.php',
        'PlanjeagendaModelMyevents'         => $sitePath . '/models/myevents.php',
        'PlanjeagendaModelMyvenues'         => $sitePath . '/models/myvenues.php',
        'PlanjeagendaModelSearch'           => $sitePath . '/models/search.php',
        'PlanjeagendaModelVenueCal'         => $sitePath . '/models/venuecal.php',
        'PlanjeagendaModelVenueslist'       => $sitePath . '/models/venueslist.php',
        'PlanjeagendaModelWeekcal'          => $sitePath . '/models/weekcal.php',
        'PlanjeagendaViewCalendar'          => $sitePath . '/views/calendar/view.html.php',
        'PlanjeagendaViewDay'               => $sitePath . '/views/day/view.html.php',
        'PlanjeagendaViewEditevent'         => $sitePath . '/views/editevent/view.html.php',
        'PlanjeagendaViewEditvenue'         => $sitePath . '/views/editvenue/view.html.php',
        'PlanjeagendaViewEventslist'        => $sitePath . '/views/eventslist/view.html.php',
        'PlanjeagendaViewMailto'            => $sitePath . '/views/mailto/view.html.php',
        'PlanjeagendaViewMyattendances'     => $sitePath . '/views/myattendances/view.html.php',
        'PlanjeagendaViewMyevents'          => $sitePath . '/views/myevents/view.html.php',
        'PlanjeagendaViewMyvenues'          => $sitePath . '/views/myvenues/view.html.php',
        'PlanjeagendaViewSearch'            => $sitePath . '/views/search/view.html.php',
        'PlanjeagendaViewVenueslist'        => $sitePath . '/views/venueslist/view.html.php',
        'PlanjeagendaViewWeekcal'           => $sitePath . '/views/weekcal/view.html.php',
        'PlanjeagendaViewAttendee'          => $adminPath . '/views/attendee/view.html.php',
        'PlanjeagendaViewCategoryelement'   => $adminPath . '/views/categoryelement/view.html.php',
        'PlanjeagendaViewContactelement'    => $adminPath . '/views/contactelement/view.html.php',
        'PlanjeagendaViewCssmanager'        => $adminPath . '/views/cssmanager/view.html.php',
        'PlanjeagendaViewEventelement'      => $adminPath . '/views/eventelement/view.html.php',
        'PlanjeagendaViewEvents'            => $adminPath . '/views/events/view.html.php',
        'PlanjeagendaViewExport'            => $adminPath . '/views/export/view.html.php',
        'PlanjeagendaViewGroup'             => $adminPath . '/views/group/view.html.php',
        'PlanjeagendaViewGroups'            => $adminPath . '/views/groups/view.html.php',
        'PlanjeagendaViewHelp'              => $adminPath . '/views/help/view.html.php',
        'PlanjeagendaViewHousekeeping'      => $adminPath . '/views/housekeeping/view.html.php',
        'PlanjeagendaViewImagehandler'      => $adminPath . '/views/imagehandler/view.html.php',
        'PlanjeagendaViewImport'            => $adminPath . '/views/import/view.html.php',
        'PlanjeagendaViewMain'              => $adminPath . '/views/main/view.html.php',
        'PlanjeagendaViewDebuglog'          => $adminPath . '/views/debuglog/view.html.php',
        'PlanjeagendaViewExport'            => $adminPath . '/views/export/view.html.php',
        'PlanjeagendaViewImport'            => $adminPath . '/views/import/view.html.php',
        'PlanjeagendaViewExport'            => $adminPath . '/views/export/view.html.php',
        'PlanjeagendaViewImport'            => $adminPath . '/views/import/view.html.php',
        'PlanjeagendaViewSettings'          => $adminPath . '/views/settings/view.html.php',
        'PlanjeagendaViewSource'            => $adminPath . '/views/source/view.html.php',
        'PlanjeagendaViewUpdatecheck'       => $adminPath . '/views/updatecheck/view.html.php',
        'PlanjeagendaViewUserelement'       => $adminPath . '/views/userelement/view.html.php',
        'PlanjeagendaViewVenueelement'      => $adminPath . '/views/venueelement/view.html.php',
        'PlanjeagendaViewVenues'            => $adminPath . '/views/venues/view.html.php',
    ];

    // Check admin-only first
    if (isset($adminOnly[$class])) {
        $file = $adminOnly[$class];
        if (file_exists($file)) require_once $file;
        return;
    }

    // Check context-sensitive classes
    if (isset($contextSensitive[$class])) {
        $file = $isAdmin ? $contextSensitive[$class][0] : $contextSensitive[$class][1];
        if (file_exists($file)) require_once $file;
        return;
    }

    // Check site-only
    if (isset($siteOnly[$class])) {
        $file = $siteOnly[$class];
        if (file_exists($file)) require_once $file;
        return;
    }
});
