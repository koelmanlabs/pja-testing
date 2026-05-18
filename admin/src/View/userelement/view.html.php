<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * View class for the JEM userelement screen
 *
 * @package JEM
 *
 */
class PlanjeagendaViewUserElement extends HtmlView {

    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        // initialise variables
        $app = Factory::getApplication();
        $document = $app->getDocument();
        $jemsettings = PlanjeagendaAdmin::config();
        $db = Factory::getContainer()->get('DatabaseDriver');

        // get var
        $filter_order        = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_order', 'filter_order', 'u.name', 'cmd');
        $filter_order_Dir    = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search             = $app->getUserStateFromRequest('com_planjeagenda.userelement.filter_search', 'filter_search', '', 'string');
        $search             = $db->escape(trim(\Joomla\String\StringHelper::strtolower($search)));

        // prepare the document
        $document->setTitle(Text::_('com_planjeagenda_SELECTATTENDEE'));

        // Load css
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');
        // Get data from the model
        $users            = $this->get('Data');
        $pagination     = $this->get('Pagination');

        // build selectlists
        $lists = array();
        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        // search filter
        $lists['search']= $search;

        // assign data to template
        $this->lists        = $lists;
        $this->rows            = $users;
        $this->jemsettings    = $jemsettings;
        $this->pagination    = $pagination;

        parent::display($tpl);
    }
}
?>
