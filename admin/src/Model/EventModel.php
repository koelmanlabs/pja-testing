<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

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

/**
 * Event model.
 */
class EventModel extends AdminModel
{

    /**
     * Method to change the published state of one or more records.
     *
     * @param  array   &$pks  A list of the primary keys to change.
     * @param  integer $value The value of the published state.
     *
     * @return boolean True on success.
     *
     * @since  2.2.2
     */
    public function publish(&$pks, $value = 1)
    {
        // Additionally include the JEM plugins for the onContentChangeState event.
        PluginHelper::importPlugin('planjeagenda');

        return parent::publish($pks, $value);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param  object  A record object.
     * @return boolean True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        $result = false;

        if (!empty($record->id) && ($record->published == -2)) {
            $user = Factory::getApplication()->getIdentity();

            $result = $user->authorise('core.delete', 'com_planjeagenda') || ($user->id == $record->created_by && $user->authorise('core.delete.own', 'com_planjeagenda'));
        }

        return $result;
    }

    /**
     * Method to test whether a record can be published/unpublished.
     *
     * @param  object  A record object.
     * @return boolean True if allowed to change the state of the record. Defaults to the permission set in the component.
     */
    protected function canEditState($record)
    {
        $user = Factory::getApplication()->getIdentity();

        $id    = $record->id ?? false; // isset ensures 0 !== false
        $owner = !empty($record->created_by) ? $record->created_by : false;
        $cats  = !empty($record->catid) ? array($record->catid) : false;

        return $user->authorise('core.edit.state', 'com_planjeagenda') || ($user->id == $owner && $user->authorise('core.edit.own', 'com_planjeagenda'));
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param  type   The table type to instantiate
     * @param  string A prefix for the table class name. Optional.
     * @param  array  Configuration array for model. Optional.
     * @return Table A database object
     */
    public function getTable($type = '', $prefix = 'Table', $config = [])
    {
        try {
            $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
            return new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\EventTable($db);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Method to get the form.
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        \Joomla\CMS\Form\Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_planjeagenda/forms');
        \Joomla\CMS\Form\Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_planjeagenda/src/Model/fields');

        $form = $this->loadForm('com_planjeagenda.event', 'event', array('control' => 'jform', 'load_data' => $loadData));
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
        $jemsettings = PlanjeagendaHelper::config();

        if ($item = parent::getItem($pk)){
            // Convert the params field to an array.
            // (this may throw an exception - but there is nothings we can do)
            $registry = new Registry;
            $registry->loadString($item->attribs ?? '{}');
            $item->attribs = $registry->toArray();

            // Convert the metadata field to an array.
            $registry = new Registry;
            $registry->loadString($item->metadata ?? '{}');
            $item->metadata = $registry->toArray();

            $item->articletext = ($item->fulltext && trim($item->fulltext) != '') ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

            $db = Factory::getContainer()->get('DatabaseDriver');

            $query = $db->getQuery(true);
            $query->select('SUM(places)');
            $query->from('#__pja_register');
            $query->where(array('event= '.$db->quote($item->id), 'status=1', 'waiting=0'));

            $db->setQuery($query);
            $res = $db->loadResult();
            $item->booked = $res;

            $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__pja_attachments'))
            ->where($db->quoteName('object') . ' = ' . $db->quote('event'))
            ->where($db->quoteName('object_id') . ' = ' . (int) $item->id);
            
            $db->setQuery($query);
            $item->attachments = $db->loadObjectList();

            // FIX 1: laad categorieën als array zodat het form-systeem ze correct
            // kan binden — de catoptions-field leest dit ook als fallback.
            // Single-category: get catid directly from pja_events
            $catid = (int) $item->catid;
            $item->cats = $catid ? [$catid] : [];

            if ($item->id){
                // Store current recurrence values
                $item->recurr_bak = new \stdClass;
                foreach (get_object_vars($item) as $k => $v) {
                    if (strncmp('recurrence_', $k, 11) === 0) {
                        $item->recurr_bak->$k = $v;
                    }
                }

            }

            $item->author_ip = $jemsettings->storeip ? \PlanjeagendaHelper::retrieveIP() : false;

            if (empty($item->id)){
                $item->country = $jemsettings->defaultCountry;
            }
        }

        return $item;
    }

    /**
     * Method to get the data that should be injected in the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_planjeagenda.edit.event.data', array());

        if (empty($data)){
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param  $table Table-object.
     */
    protected function _prepareTable($table)
    {
        $jinput = Factory::getApplication()->input;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);

        // Increment version number.
        $table->version ++;

        //get time-values from time selectlist and combine them accordingly
        $starthours   = $jinput->get('starthours','','cmd');
        $startminutes = $jinput->get('startminutes','','cmd');
        $endhours     = $jinput->get('endhours','','cmd');
        $endminutes   = $jinput->get('endminutes','','cmd');

        // StartTime
        if ($starthours != '' && $startminutes != '') {
            $table->times = $starthours.':'.$startminutes;
        } else if ($starthours != '' && $startminutes == '') {
            $startminutes = "00";
            $table->times = $starthours.':'.$startminutes;
        } else if ($starthours == '' && $startminutes != '') {
            $starthours = "00";
            $table->times = $starthours.':'.$startminutes;
        } else {
            $table->times = "";
        }

        // EndTime
        if ($endhours != '' && $endminutes != '') {
            $table->endtimes = $endhours.':'.$endminutes;
        } else if ($endhours != '' && $endminutes == '') {
            $endminutes = "00";
            $table->endtimes = $endhours.':'.$endminutes;
        } else if ($endhours == '' && $endminutes != '') {
            $endhours = "00";
            $table->endtimes = $endhours.':'.$endminutes;
        } else {
            $table->endtimes = "";
        }
    }

    /**
     * Method to save the form data.
     *
     * @param  $data array
     */
    public function save($data)
    {
        // debug log removed
        // Variables
        $app         = Factory::getApplication();
        $jinput      = $app->input;

        $jemsettings = PlanjeagendaHelper::config();
        $table       = $this->getTable();

        // Check if we're in the front or back
        $backend = (bool)$app->isClient('administrator');
        $new     = (bool)empty($data['id']);

        // Variables
        // FIX 1: normaliseer cats altijd naar array van integers
        $rawCats = $data['cats'] ?? [];
        if (is_string($rawCats)) {
            $rawCats = $rawCats !== '' ? explode(',', $rawCats) : [];
        }
        $cats = array_values(array_filter(array_map('intval', (array) $rawCats)));
        $invitedusers         = $data['invited'] ?? '';
        $recurrencenumber     = $jinput->get('recurrence_number', '', 'int');
        $recurrencebyday      = $jinput->get('recurrence_byday', '', 'string');
        $recurrencebylastday  = $jinput->get('recurrence_bylastday', '', 'string');
        $metakeywords         = $jinput->get('meta_keywords', '', '');
        $metadescription      = $jinput->get('meta_description', '', '');
        $task                 = $jinput->get('task', '', 'cmd');
        $data['metadata']     = $data['metadata'] ?? '';
        $data['attribs']      = $data['attribs'] ?? '';
        $data['ordering']     = $data['ordering'] ?? '';

        // convert international date formats...
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Strip time component — CalendarField may submit datetime strings
        foreach (['dates', 'enddates', 'recurrence_limit_date'] as $_dateField) {
            if (!empty($data[$_dateField])) {
                // Take only the date part (first 10 chars of Y-m-d or Y-m-d H:i:s)
                $raw = trim($data[$_dateField]);
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
                    $data[$_dateField] = $m[1];
                } else {
                    try {
                        $d = Factory::getDate($raw, 'UTC');
                        $data[$_dateField] = $d->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $data[$_dateField] = null;
                    }
                }
            }
        }

        // Load the event from the database, check if the event is the initial event (new, root, and not a recurrence).
        // In this case, the event only needs to be updated if the recurrence setting has not changed.
        $isInitialEvent = true;

        if(!$new && $data["recurrence_type"]) {
            // This event has recurrence, can be a root or child event because has ID (!new).
            $isInitialEvent = false;
            $this->eventid = $data["id"];

            // Get data event in DB
            $eventdb = (array)$this->getEventAllData();

            // Categories
            $eventdb ['cats'] = $this->getEventCats();
            // cats is already normalised to array above

            // Times
            if ($jinput->get('starthours', null, 'raw') !== null){
                $starthours    = $jinput->get('starthours', '', 'int');
                $startminutes = $jinput->get('startminutes', '', 'int');
                if ($startminutes){
                    $data['times'] = str_pad($starthours,2,'0', STR_PAD_LEFT) . ':' . str_pad($startminutes,2,'0', STR_PAD_LEFT) . ':00';
                } else {
                    $data['times'] = str_pad($starthours,2,'0', STR_PAD_LEFT) . ':00:00';
                }
            } else {
                $data['times'] = null;
            }

            //Endtimes
            if ($jinput->get('endhours', null, 'raw') !== null){
                $endhours   = $jinput->get('endhours', '', 'int');
                $endminutes = $jinput->get('endminutes', '', 'int');

                if ($endminutes){
                    $data['endtimes'] = str_pad($endhours,2,'0', STR_PAD_LEFT) . ':' . str_pad($endminutes,2,'0', STR_PAD_LEFT) . ':00';
                } else {
                    $data['endtimes'] = str_pad($endhours,2,'0', STR_PAD_LEFT) . ':00:00';
                }
            } else {
                $data['endtimes'] = null;
            }

            // Alias
            if(isset($data['alias'])) {
                if (!$data['alias']) {
                    $alias = strtolower($data['title']);
                    $alias = preg_replace('/[^a-z0-9]+/i', '-', $alias);
                    $alias = preg_replace('/-+/', '-', $alias);
                    $data['alias'] = trim($alias, '-');
                }
            }else{
                $data['alias'] = $eventdb['alias'];
            }

            // Introtext & Fulltext: Search for the {readmore} tag and split the text up accordingly.
            if (isset($data['articletext'])) {
                $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
                $tagPos = preg_match($pattern, $data['articletext']);

                if ($tagPos == 0) {
                    $data['introtext'] = $data['articletext'];
                    $data['fulltext'] = '';
                } else {
                    list ( $data['introtext'], $data['fulltext']) = preg_split($pattern, $data['articletext'], 2);
                }
            }else{
                $data['introtext'] = $data['articletext'];
            }

            // Contact
            if($data['contactid'] == ''){
                $data['contactid'] = 0;
            }

            // Times <= Endtimes
            if($data['enddates']!== null && $data['enddates'] != ''){
                if($data['dates'] > $data['enddates']){
                    Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_EVENT_ERROR_END_BEFORE_START_DATES') . ' [ID:' . $data['id'] . ']', 'error');
                    return false;
                } else {
                    if($data['dates'] == $data['enddates']){
                        if($data['endtimes'] !== null && $data['endtimes'] != '') {
                            if ($data['times'] > $data['endtimes']) {
                                Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_EVENT_ERROR_END_BEFORE_START_TIMES') . ' [ID:' . $data['id'] . ']', 'error');
                                return false;
                            }
                        }
                    }
                }
            }

            // Get the fields changed
            $diff = array_diff_assoc($data, $eventdb);

            //If $diff contains some of fields (Defined in $fieldNotAllow) then dissolve recurrence and save again serie
            //If not, update de field of this event (save=false).
            $fieldNotAllow = ['recurrence_first_id', 'recurrence_number', 'recurrence_type', 'recurrence_counter', 'recurrence_limit', 'recurrence_limit_date', 'recurrence_byday', 'recurrence_bylastday'];
            foreach ($diff as $d => $value) {
                if (in_array($d, $fieldNotAllow)) {
                    // This event must be updated its fields
                    $data[$d] =  $value;
                    // Mark the event as root or new
                    $isInitialEvent = true;
                }
            }

            // If $isInitialEvent is true and recurrence_first_id != 0 then this event must be the first event of a new recurrence (series)
            if($isInitialEvent){
                if($eventdb['recurrence_first_id'] != 0) {

                    // Convert to root event
                    $data['recurrence_first_id'] = 0;

                    // Copy the recurrence data if it doesn't exist
                    if (!isset($data['recurrence_number'])) {
                        $data['recurrence_number'] = $eventdb['recurrence_number'];
                    }
                    if (!isset($data['recurrence_type'])) {
                        $data['recurrence_type'] = $eventdb['recurrence_type'];
                    }
                    if (!isset($data['recurrence_counter'])) {
                        $data['recurrence_counter'] = $eventdb['recurrence_counter'];
                    }
                    if (!isset($data['recurrence_limit'])) {
                        $data['recurrence_limit'] = $eventdb['recurrence_limit'];
                    }
                    if (!isset($data['recurrence_limit_date'])) {
                        $data['recurrence_limit_date'] = $eventdb['recurrence_limit_date'];
                    }
                    if (!isset($data['recurrence_byday'])) {
                        $data['recurrence_byday'] = $eventdb['recurrence_byday'];
                    }
                    if (!isset($data['recurrence_bylastday'])) {
                        $data['recurrence_bylastday'] = $eventdb['recurrence_bylastday'];
                    }
                }
            }else{
                // Get the recurrence_first_id for this recurrence event
                $data['recurrence_first_id'] = $eventdb ['recurrence_first_id'];
            }
        }

        // Set publish_down to null if they are empty (publish_up must have a datetime)
        if (empty($data['publish_down'])) {
            $data['publish_down'] = null;
        }

        // if the 'registra' field does not exist or is null, set it to the value from klevents settings
        if(!isset($data['registra'])) {
            $data['registra'] =$jemsettings->showfroregistra;
        }

        // set to null if registration is empty
        if($data['registra_from'] == ''){
            $data['registra_from'] = null;
        }
        if($data['registra_until'] == ''){
            $data['registra_until'] = null;
        }
        if($data['unregistra_until'] == ''){
            $data['unregistra_until'] = null;
        }
        if($data['reginvitedonly']== null){
            $data['reginvitedonly'] = 0;
        }

        if($isInitialEvent) {
            // event maybe first of recurrence set -> dissolve complete set
            if (PlanjeagendaHelper::dissolve_recurrence($data['id'])) {
                $this->cleanCache();
            }

            if ($data['dates'] == null || $data['recurrence_type'] == '0') {
                $data['recurrence_number'] = '0';
                $data['recurrence_byday'] = '0';
                $data['recurrence_bylastday'] = '0';
                $data['recurrence_counter'] = '0';
                $data['recurrence_type'] = '0';
                $data['recurrence_limit'] = '0';
                $data['recurrence_limit_date'] = null;
                $data['recurrence_first_id'] = '0';
            } else {
                if (!$new) {
                    // edited event maybe part of a recurrence set
                    // -> drop event from set
                    $data['recurrence_first_id'] = '0';
                    $data['recurrence_counter'] = '0';
                }

                $data['recurrence_number'] = $recurrencenumber;
                $data['recurrence_byday'] = $recurrencebyday;
                $data['recurrence_bylastday'] = $recurrencebylastday;

                if (!empty($data['recurrence_limit_date']) && ($data['recurrence_limit_date'] != null)) {
                    $d = Factory::getDate($data['recurrence_limit_date'], 'UTC');
                    $data['recurrence_limit_date'] = $d->format('Y-m-d', true, false);
                }
            }

            $data['meta_keywords'] = $metakeywords;
            $data['meta_description'] = $metadescription;

            // Store IP of author only on new events; always derived server-side, never from POST.
            if ($new) {
                $data['author_ip'] = $jemsettings->storeip ? \PlanjeagendaHelper::retrieveIP() : '';
            }

            // Store as copy - reset creation date, modification fields, hit counter, version
            if ($task == 'save2copy') {
                unset($data['created']);
                unset($data['modified']);
                unset($data['modified_by']);
                unset($data['version']);
                unset($data['hits']);
            }

            // Save the event
            $saved = parent::save($data);

            if ($saved) {
                // At this point we do have an id.
                $pk = $this->getState($this->getName() . '.id');

                if (isset($data['featured'])) {
                    $this->featured($pk, $data['featured']);
                }

                // on frontend attachment uploads maybe forbidden
                // so allow changing name or description only
                $allowed = $backend || ($jemsettings->attachmentenabled > 0);

                if ($allowed) {
                    // attachments, new ones first
                    $attachments = $jinput->files->get('attach', array(), 'array');
                    $attach_name = $jinput->post->get('attach-name', array(), 'array');
                    $attach_descr = $jinput->post->get('attach-desc', array(), 'array');
                    $attach_access = $jinput->post->get('attach-access', array(), 'array');
                    foreach ($attachments as $n => &$a) {
                        $a['customname'] = array_key_exists($n, $attach_access) ? $attach_name[$n] : '';
                        $a['description'] = array_key_exists($n, $attach_access) ? $attach_descr[$n] : '';
                        $a['access'] = array_key_exists($n, $attach_access) ? $attach_access[$n] : '';
                    }
                    AttachmentHelper::postUpload($attachments, 'event', $pk);
                }

                // Store cats
                if (!$this->_storeCategoriesSelected($pk, $cats, !$backend, $new)) {
                    //    \PlanjeagendaHelper::addLogEntry('Error storing categories for event ' . $pk, __METHOD__, Log::ERROR);
                    $this->setError(Text::_('com_planjeagenda_EVENT_ERROR_STORE_CATEGORIES'));
                    $saved = false;
                }

                // Store invited users (frontend only, on backend no attendees on editevent view)
                if (!$backend && ($jemsettings->regallowinvitation == 1)) {
                    if (!$this->_storeUsersInvited($pk, $invitedusers, !$backend, $new)) {
                        //    \PlanjeagendaHelper::addLogEntry('Error storing users invited for event ' . $pk, __METHOD__, Log::ERROR);
                        $this->setError(Text::_('com_planjeagenda_EVENT_ERROR_STORE_INVITED_USERS'));
                        $saved = false;
                    }
                }

                // check for recurrence
                // when filled it will perform the cleanup function
                $table->load($pk);
                if (($table->recurrence_number > 0) && ($table->dates != null)) {
                    \PlanjeagendaHelper::cleanup(2); // 2 = force on save, needs special attention
                }
            }
        } else {
            // This event is part of a recurrence series. Check if it is the root event to apply changes to all occurrences in the series.
            if (!$data["recurrence_first_id"]) {
                // the event is root
                $events = [];
                // retrieve all recurrence events associated with this root ID
                $allRecurrenceEvents = $this->getListRecurrenceEventsbyId($data['id'], $data['id'], time());

                // convert them to an array of objects each event and update the events with the data fields
                foreach ($allRecurrenceEvents as $recurrenceEvent){
                    $event = (array) $recurrenceEvent;
                    // update only the fields that were changed
                    foreach ($diff as $field => $value) {
                        if (array_key_exists($field, $event)) {
                            $event[$field] = $value;
                        }
                    }
                    $events[] = $event;
                }
            } else {
                // the event is a child
                $events[] = $data;
            }

            //Fields allowed to update
            $fieldAllow = ['title', 'locid', 'cats', 'dates', 'enddates', 'times', 'endtimes', 'title', 'alias', 'modified', 'modified_by', 'version', 'author_ip', 'created', 'introtext', 'meta_keywords', 'meta_description', 'datimage', 'checked_out', 'checked_out_time', 'registra', 'registra_from', 'registra_until', 'unregistra', 'unregistra_until', 'maxplaces', 'minbookeduser', 'maxbookeduser', 'reservedplaces', 'waitinglist', 'requestanswer', 'seriesbooking', 'singlebooking', 'published', 'contactid', 'custom1', 'custom2', 'custom3', 'custom4', 'custom5', 'custom6', 'custom7', 'custom8', 'custom9', 'custom10', 'fulltext', 'created_by_alias', 'access', 'featured', 'language'];
            $saved = false;

            // get the fields update
            foreach ($events as $event) {
                $fieldsupdated = "";

                // save the event
                $saved = parent::save($event);

                if ($saved){
                    foreach ($diff as $d => $value) {
                        // update only the fields that were changed
                        if (in_array($d, $fieldAllow)) {
                            $this->updateField($data['id'], $d, $value);
                            $fieldsupdated = $fieldsupdated . ($fieldsupdated != '' ? ', ' : '') . $d;
                        }
                    }
                    if ($fieldsupdated != '') {
                        Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_EVENT_FIELDS_EVENT_UPDATED') . ' ' . $fieldsupdated . ' [ID:' . $event['id'] . ']', 'info');
                    }
                } else {
                    Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_EVENT_ERROR_EVENT_UPDATED') . ' ' . implode(", ", array_keys($diff)) . ' [ID:' . $event['id'] . ']', 'info');
                }
            }

            $table->load($data['id']);
            if (isset($table->id)) {
                $this->setState($this->getName() . '.id', $table->id);
            }

			// Update  and new attachment file
            $allowed = $backend || ($jemsettings->attachmentenabled > 0);

            if ($allowed) {
                // attachments, new ones first
                $attachments = $jinput->files->get('attach', array(), 'array');
                $attach_name = $jinput->post->get('attach-name', array(), 'array');
                $attach_descr = $jinput->post->get('attach-desc', array(), 'array');
                $attach_access = $jinput->post->get('attach-access', array(), 'array');
                foreach ($attachments as $n => &$a) {
                    $a['customname'] = array_key_exists($n, $attach_access) ? $attach_name[$n] : '';
                    $a['description'] = array_key_exists($n, $attach_access) ? $attach_descr[$n] : '';
                    $a['access'] = array_key_exists($n, $attach_access) ? $attach_access[$n] : '';
                }
                AttachmentHelper::postUpload($attachments, 'event', $this->eventid);
            }

            // and update old ones
            $old = array();
            $old['id'] = $jinput->post->get('attached-id', array(), 'array');
            $old['name'] = $jinput->post->get('attached-name', array(), 'array');
            $old['description'] = $jinput->post->get('attached-desc', array(), 'array');
            $old['access'] = $jinput->post->get('attached-access', array(), 'array');

            foreach ($old['id'] as $k => $id) {
                $attach = array();
                $attach['id'] = $id;
                $attach['name'] = $old['name'][$k];
                $attach['description'] = $old['description'][$k];
                if ($allowed) {
                    $attach['access'] = $old['access'][$k];
                } // else don't touch this field
                AttachmentHelper::update($attach);
            }
        }

        return $saved;
    }


    /**
     * Method to get list recurrence events data.
     *
     * @param  int  The id of the event.
     * @param  int  The id of the parent event.
     * @return mixed  item data object on success, false on failure.
     */
    public function getListRecurrenceEventsbyId ($id, $pk, $datetimeFrom, $iduser=null, $status=null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('event.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        try
        {
            $settings = \PlanjeagendaHelper::globalattribs();
            $user     = Factory::getApplication()->getIdentity();
            $levels   = $user->getAuthorisedViewLevels();

            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);

            # Event
            $query->select(
                $this->getState('item.select',
                    'a.id, a.id AS did, a.title, a.alias, a.dates, a.enddates, a.times, a.endtimes, a.access, a.attribs, a.metadata, ' .
                    'a.custom1, a.custom2, a.custom3, a.custom4, a.custom5, a.custom6, a.custom7, a.custom8, a.custom9, a.custom10, ' .
                    'a.created, a.created_by, a.published, a.registra, a.registra_from, a.registra_until, a.unregistra, a.unregistra_until, ' .
                    'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, a.modified_by, ' .
                    'a.checked_out, a.checked_out_time, a.datimage,  a.version, a.featured, ' .
                    'a.seriesbooking, a.singlebooking, a.meta_keywords, a.meta_description, a.created_by_alias, a.introtext, a.fulltext, a.maxplaces, a.reservedplaces, a.minbookeduser, a.maxbookeduser, a.waitinglist, a.requestanswer, ' .
                    'a.hits, a.language, a.recurrence_type, a.recurrence_first_id' . ($iduser? ', r.waiting, r.places, r.status':'')))    ;
            $query->from('#__pja_events AS a');

            $dateFrom = date('Y-m-d', $datetimeFrom);
            $timeFrom = date('H:i:s', $datetimeFrom);
            $query->where('((a.recurrence_first_id = 0 AND a.id = ' . (int)($pk?$pk:$id) . ') OR a.recurrence_first_id = ' . (int)($pk?$pk:$id) . ')');
            $query->where("(a.dates > '" . $dateFrom . "' OR a.dates = '" . $dateFrom . "' AND dates >= '" . $timeFrom . "')");
            $query->order('a.dates ASC');

            $db->setQuery($query);
            $data = $db->loadObjectList();
        }
        catch (\Exception $e)
        {
            $this->setError($e);
            return false;
        }

        return $data;
    }


    /**
     * Get event all data
     *
     * @access public
     * @return object
     */
    public function getEventAllData()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__pja_events');
        $query->where('id = '.$db->Quote($this->eventid));
        $db->setQuery( $query );
        $event = $db->loadObject();

        return $event;
    }


    /**
     * Get categories of event
     *
     * @access public
     * @return string
     */
    public function getEventCats()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('GROUP_CONCAT(catid) as cats');
        $query->from('#__pja_events AS rel');
        $query->where('id = ' . (int)$this->eventid); // pja_events.id
        $db->setQuery( $query );
        $cats = $db->loadResult();

        return $cats;
    }



    /**
     * Method to update cats_event_selections table.
     * Records of previously selected categories will be removed
     * and newly selected categories will be stored.
     * Because user may not have permissions for all categories on frontend
     * records with non-permitted categories will be untouched.
     *
     * @param  int     The event id.
     * @param  array   The categories user has selected.
     * @param  bool    Flag to indicate if we are on frontend
     * @param  bool    Flag to indicate new event
     *
     * @return boolean True on success.
     */
    protected function _storeCategoriesSelected($eventId, $categories, $frontend, $new)
    {
        // Single-category: store catid directly on the event row
        $db = $this->getDatabase();

        // $categories may be array (multi-select field) or scalar
        if (is_array($categories)) {
            $catid = (int) reset($categories); // use first selected
        } else {
            $catid = (int) $categories;
        }

        if ($catid <= 0) {
            return true; // no category selected, leave as-is
        }

        $query = $db->getQuery(true)
            ->update('#__pja_events')
            ->set('catid = ' . $catid)
            ->where('id = ' . (int) $eventId);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Also maintain the relation table for backward compatibility
        $db->setQuery('DELETE FROM #__pja_cats_event_relations WHERE itemid = ' . (int) $eventId);
        $db->execute();
        $db->setQuery(
            'INSERT IGNORE INTO #__pja_cats_event_relations (catid, itemid, ordering) VALUES ('
            . $catid . ', ' . (int) $eventId . ', 0)'
        );
        try { $db->execute(); } catch (\Exception $e) { /* ignore duplicate */ }

        return true;
    }

    /**
     * Method to update cats_event_selections table.
     * Records of previously selected categories will be removed
     * and newly selected categories will be stored.
     * Because user may not have permissions for all categories on frontend
     * records with non-permitted categories will be untouched.
     *
     * @param  int     The event id.
     * @param  mixed   The user ids as array or comma separated string.
     * @param  bool    Flag to indicate if we are on frontend
     * @param  bool    Flag to indicate new event
     *
     * @return boolean True on success.
     */
    protected function _storeUsersInvited($eventId, $users, $frontend, $new)
    {
        $eventId = (int)$eventId;
        if (!is_array($users)) {
            $users = explode(',', $users);
        }
        $users = array_unique($users);
        $users = array_filter($users);

        if (empty($eventId)) {
            return false;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        # Get current registrations
        $query = $db->getQuery(true);
        $query->select(array('reg.id, reg.uid, reg.status, reg.waiting'));
        $query->from('#__pja_register As reg');
        $query->where('reg.event = ' . $eventId);
        $db->setQuery($query);
        $regs = $db->loadObjectList('uid');

        PluginHelper::importPlugin('planjeagenda');
        $dispatcher = Factory::getApplication();

        # Add new records, ignore users already registered
        foreach ($users AS $user)
        {
            if (!array_key_exists($user, $regs)) {
                $query = $db->getQuery(true);
                $query->insert('#__pja_register');
                $query->columns(array('event', 'uid', 'status'));
                $query->values($eventId.','.$user.',0');
                $db->setQuery($query);
                try {
                    $ret = $db->execute();
                } catch (\Exception $e) {
                    \PlanjeagendaHelper::addLogEntry('Exception: '. $e->getMessage(), __METHOD__, Log::ERROR);
                    $ret = false;
                }

                if ($ret !== false) {
                    $id = $db->insertid();
                    Factory::getApplication()->triggerEvent('onEventUserRegistered', array($id));
                }
            }
        }

        # Remove obsolete invitations
        foreach ($regs as $reg)
        {
            if (($reg->status == 0) && (array_search($reg->uid, $users) === false)) {
                $query = $db->getQuery(true);
                $query->delete('#__pja_register');
                $query->where('id = '.$reg->id);
                $db->setQuery($query);
                try {
                    $ret = $db->execute();
                } catch (\Exception $e) {
                    \PlanjeagendaHelper::addLogEntry('Exception: '. $e->getMessage(), __METHOD__, Log::ERROR);
                    $ret = false;
                }

                if ($ret !== false) {
                    Factory::getApplication()->triggerEvent('onEventUserUnregistered', array($eventId, $reg));
                }
            }
        }

        $cache = Factory::getCache('com_planjeagenda');
        $cache->clean();

        return true;
    }

    /**
     * Method to toggle the featured setting of articles.
     *
     * @param  array   The ids of the items to toggle.
     * @param  int     The value to toggle to.
     *
     * @return boolean True on success.
     */
    public function featured($pks, $value = 0)
    {
        // Sanitize the ids.
        $pks = (array)$pks;
        \Joomla\Utilities\ArrayHelper::toInteger($pks);

        if (empty($pks)) {
            $this->setError(Text::_('com_planjeagenda_EVENTS_NO_ITEM_SELECTED'));
            return false;
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');

            $db->setQuery(
                'UPDATE #__pja_events' .
                ' SET featured = '.(int) $value.
                ' WHERE id IN ('.implode(',', $pks).')'
            );
            $db->execute() ;

        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();

        return true;
    }

    /**
     * Method to update the field in the events table.
     *
     * @param  int     The id of event.
     * @param  string  The field of event table.
     * @param  string  The value of field (to update).
     *
     * @return boolean True on success.
     */
    public function updateField($eventid, $field, $value)
    {
        // Sanitize the ids.
        $eventid = (int)$eventid;

        if (empty($eventid)) {
            $this->setError(Text::_('com_planjeagenda_EVENTS_NO_ITEM_SELECTED'));
            return false;
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            if($field == 'cats'){
                // $value may arrive as an array (multi-select form field)
                // or as a comma-separated string — handle both
                if (is_array($value)) {
                    $cats = array_filter(array_map('intval', $value));
                } else {
                    $cats = array_filter(array_map('intval', explode(',', (string) $value)));
                }

                // Delete all old categories for id event
                $db->setQuery('DELETE FROM #__pja_cats_event_relations WHERE itemid = ' . $db->quote($eventid) );
                $db->execute();

                // Insert new categories for id event
                foreach($cats as $c){
                    $db->setQuery('INSERT INTO #__pja_cats_event_relations (catid, itemid, ordering) VALUES  (' . (int)$c . ',' . $db->quote($eventid) . ',0)');
                    $db->execute();
                }
            } else {
                // Update the value of field into events table
                // If $value is an array (e.g. from a multi-select), implode it
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $db->setQuery('UPDATE #__pja_events SET ' . $field . ' = ' . ($value !== null ? $db->quote($value) : 'null') . ' WHERE id = ' . $db->quote($eventid));
                $db->execute();
            }

        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $this->cleanCache();

        // Save Joomla tags for this event
        if (!empty($data['tags'])) {
            try {
                $savedTable = $this->getTable();
                $savedTable->load($this->getState($this->getName() . '.id'));
                $tagsHelper = new \Joomla\CMS\Helper\TagsHelper;
                $tagsHelper->postStoreProcess($savedTable, $data['tags']);
            } catch (\Throwable $tagEx) {
                // Tags are non-critical — log but don't fail the save
                \Joomla\CMS\Log\Log::add('PlanjeagendaEvent tags: ' . $tagEx->getMessage(), \Joomla\CMS\Log\Log::WARNING, 'com_planjeagenda');
            }
        }

        return true;
    }
}
