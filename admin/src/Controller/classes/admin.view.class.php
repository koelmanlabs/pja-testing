<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

/**
 * PlanjeagendaView class with JEM specific extensions
 *
 * @package JEM
 */
class PlanjeagendaAdminView extends HtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        if (PlanjeagendaSidebarHelper::getEntries()) {
            $this->sidebar = PlanjeagendaSidebarHelper::render();
        }

        parent::display($tpl);
    }
}
