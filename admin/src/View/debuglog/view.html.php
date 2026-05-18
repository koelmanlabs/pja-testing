<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Koelmanlabs\Plugin\System\Pjadebug\Logger\DbLogger;

class PlanjeagendaViewDebuglog extends PlanjeagendaAdminView
{
    protected $items;
    protected $counts;
    protected $pagination;
    protected $state;
    protected $filters;

    public function display($tpl = null)
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $input = $app->input;

        // Controleer of de plugin tabel beschikbaar is
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        if (!in_array($prefix . 'pja_debug_log', $tables, true)) {
            $app->enqueueMessage(
                'PJA Debug plugin is niet geïnstalleerd of de tabel ontbreekt.',
                'warning'
            );
            $this->items  = [];
            $this->counts = ['debug'=>0,'info'=>0,'warning'=>0,'error'=>0,'total'=>0];
            parent::display($tpl);
            return;
        }

        // Filters
        $this->filters = [
            'level'     => $input->get('filter_level', '', 'string'),
            'component' => $input->get('filter_component', '', 'string'),
            'search'    => $input->get('filter_search', '', 'string'),
            'tab'       => $input->get('tab', 'all', 'string'),
        ];

        // Paginering
        $limit  = (int)$app->getUserStateFromRequest('com_planjeagenda.debuglog.limit', 'limit', 50, 'int');
        $start  = (int)$input->get('limitstart', 0, 'int');

        // Bouw query
        $query = $db->getQuery(true)
            ->select(['id','created_at','level','component','label','message',
                      'file','line','memory_kb','time_ms','query','session_id'])
            ->from($db->quoteName('#__pja_debug_log'));

        // Filter: tab (queries apart)
        if ($this->filters['tab'] === 'queries') {
            $query->where('query IS NOT NULL AND query != ' . $db->quote(''));
        }

        // Filter: level
        if (!empty($this->filters['level'])) {
            $query->where('level = ' . $db->quote($this->filters['level']));
        }

        // Filter: component
        if (!empty($this->filters['component'])) {
            $query->where('component = ' . $db->quote($this->filters['component']));
        }

        // Filter: zoekterm
        if (!empty($this->filters['search'])) {
            $search = '%' . $db->escape($this->filters['search']) . '%';
            $query->where('(label LIKE ' . $db->quote($search) .
                          ' OR message LIKE ' . $db->quote($search) .
                          ' OR file LIKE ' . $db->quote($search) . ')');
        }

        // Totaal tellen
        $countQuery = clone $query;
        $countQuery->clear('select')->select('COUNT(*)');
        $total = (int)$db->setQuery($countQuery)->loadResult();

        // Sortering en paginering
        $query->order('id DESC')->setLimit($limit, $start);

        $this->items      = $db->setQuery($query)->loadObjectList() ?: [];
        $this->counts     = DbLogger::getCounts();

        // Unieke componenten voor filter dropdown
        $compQuery = $db->getQuery(true)
            ->select('DISTINCT component')
            ->from($db->quoteName('#__pja_debug_log'))
            ->order('component ASC');
        $this->components = $db->setQuery($compQuery)->loadColumn() ?: [];

        // Legen actie
        if ($input->get('action') === 'clear' && $this->user->authorise('core.manage', 'com_planjeagenda')) {
            DbLogger::clear();
            $app->enqueueMessage('Log tabel geleegd.', 'success');
            $app->redirect('index.php?option=com_planjeagenda&view=debuglog');
            return;
        }

        // Toolbar
        ToolbarHelper::title('Plan Je Agenda — Debug log', 'bug');
        ToolbarHelper::custom(
            'debuglog.clear',
            'trash',
            '',
            Text::_('com_planjeagenda_DEBUG_LOG_CLEAR'),
            false
        );
        ToolbarHelper::preferences('com_planjeagenda');

        parent::display($tpl);
    }
}
