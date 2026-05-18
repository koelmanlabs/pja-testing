<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaFactory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\AttachmentHelper;

class GroupModel extends AdminModel
{

    /**
     * Method to test whether a record can be deleted.
     *
     * @param  object  A record object.
     * @return boolean True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id) && ($record->published == -2))
        {
            $user = Factory::getApplication()->getIdentity();

            if (!empty($record->catid)) {
                return $user->authorise('core.delete', 'com_planjeagenda.category.'.(int) $record->catid);
            } else {
                return $user->authorise('core.delete', 'com_planjeagenda');
            }
        }

        return false;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param  object  A record object.
     * @return boolean True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     */
    protected function canEditState($record)
    {
        $user = Factory::getApplication()->getIdentity();

        if (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_planjeagenda.category.'.(int) $record->catid);
        } else {
            return $user->authorise('core.edit.state', 'com_planjeagenda');
        }
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param  type   The table type to instantiate. Optional.
     * @param  string A prefix for the table class name. Optional.
     * @param  array  Configuration data for model. Optional.
     * @return Table A database object
     */
    public function getTable($type = '', $prefix = 'Table', $config = [])
    {
        try {
            $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
            return new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\GroupTable($db);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to get the record form.
     *
     * @param  array   $data     Data for the form. Optional.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not. Optional.
     * @return mixed   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_planjeagenda.group', 'group', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param  integer The id of the primary key.
     *
     * @return mixed   Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        return $item;
    }

    /**
     * Method to get the data that should be injected in the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_planjeagenda.edit.group.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param Table The table object to prepare.
     *
     */
    protected function _prepareTable($table)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $app = Factory::getApplication();

        // Make sure the data is valid
        if (!$table->check()) {
            $this->setError($table->getError());
            return;
        }

        // Store data
        if (!$table->store(true)) {
            throw new \Exception($table->getError(), 500);
        }

        $members = $app->input->get('maintainers', array(), 'array');

        // Updating group references
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__pja_groupmembers'));
        $query->where('group_id = '.(int)$table->id);

        $db->setQuery($query);
        $db->execute();

        foreach($members as $member)
        {
            $member = intval($member);

            $query = $db->getQuery(true);
            $columns = array('group_id', 'member');
            $values = array((int)$table->id, $member);

            $query->insert($db->quoteName('#__pja_groupmembers'))
                  ->columns($db->quoteName($columns))
                  ->values(implode(',', $values));

            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Method to get the members data
     *
     * @access public
     * @return array List of members
     *
     */
    public function getMembers()
    {
        $members = $this->_members();

        $users = array();

        if (!empty($members)) {
            $query = 'SELECT id AS value, username, name'
                   . ' FROM #__users'
                   . ' WHERE id IN ('.$members.')'
                   . ' ORDER BY name ASC'
                   ;

            $this->_db->setQuery($query);
            $users = $this->_db->loadObjectList();

            foreach ($users as &$user) {
                $user->text = $user->name . ' (' . $user->username . ')';
            }
        }

        return $users;
    }

    /**
     * Method to get the selected members.
     *
     * @access protected
     * @return string
     *
     */
    protected function _members()
    {
        $item = parent::getItem();

        $members = null;

        //get selected members
        if ($item->id) {
            $query = 'SELECT member'
                   . ' FROM #__pja_groupmembers'
                   . ' WHERE group_id = '.(int)$item->id;

            $this->_db->setQuery ($query);
            $member_ids = $this->_db->loadColumn();

            if (is_array($member_ids)) {
                $members = implode(',', $member_ids);
            }
        }

        return $members;
    }

    /**
     * Method to get the available users.
     *
     * @access public
     * @return mixed
     *
     */
    public function getAvailable()
    {
        $members = $this->_members();

        // get non selected members
        $query  = 'SELECT id AS value, username, name FROM #__users';
        $query .= ' WHERE block = 0' ;

        if ($members) {
            $query .= ' AND id NOT IN ('.$members.')' ;
        }

        $query .= ' ORDER BY name ASC';

        $this->_db->setQuery($query);
        $available = $this->_db->loadObjectList();

        foreach ($available as &$item) {
            $item->text = $item->name . ' (' . $item->username . ')';
        }

        return $available;
    }
}
