<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use KoelmanLabs\Component\Planjeagenda\Site\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Site\Model\CategoriesModel;

class CategoriesModel extends BaseDatabaseModel
{
    protected $_id         = null;
    protected $_data       = null;
    protected $_total      = null;
    protected $_pagination = null;
    protected $_showemptycats = false;

    public function __construct($config = [], $factory = null)
    {
        parent::__construct($config, $factory);

        $app   = Factory::getApplication();
        $jinput = $app->input;

        $this->_id = $jinput->getInt('id', 0);

        $limit      = $app->getUserStateFromRequest('com_planjeagenda.categories.limit', 'limit', 20, 'int');
        $limitstart = $jinput->getInt('limitstart', 0);
        $limitstart = $limit ? (int)(floor($limitstart / $limit) * $limit) : 0;

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $jemsettings = PlanjeagendaHelper::config();
        $this->_showemptycats = (bool) $jemsettings->show_empty_categories;
    }

    public function setId(int $id): void
    {
        $this->_id   = $id;
        $this->_data = null;
    }

    public function getData(): array
    {
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
            $this->_data = $db->loadObjectList() ?: [];
        }
        return $this->_data;
    }

    public function getTotal(): int
    {
        if (empty($this->_total)) {
            $query = $this->_buildQueryTotal();
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('SELECT COUNT(*) FROM (' . $query . ') AS subq');
            $this->_total = (int)($db->loadResult() ?: 0);
        }
        return $this->_total;
    }

    protected function _buildQuery(
        bool $emptycat      = true,
        int  $parent_id     = 0,
        bool $parentCategory = false,
        string $ordering    = 'c.lft ASC'
    ): string {
        $user   = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $userId = (int) $user->id;

        $jemsettings = PlanjeagendaHelper::config();

        $task   = Factory::getApplication()->input->getCmd('task', '');
        $where_sub = ' WHERE';
        if ($task == 'archive') {
            $where_sub .= ' i.published = 2';
        } else {
            $where_sub .= ' i.published = 1';
        }
        $where_sub .= ' AND i.access IN (' . implode(',', $levels) . ')';

        if ($jemsettings->eventowner || $user->authorise('core.edit', 'com_planjeagenda')) {
            $where_sub_or = [];

            if (!$user->authorise('core.edit', 'com_planjeagenda') && !$user->authorise('core.edit.own', 'com_planjeagenda')) {
                // Standard user — only see published events
            } else {
                // hint: above it's a not not ;-)
                // meaning: Show unpublished events not connected to a category which is not one of the allowed categories.
                // user permitted on own events
                if (($userId !== 0) && ($user->authorise('core.edit.own', 'com_planjeagenda') || $jemsettings->eventowner)) {
                    $where_sub_or[] = '(i.published = 0 AND i.created_by = ' . $userId . ')';
                }
                if (!empty($where_sub_or)) {
                    $where_sub .= ' AND (' . implode(' OR ', $where_sub_or) . ')';
                }
            }
        }
        $where_sub .= ' AND c.id = cc.id';

        // show/hide empty categories
        $empty = $emptycat ? '' : ' HAVING assignedevents > 0';

        // Parent category itself or its sub categories
        $parent_id       = $parent_id ?: $this->_id;
        $parentCategoryQuery = $parentCategory
            ? 'c.id=' . (int)$parent_id
            : 'c.parent_id=' . (int)$parent_id;

        $case_when_a = ' CASE WHEN c.access IN (' . implode(',', $levels) . ') THEN 1 ELSE 0 END as user_has_access_category,';

        // Filter by access level
        if ($jemsettings->access_level_locked_categories != '["1"]') {
            $accessLevels = json_decode($jemsettings->access_level_locked_categories, true) ?: [];
            $newlevels    = array_values(array_unique(array_merge($levels, $accessLevels)));
            $where_access = ' AND c.access IN (' . implode(',', $newlevels) . ')';
        } else {
            $where_access = ' AND c.access IN (' . implode(',', $levels) . ')';
        }

        $query = 'SELECT c.*,'
               . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS slug,'
               . $case_when_a
               . ' ('
               . '  SELECT COUNT(DISTINCT i.id)'
               . '  FROM #__pja_events AS i'
               . '  LEFT JOIN #__pja_categories AS ci ON ci.id = i.catid'
               . '  LEFT JOIN #__pja_categories AS cc ON cc.id = c.id'
               . $where_sub
               . '  GROUP BY cc.id'
               . ' ) AS assignedevents'
               . ' FROM #__pja_categories AS c'
               . ' WHERE c.published = 1'
               . ' AND ' . $parentCategoryQuery
               . $where_access
               . ' GROUP BY c.id ' . $empty
               . ' ORDER BY ' . $ordering;

        return $query;
    }

    protected function _buildQueryTotal()
    {
        $user = Factory::getApplication()->getIdentity();
        // Support Joomla access levels instead of single group id
        $levels = $user->getAuthorisedViewLevels();

        $query = 'SELECT DISTINCT c.id'
               . ' FROM #__pja_categories AS c';

        if (!$this->_showemptycats) {
            $query .= ' INNER JOIN #__pja_events AS ae ON ae.catid = c.id '
                    . ' INNER JOIN #__pja_events AS e ON e.id = ae.id ';
        }

        $query .= ' WHERE c.published = 1'
                . ' AND c.parent_id = ' . (int) $this->_id
                . ' AND c.access IN (' . implode(',', $levels) . ')'
                ;

        if (!$this->_showemptycats) {
            $query .= ' AND e.access IN (' . implode(',', $levels) . ')';

            $task = Factory::getApplication()->input->getCmd('task', '');
            if($task == 'archive') {
                $query .= ' AND e.published = 2';
            } else {
                $query .= ' AND e.published = 1';
            }
        }

        return $query;
    }

    /**
     * Method to get a pagination object for the events
     *
     * @access public
     * @return integer
     */
    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }
}

// Alias so Joomla's legacy MVC can find it by the standard name
if (!class_exists('CategoriesModel')) {
    class_alias('CategoriesModel', 'CategoriesModel');
}
