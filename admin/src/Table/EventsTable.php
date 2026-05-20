<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Events List Table class
 * Handles bulk operations on events
 */
class EventsTable extends Table
{
    /**
     * @param   DatabaseInterface  $db  Database connector object
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__pja_events', 'id', $db);
    }

    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table. The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param  mixed    $pks     An array of primary key values to update. If not set
     *                           the instance property value is used. [optional]
     * @param  integer  $state   The publishing state. eg. [0 = unpublished, 1 = published] [optional]
     * @param  integer  $userId  The user id of the user performing the operation. [optional]
     *
     * @return boolean  True on success.
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array((int)$this->$k);
            } else {
                // Nothing to set publishing state on, return false.
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $this->_db->quoteName($k) . ' IN (' . implode(',', $pks) . ')';

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        } else {
            $checkin = ' AND (checked_out IS null OR checked_out = 0 OR checked_out = ' . (int) $userId . ')';
        }

        // Update the publishing state for rows with the given primary keys.
        $query = $this->_db->getQuery(true);
        $query->update($this->_db->quoteName($this->_tbl));
        $query->set($this->_db->quoteName('published') . ' = ' . (int) $state);
        $query->where($where);

        try {
            $this->_db->setQuery($query . $checkin);
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'notice');
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
            // Checkin the rows.
            foreach ($pks as $pk) {
                $this->checkin($pk);
            }
        }

        // If the Table instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->published = $state;
        }

        $this->setError('');

        return true;
    }

    /**
     * Method to toggle the featured setting of events.
     *
     * @param  array   The ids of the items to toggle.
     * @param  int     The value to toggle to.
     *
     * @return boolean True on success.
     */
    public function featured($pks = null, $value = 0)
    {
        $k = $this->_tbl_key;

        // Sanitize input.
        \Joomla\Utilities\ArrayHelper::toInteger($pks);
        $value = (int) $value;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array((int)$this->$k);
            } else {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        try {
            // Update the featured state for rows with the given primary keys.
            $query = $this->_db->getQuery(true);
            $query->update($this->_db->quoteName($this->_tbl));
            $query->set($this->_db->quoteName('featured') . ' = ' . $value);
            $query->where($this->_db->quoteName($k) . ' IN (' . implode(',', $pks) . ')');

            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // If the Table instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->featured = $value;
        }

        return true;
    }

    /**
     * Method to delete a row from the database table by primary key value.
     * After deletion all registrations and attachments are deleted.
     *
     * @param  mixed  $pk  An optional primary key value to delete. If not set the instance property value is used.
     *
     * @return boolean  True on success.
     */
    public function delete($pk = null)
    {
        $k = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

        if ($pk === null) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            return false;
        }

        try {
            // Delete registrations for this event
            $query = $this->_db->getQuery(true);
            $query->delete($this->_db->quoteName('#__pja_register'));
            $query->where($this->_db->quoteName('event') . ' = ' . (int) $pk);
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Delete attachments for this event
            $query = $this->_db->getQuery(true);
            $query->delete($this->_db->quoteName('#__pja_attachments'));
            $query->where($this->_db->quoteName('object') . ' = ' . $this->_db->quote('event'));
            $query->where($this->_db->quoteName('object_id') . ' = ' . (int) $pk);
            $this->_db->setQuery($query);
            $this->_db->execute();

        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Call parent delete
        return parent::delete($pk);
    }

    /**
     * Method to change the ordering of a record.
     *
     * @param  integer  $delta  The amount by which to move the ordering either up or down.
     *
     * @return boolean  True on success.
     */
    public function move($delta)
    {
        // Not typically used for events since they're sorted by date
        // But included for completeness if ordering field is used

        $k = $this->_tbl_key;
        $pk = $this->$k;

        if ($pk === null) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
            return false;
        }

        $query = $this->_db->getQuery(true);
        $query->update($this->_db->quoteName($this->_tbl));
        $query->set($this->_db->quoteName('ordering') . ' = ' . $this->_db->quoteName('ordering') . ' + ' . (int) $delta);
        $query->where($this->_db->quoteName($k) . ' = ' . (int) $pk);

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
            $this->ordering = $this->ordering + $delta;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }
}
