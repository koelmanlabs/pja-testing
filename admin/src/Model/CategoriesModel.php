<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

class CategoriesModel extends ListModel
{
    /**
     * Constructor.
     *
     */
    public function __construct($config = [], $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'a.catname',
                'alias', 'a.alias',
                'state', 'a.published',
                'access', 'a.access', 'access_level',
                'language', 'a.language',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'created_time', 'a.created_time',
                'created_user_id', 'a.created_user_id',
                'lft', 'a.lft',
                'rgt', 'a.rgt',
                'level', 'a.level',
                'path', 'a.path',
            );
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param  string  An optional ordering field.
     * @param  string  An optional direction (asc|desc).
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        
        $forcedLanguage = $input->get('forcedLanguage', '', 'cmd');
        
        // Adjust the context to support modal layouts.
        if ($layout = $input->get('layout')) {
            $this->context .= '.' . $layout;
        }
        
        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }
        
        $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state'); 
      
        // Laad parameters
        $params = ComponentHelper::getParams('com_planjeagenda');
        $this->setState('params', $params);
        
        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param  string  $id  A prefix for the store id.
     *
     * @return string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * @return string
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.catname, a.color, a.alias, a.note, a.published, a.access' .
                ', a.checked_out, a.groupid, a.checked_out_time, a.created_user_id' .
                ', a.path, a.parent_id, a.level, a.lft, a.rgt' .
                ', a.language'
            )
        );
        $query->from('#__pja_categories AS a');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = a.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

        // Join over the groups
        $query->select('gr.name AS catgroup');
        $query->join('LEFT', '#__pja_groups AS gr ON gr.id = a.groupid');


        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int) $published);
        }
        elseif ($published === '') {
            $query->where('(a.published IN (0, 1))');
        }

        $query->where('(a.alias NOT LIKE "root")');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
                $query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
            }
            else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.catname LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.note LIKE '.$search.')');
            }
        }
        
        
        /* @todo: fix later | Filter on the language.
        /*
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
            ->bind(':language', $language);
        }
        */
        
      
        // Add the list ordering clause
        $listOrdering = $this->getState('list.ordering', 'a.lft');
        $listDirn = $db->escape($this->getState('list.direction', 'ASC'));
        if ($listOrdering == 'a.access') {
            $query->order('a.access '.$listDirn.', a.lft '.$listDirn);
        } else {
            $allowed_cat_cols = ['a.id','a.catname','a.alias','a.published','a.access','a.lft'];
            $listOrdering = PlanjeagendaHelper::sanitizeOrderCol($listOrdering, $allowed_cat_cols, 'a.lft');
            $_allowed = ['a.catname','a.alias','a.published','a.access','a.ordering','a.lft'];
            $listOrdering = PlanjeagendaHelper::sanitizeOrderCol($listOrdering, $_allowed, 'a.lft');
            $_allowed = ['a.catname','a.alias','a.published','a.access','a.ordering','a.lft'];
            $listOrdering = PlanjeagendaHelper::sanitizeOrderCol($listOrdering, $_allowed, 'a.lft');
            $query->order($db->escape($listOrdering).' '.$listDirn);
        }

        return $query;
    }

    /**
     *
     */
    public function getItems()
    {
        $items = parent::getItems() ?? [];
        // debug log removed

        foreach ($items as $item) {
            $item->assignedevents = $this->countCatEvents($item->id);
        }

        return $items;
    }

    private function countCatEvents($id)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query = 'SELECT COUNT(catid) as num'
                .' FROM #__pja_events'
                .' WHERE catid = '.(int)$id
                .' GROUP BY catid'
                ;

        $db->setQuery($query);
        $result = $db->loadResult('catid');

        return $result;
    }

}
