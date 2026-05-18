<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Nested;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

class CategoryTable extends Nested
{
    public function __construct($db)
    {
        parent::__construct('#__pja_categories', 'id', $db);
        $this->_trackAssets = true;

        if (self::addRoot() !== false) {
            return;
        }
    }
    
    
    protected function _getAssetName()
    {
        return 'com_planjeagenda.category.' . (int) $this->id;
    }
    
    protected function _getAssetTitle()
    {
        return $this->title;
    }
    
    protected function _getAssetParentId($table = null, $id = null)
    {
        // Meestal parent = component root
        $asset = Table::getInstance('Asset');
        $asset->loadByName('com_planjeagenda');
        return $asset->id;
    }

    /**
     * Method to delete a node and, optionally, its child nodes from the table.
     *
     * @param  integer  $pk        The primary key of the node to delete.
     * @param  boolean  $children  True to delete child nodes, false to move them up a level.
     *
     * @return boolean  True on success.
     *
     * @link   https://docs.joomla.org/JTableNested/delete
     */
    public function delete($pk = null, $children = false)
    {
        return parent::delete($pk, $children);
    }

    /**
     * Add the root node to an empty table.
     *
     * @return integer  The id of the new root node.
     */
    public function addRoot()
    {
        if (self::getRootId() !== false) {
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Insert columns.
        $columns = array('parent_id', 'lft','rgt', 'level', 'catname', 'alias', 'access','title','published');

        // Insert values.
        $values = array(0, 0, 1, 0, $db->quote('root'), $db->quote('root'),1, $db->quote('root'),1);

        // Prepare the insert query.
        $query
        ->insert($db->quoteName('#__pja_categories'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
        $db->setQuery($query);

        $db->execute();

        return $db->insertid();
    }

    /**
     * try to insert first, update if fails
     *
     * Can be overloaded/supplemented by the child class
     *
     * @access public
     * @param  boolean If false, null object variables are not updated
     * @return null|string null if successful otherwise returns and error message
     */
    public function insertIgnore($updateNulls = false)
    {
        $ret = $this->_insertIgnoreObject($this->_tbl, $this, $this->_tbl_key);
        if ($ret < 0) {
            $this->setError(get_class($this).'::store failed - '.$this->_db->getError());
        }
        return $ret;
    }

    /**
     * Inserts a row into a table based on an objects properties, ignore if already exists
     *
     * @access protected
     * @param  string  The name of the table
     * @param  object  An object whose properties match table fields
     * @param  string  The name of the primary key. If provided the object property is updated.
     * @return int number of affected row
     */
    protected function _insertIgnoreObject($table, &$object, $keyName = NULL)
    {
        $fmtsql = 'INSERT IGNORE INTO '.$this->_db->quoteName($table).' (%s) VALUES (%s) ';
        $fields = [];
        $values = [];

        foreach (get_object_vars($object) as $k => $v) {            
            if ($k[0] === '_' || is_array($v) || is_object($v) || $v === null || $v === '') {
                continue;
            }

            $fields[] = $this->_db->quoteName($k);
            $values[] = $this->_db->quote($v);
        }

        if (empty($fields)) {
            return 0;
        }

        $this->_db->setQuery(sprintf($fmtsql, implode(',', $fields), implode(',', $values)));
        $results = $this->_db->execute();
        if ($results === false){
            return -1;
        }
        $id = $this->_db->insertid();
        if ($keyName && $id) {
            $object->$keyName = $id;
        }
        return $this->_db->getAffectedRows();
    }

    /**
     * Overloaded check function
     *
     * @return boolean
     *
     * @see    Table::check
     * @since  11.1
     */
    public function check()
    {
        // Check for a title.
        if (trim($this->catname) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));
            return false;
        }
        $this->alias = trim($this->alias);
        if (empty($this->alias)) {
            $this->alias = $this->catname;
        }

        $this->alias = PlanjeagendaHelper::stringURLSafe($this->alias);
        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        // Validate color field — must be empty or a valid CSS hex colour.
        if (!empty($this->color) && !preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $this->color)) {
            $this->color = '';
        }

        return true;
    }

    /**
     * Overloaded bind function.
     *
     * @param  array   $array   named array
     * @param  string  $ignore  An optional array or space separated list of properties
     *                          to ignore while binding.
     *
     * @return mixed   Null if operation was satisfactory, otherwise returns an error
     *
     * @see    Table::bind
     * @since  11.1
     */
    public function bind($array, $ignore = '')
    {
        // Ensure published defaults to 1 (published) for new categories
        if (!isset($array['published']) || $array['published'] === '') {
            $array['published'] = 1;
        }
        // Ensure access defaults to 1 (public) for new categories
        if (!isset($array['access']) || $array['access'] === '') {
            $array['access'] = 1;
        }

        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry;
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new Registry;
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
        }

        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new \Joomla\CMS\Access\Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded Table::store to set created/modified and user id.
     *
     * @param  boolean  $updateNulls  True to update fields even if they are null.
     * @return boolean  True on success.
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();
        if ($this->id) {
            // Existing category
            $this->modified_time = $date->toSql();
            $this->modified_user_id = $user->id;
        } else {
            // New category
            $this->created_time = $date->toSql();
            $this->created_user_id = $user->id;
        }
        // Verify that the alias is unique
        $db = Factory::getContainer()->get('DatabaseDriver');
        $table = new self($db);

        if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id))
            && ($table->id != $this->id || $this->id == 0)) {

            $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Check Csv Import
     * @Todo   add validation
     */
    public function checkCsvImport()
    {
        foreach (get_object_vars($this) as $k => $v) {
            if (is_array($v) || is_object($v) || $v === null || $k[0] === '_') {
                continue;
            }
            //Change datetime to null when its value is '000-00-00' (support J4 & J5)
            if (strpos($v, '0000-00-00') !== false) {
                $this->$k = null;
            }
        }

        return true;
    }

    /**
     * Store Csv Import
     */
    public function storeCsvImport($updateNulls = false)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // If a primary key exists update the object, otherwise insert it.
        if ($this->$k) {
            $stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
            $stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // If the store failed return false.
        if (!$stored) {
            $e = Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $stored->getError());
            $this->setError($e);
            return false;
        }

        if ($this->_locked) {
            $this->_unlock();
        }

        return true;
    }
    
    
    
    
    
    
    
    /*
     * @todo make rebuild function working
     */
    public function rebuildTest($parent_id = null, $left = 1)
    {
        $db = $this->getDbo();
        
        if ($parent_id === null) {
            $parent_id = $this->parent_id ?: 0;
        }
        
        $right = $left + 1;
        
        $query = $db->getQuery(true)
        ->select('id')
        ->from($this->getTableName())
        ->where('parent_id = ' . (int)$parent_id)
        ->order('lft ASC');
        
        $children = $db->setQuery($query)->loadColumn();
        
        foreach ($children as $child) {
            $right = $this->rebuild($child, $right);
        }
        
        $query = $db->getQuery(true)
        ->update($this->getTableName())
        ->set('lft = ' . (int)$left)
        ->set('rgt = ' . (int)$right)
        ->where('id = ' . (int)$this->id);
        
        if ($this->id) {
            $db->setQuery($query)->execute();
        }
        
        return $right + 1;
    }
    
    
    
    
    
    
    
    
    
    
}
