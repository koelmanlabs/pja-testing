<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Attendees;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items; protected $pagination; protected $state; protected $event; protected $lists;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');

        if ($this->getLayout() === 'print') {
            return $this->_displayprint($tpl);
        }

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $event            = $this->get('Event');

        if (count($errors = (array)($this->get('Errors') ?? []))) {
            $app->enqueueMessage(implode("\n", $errors), 'error'); return false;
        }
        if (empty($event)) {
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error'); return false;
        }

        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $filterStatus = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_status', 'filter_status', -2, 'int');
        $filterType   = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_type',   'filter_type',    0, 'int');
        $filterSearch = $app->getUserStateFromRequest('com_planjeagenda.attendees.filter_search', 'filter_search', '', 'string');
        $filterSearch = $db->escape(trim(\Joomla\String\StringHelper::strtolower($filterSearch)));

        $filters = [
            HTMLHelper::_('select.option', '1', Text::_('COM_PLANJEAGENDA_NAME')),
            HTMLHelper::_('select.option', '2', Text::_('COM_PLANJEAGENDA_USERNAME')),
        ];
        $this->lists['filter'] = HTMLHelper::_('select.genericlist', $filters, 'filter_type',
            ['size'=>'1','class'=>'inputbox'], 'value', 'text', $filterType);
        $this->lists['search'] = $filterSearch;

        $options = [
            HTMLHelper::_('select.option', -2, Text::_('COM_PLANJEAGENDA_ATT_FILTER_ALL')),
            HTMLHelper::_('select.option',  0, Text::_('COM_PLANJEAGENDA_ATT_FILTER_INVITED')),
            HTMLHelper::_('select.option', -1, Text::_('COM_PLANJEAGENDA_ATT_FILTER_NOT_ATTENDING')),
            HTMLHelper::_('select.option',  1, Text::_('COM_PLANJEAGENDA_ATT_FILTER_ATTENDING')),
            HTMLHelper::_('select.option',  2, Text::_('COM_PLANJEAGENDA_ATT_FILTER_WAITING')),
        ];
        $this->lists['status'] = HTMLHelper::_('select.genericlist', $options, 'filter_status',
            ['onChange'=>'this.form.submit();'], 'value', 'text', $filterStatus);

        $this->event = $event;
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function _displayprint($tpl = null)
    {
        $this->rows  = $this->get('Items');
        $this->event = $this->get('Event');
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_REGISTERED_USERS'), 'users');
        ToolbarHelper::addNew('attendees.add');
        ToolbarHelper::editList('attendees.edit');
        ToolbarHelper::custom('attendees.setNotAttending', 'loop', 'loop', Text::_('COM_PLANJEAGENDA_ATTENDEES_SETNOTATTENDING'), true);
        ToolbarHelper::custom('attendees.setAttending',    'loop', 'loop', Text::_('COM_PLANJEAGENDA_ATTENDEES_SETATTENDING'),    true);
        if (!empty($this->event->waitinglist)) {
            ToolbarHelper::custom('attendees.setWaitinglist', 'loop', 'loop', Text::_('COM_PLANJEAGENDA_ATTENDEES_SETWAITINGLIST'), true);
        }
        ToolbarHelper::spacer();
        ToolbarHelper::custom('attendees.export', 'download', 'download', Text::_('COM_PLANJEAGENDA_EXPORT'), false);
        $bar = Toolbar::getInstance('toolbar');
        $bar->appendButton('Popup', 'print', 'COM_PLANJEAGENDA_PRINT',
            'index.php?option=com_planjeagenda&view=attendees&layout=print&tmpl=component&eventid='.$this->event->id, 600, 300);
        ToolbarHelper::deleteList('COM_PLANJEAGENDA_CONFIRM_DELETE', 'attendees.remove', 'COM_PLANJEAGENDA_ATTENDEES_DELETE');
        ToolbarHelper::spacer();
        ToolbarHelper::custom('attendees.back', 'back', 'back', Text::_('COM_PLANJEAGENDA_ATT_BACK'), false);
    }
}
