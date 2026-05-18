<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class PlanjeagendaModelSearch extends BaseDatabaseModel
{
    protected $_data       = null;
    protected $_total      = null;
    protected $_pagination = null;
    protected $_query      = null;

    // Keep a J6-safe DB reference alongside the legacy $_db
    private $db = null;

    public function __construct($config = [], $factory = null)
    {
        parent::__construct($config, $factory);

        // J6-safe DB reference — BaseDatabaseModel does not set $_db reliably
        // when constructed outside the MVC factory, so we set it explicitly.
        $this->db    = Factory::getContainer()->get('DatabaseDriver');
        $this->_db   = $this->db;  // legacy alias used throughout this class

        $app         = Factory::getApplication();
        $jemsettings = PlanjeagendaHelper::config();

        $limit      = $app->getUserStateFromRequest('com_planjeagenda.search.limit', 'limit', $jemsettings->display_num, 'int');
        $limitstart = $app->input->getInt('limitstart', 0);
        $limitstart = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $filter_order = $app->input->getCmd('filter_order', 'a.dates');
        $this->setState('filter_order', $filter_order);

        $filter_order_DirDefault = 'ASC';
        $task = $app->input->getCmd('task', '');
        if (($task == 'archive') && ($filter_order == 'a.dates')) {
            $filter_order_DirDefault = 'DESC';
        }
        $this->setState('filter_order_Dir', $app->input->getCmd('filter_order_Dir', $filter_order_DirDefault));
    }

    public function getData()
    {
        $pop = Factory::getApplication()->input->getBool('pop', false);

        if (empty($this->_data)) {
            $query = $this->_buildQuery();

            if ($pop) {
                $this->_data = $this->_getList($query);
            } else {
                $pagination  = $this->getPagination();
                $this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
            }

            foreach ($this->_data as $i => $item) {
                $item->categories = $this->getCategories($item->id);
                if (empty($item->categories)) {
                    unset($this->_data[$i]);
                }
            }
        }

        return $this->_data;
    }

    public function getPagination()
    {
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination(
                $this->getTotal(),
                $this->getState('limitstart'),
                $this->getState('limit')
            );
        }
        return $this->_pagination;
    }

    public function getTotal()
    {
        if (empty($this->_total)) {
            $query         = $this->_buildQuery();
            $this->_total  = $this->_getListCount($query);
        }
        return $this->_total;
    }

    // ── Query building ─────────────────────────────────────────────────────

    protected function _buildQuery()
    {
        if (empty($this->_query)) {
            $where   = $this->_buildWhere();
            $orderby = $this->_buildOrderBy();

            // Note: categories alias = 'c', countries alias = 'ct' (no duplicate)
            $this->_query =
                'SELECT a.id, a.dates, a.enddates, a.times, a.endtimes, a.title,'
                . ' a.created, a.created_by, a.created_by_alias, a.locid, a.published, a.access,'
                . ' a.recurrence_type, a.recurrence_first_id, a.recurrence_byday,'
                . ' a.recurrence_counter, a.recurrence_limit, a.recurrence_limit_date, a.recurrence_number,'
                . ' a.alias, a.attribs, a.checked_out, a.checked_out_time, a.contactid,'
                . ' a.datimage, a.featured, a.hits, a.language, a.version,'
                . ' a.custom1, a.custom2, a.custom3, a.custom4, a.custom5,'
                . ' a.custom6, a.custom7, a.custom8, a.custom9, a.custom10,'
                . ' a.introtext, a.fulltext, a.registra, a.unregistra, a.maxplaces,'
                . ' a.waitinglist, a.metadata, a.meta_keywords, a.meta_description,'
                . ' a.modified, a.modified_by,'
                . ' l.id AS l_id, l.venue, l.street, l.postalCode, l.city, l.state,'
                . ' l.country, l.url, l.published AS l_published,'
                . ' l.alias AS l_alias, l.locdescription, l.locimage,'
                . ' l.latitude, l.longitude, l.map,'
                . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END AS slug,'
                . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', a.locid, l.alias) ELSE a.locid END AS venueslug'
                . ' FROM #__pja_events AS a'
                . ' LEFT JOIN #__pja_categories AS c  ON c.id  = a.catid'
                . ' LEFT JOIN #__pja_venues     AS l  ON l.id  = a.locid'
                . ' LEFT JOIN #__pja_countries  AS ct ON ct.iso2 = l.country'
                . $where
                . ' GROUP BY a.id'
                . $orderby;
        }

        return $this->_query;
    }

    protected function _buildOrderBy()
    {
        $app  = Factory::getApplication();
        $task = $app->input->getCmd('task', '');

        $filter_order     = $this->getState('filter_order');
        $filter_order_Dir = $this->getState('filter_order_Dir');
        $default_dir      = ($task == 'archive') ? 'DESC' : 'ASC';

        $allowed = ['a.dates', 'a.title', 'l.venue', 'l.city', 'l.state', 'c.catname', 'a.created'];
        $filter_order     = PlanjeagendaHelper::sanitizeOrderCol(
            InputFilter::getInstance()->clean($filter_order, 'cmd'), $allowed, 'a.dates'
        );
        $filter_order_Dir = in_array(strtoupper($filter_order_Dir), ['ASC', 'DESC'])
            ? strtoupper($filter_order_Dir) : $default_dir;

        if ($filter_order === 'a.dates') {
            return ' ORDER BY a.dates ' . $filter_order_Dir
                 . ', a.times '         . $filter_order_Dir
                 . ', a.created '       . $filter_order_Dir;
        }

        return ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir
             . ', a.dates '   . $default_dir
             . ', a.times '   . $default_dir
             . ', a.created ' . $default_dir;
    }

    protected function _buildWhere()
    {
        $app    = Factory::getApplication();
        $params = $app->isClient('administrator')
            ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda')
            : $app->getParams();
        $task   = $app->input->getCmd('task', '');
        $user   = $app->getIdentity();
        $levels = $user->getAuthorisedViewLevels();

        $where = ($task === 'archive')
            ? ' WHERE a.published = 2'
            : ' WHERE a.published = 1';

        $where .= ' AND a.access IN (' . implode(', ', $levels) . ')';

        $filter           = $app->getUserStateFromRequest('com_planjeagenda.search.filter_search',    'filter_search',    '', 'string');
        $filter_type      = $app->input->getString('filter_type', '');
        $filter_continent = $app->getUserStateFromRequest('com_planjeagenda.search.filter_continent', 'filter_continent', '', 'string');
        $filter_country   = $app->getUserStateFromRequest('com_planjeagenda.search.filter_country',   'filter_country',   '', 'string');
        $filter_city      = $app->getUserStateFromRequest('com_planjeagenda.search.filter_city',      'filter_city',      '', 'string');
        $filter_date_from = $app->getUserStateFromRequest('com_planjeagenda.search.filter_date_from', 'filter_date_from', '', 'string');
        $filter_date_to   = $app->getUserStateFromRequest('com_planjeagenda.search.filter_date_to',   'filter_date_to',   '', 'string');
        $filter_category  = $app->getUserStateFromRequest('com_planjeagenda.search.filter_category',  'filter_category',  0,  'int');
        $top_category     = $params->get('top_category', 1);

        // No filter = no results (search requires input)
        $filter_category = ($filter_category > 1) ? $filter_category : $top_category;
        if (!($filter || $filter_continent || $filter_country || $filter_city
            || $filter_date_from || $filter_date_to || $filter_category != $top_category)) {
            return ' WHERE 0';
        }

        // Keyword filter
        if ($filter) {
            $f    = \Joomla\String\StringHelper::strtolower($filter);
            $safe = $this->db->quote('%' . $this->db->escape($f, true) . '%', false);
            $type = \Joomla\String\StringHelper::strtolower($filter_type);
            switch ($type) {
                case 'venue': $where .= ' AND LOWER(l.venue) LIKE ' . $safe; break;
                case 'city':  $where .= ' AND LOWER(l.city)  LIKE ' . $safe; break;
                default:      $where .= ' AND LOWER(a.title) LIKE ' . $safe; break;
            }
        }

        // Date filters — use range comparisons so idx_dates index is used
        $dateFilterType = (int) $params->get('date_filter_type', 0);
        if ($dateFilterType === 1) {
            // Match on full span: event overlaps the requested range
            if ($filter_date_from && strtotime($filter_date_from)) {
                $df = $this->db->quote(date('Y-m-d', strtotime($filter_date_from)));
                $where .= ' AND COALESCE(a.enddates, a.dates) >= ' . $df;
            }
            if ($filter_date_to && strtotime($filter_date_to)) {
                $dt = $this->db->quote(date('Y-m-d', strtotime($filter_date_to)));
                $where .= ' AND a.dates <= ' . $dt;
            }
        } else {
            // Match on start date only
            if ($filter_date_from && strtotime($filter_date_from)) {
                $df = $this->db->quote(date('Y-m-d', strtotime($filter_date_from)));
                $where .= ' AND a.dates >= ' . $df;
            }
            if ($filter_date_to && strtotime($filter_date_to)) {
                $dt = $this->db->quote(date('Y-m-d', strtotime($filter_date_to)));
                $where .= ' AND a.dates <= ' . $dt;
            }
        }

        // Location filters — use 'ct' alias for countries (not 'c' which is categories)
        if ($filter_continent) {
            $where .= ' AND ct.continent = ' . $this->db->quote($filter_continent);
        }
        if ($filter_country) {
            $where .= ' AND l.country = ' . $this->db->quote($filter_country);
        }
        if ($filter_country && $filter_city) {
            $where .= ' AND l.city = ' . $this->db->quote($filter_city);
        }

        // Category filter
        if ($filter_category) {
            $cats  = PlanjeagendaCategories::getChilds((int) $filter_category);
            $where .= ' AND c.id IN (' . implode(', ', $cats) . ')';
        }

        return $where;
    }

    // ── Helper queries ─────────────────────────────────────────────────────

    public function getCategories(int $id): array
    {
        $user   = Factory::getApplication()->getIdentity();
        $levels = implode(',', $user->getAuthorisedViewLevels());

        $this->db->setQuery(
            'SELECT c.id, c.catname, c.access, c.lft,'
            . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS catslug'
            . ' FROM #__pja_categories AS c'
            . ' INNER JOIN #__pja_events AS ae ON ae.catid = c.id'
            . ' WHERE ae.id = ' . (int) $id
            . '   AND c.published = 1'
            . '   AND c.access IN (' . $levels . ')'
        );

        return $this->db->loadObjectList() ?: [];
    }

    public function getCountryOptions(): array
    {
        $app              = Factory::getApplication();
        $filter_continent = $app->getUserStateFromRequest('com_planjeagenda.search.filter_continent', 'filter_continent', '', 'string');

        $sql = 'SELECT c.iso2 AS value, c.name AS text'
             . ' FROM #__pja_events AS a'
             . ' INNER JOIN #__pja_venues   AS l  ON l.id     = a.locid'
             . ' INNER JOIN #__pja_countries AS c ON c.iso2   = l.country';
        if ($filter_continent) {
            $sql .= ' WHERE c.continent = ' . $this->db->quote($filter_continent);
        }
        $sql .= ' GROUP BY c.iso2 ORDER BY c.name';

        $this->db->setQuery($sql);
        return $this->db->loadObjectList() ?: [];
    }

    public function getContinentFromCountry(string $country): string
    {
        $this->db->setQuery(
            'SELECT c.continent FROM #__pja_countries AS c WHERE c.iso2 = '
            . $this->db->quote($country)
        );
        return (string) ($this->db->loadResult() ?: '');
    }

    public function getCityOptions(): array
    {
        $country = Factory::getApplication()->input->getString('filter_country', '');
        if (!$country) return [];

        $this->db->setQuery(
            'SELECT DISTINCT l.city AS value, l.city AS text'
            . ' FROM #__pja_events AS a'
            . ' INNER JOIN #__pja_venues    AS l  ON l.id   = a.locid'
            . ' INNER JOIN #__pja_countries AS ct ON ct.iso2 = l.country'
            . ' WHERE l.country = ' . $this->db->quote($country)
            . ' ORDER BY l.city'
        );
        return $this->db->loadObjectList() ?: [];
    }

    public function getCategoryTree(): array
    {
        $app    = Factory::getApplication();
        $params = $app->isClient('administrator')
            ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda')
            : $app->getParams('com_planjeagenda');

        $top_id = max(1, (int) $params->get('top_category', 1));
        $user   = $app->getIdentity();
        $levels = implode(',', $user->getAuthorisedViewLevels());

        $this->db->setQuery(
            'SELECT c.* FROM #__pja_categories AS c'
            . ' WHERE c.published = 1 AND c.access IN (' . $levels . ')'
            . ' ORDER BY c.lft'
        );
        $mitems = $this->db->loadObjectList() ?: [];

        $children = [];
        foreach ($mitems as $v) {
            $children[$v->parent_id][] = $v;
        }

        return PlanjeagendaCategories::treerecurse($top_id, '', [], $children, 9999, 0, 0);
    }

    // ── Helpers (satisfy BaseDatabaseModel interface) ───────────────────────

    protected function _getList(string $query, int $limitstart = 0, int $limit = 0): array
    {
        $this->db->setQuery($query, $limitstart, $limit);
        return $this->db->loadObjectList() ?: [];
    }

    protected function _getListCount(string $query): int
    {
        // Wrap in a subquery for accurate COUNT with GROUP BY
        $this->db->setQuery('SELECT COUNT(*) FROM (' . $query . ') AS subq');
        return (int) ($this->db->loadResult() ?: 0);
    }
}
