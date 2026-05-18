<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Debuglog;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items; protected $counts; protected $filters; protected $components = [];

    public function display($tpl = null)
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $input = $app->input;

        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        if (!in_array($prefix . 'pja_debug_log', $tables, true)) {
            $app->enqueueMessage('PJA Debug plugin is niet geïnstalleerd of de tabel ontbreekt.', 'warning');
            $this->items  = [];
            $this->counts = ['debug'=>0,'info'=>0,'warning'=>0,'error'=>0,'total'=>0];
            $this->filters = ['tab'=>'all','level'=>'','component'=>'','search'=>''];
            $this->addToolbar();
            return parent::display($tpl);
        }

        $this->filters = [
            'level'     => $input->get('filter_level',     '',    'string'),
            'component' => $input->get('filter_component', '',    'string'),
            'search'    => $input->get('filter_search',    '',    'string'),
            'tab'       => $input->get('tab',              'all', 'string'),
        ];

        $limit = (int) $app->getUserStateFromRequest('com_planjeagenda.debuglog.limit', 'limit', 50, 'int');
        $start = (int) $input->get('limitstart', 0, 'int');

        $query = $db->getQuery(true)
            ->select(['id','created_at','level','component','label','message','file','line','memory_kb','time_ms','query','session_id'])
            ->from($db->quoteName('#__pja_debug_log'));

        if ($this->filters['tab'] === 'queries') {
            $query->where('query IS NOT NULL AND query != ' . $db->quote(''));
        }
        if (!empty($this->filters['level'])) {
            $query->where('level = ' . $db->quote($this->filters['level']));
        }
        if (!empty($this->filters['component'])) {
            $query->where('component = ' . $db->quote($this->filters['component']));
        }
        if (!empty($this->filters['search'])) {
            $s = '%' . $db->escape($this->filters['search']) . '%';
            $query->where('(label LIKE '.$db->quote($s).' OR message LIKE '.$db->quote($s).' OR file LIKE '.$db->quote($s).')');
        }

        $query->order('id DESC')->setLimit($limit, $start);
        $this->items = $db->setQuery($query)->loadObjectList() ?: [];

        // Count per level
        $cq = $db->getQuery(true)->select(['level','COUNT(*) AS cnt'])->from($db->quoteName('#__pja_debug_log'))->group('level');
        $rows = $db->setQuery($cq)->loadAssocList('level', 'cnt') ?: [];
        $this->counts = [
            'debug'   => (int)($rows['debug']   ?? 0),
            'info'    => (int)($rows['info']    ?? 0),
            'warning' => (int)($rows['warning'] ?? 0),
            'error'   => (int)($rows['error']   ?? 0),
            'total'   => array_sum($rows),
        ];

        $cmp = $db->getQuery(true)->select('DISTINCT component')->from($db->quoteName('#__pja_debug_log'))->order('component ASC');
        $this->components = $db->setQuery($cmp)->loadColumn() ?: [];

        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title('Plan Je Agenda — Debug log', 'bug');
        ToolbarHelper::custom('debuglog.clear', 'trash', '', Text::_('COM_PLANJEAGENDA_DEBUG_LOG_CLEAR'), false);
        ToolbarHelper::preferences('com_planjeagenda');
    }
}
