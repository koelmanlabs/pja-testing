<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Table;


defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Database\DatabaseDriver;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaFactory;

/**
 * 
 */
class VenueTable extends Table
{

    public function __construct(DatabaseDriver $db)
    {
        // Zorg dat #__pja_venues de juiste naam is van je tabel in de DB
        parent::__construct('#__pja_venues', 'id', $db);
        $this->_trackAssets = true;
    }
    
    protected function _getAssetName()
    {
        return 'com_planjeagenda.venue.' . (int) $this->id;
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
     * Overloaded bind method for the Venue table.
     */
    public function bind($array, $ignore = '')
    {
        // in here we are checking for the empty value of the checkbox

        if (!isset($array['map'])) {
            $array['map'] = 0 ;
        }
        
        
        // Fix voor Latitude
        if (isset($array['latitude']) && $array['latitude'] === '') {
            $array['latitude'] = null;
        }
        
        // Fix voor Longitude (meestal heeft die hetzelfde probleem)
        if (isset($array['longitude']) && $array['longitude'] === '') {
            $array['longitude'] = null;
        }

        //don't override without calling base class
        return parent::bind($array, $ignore);
    }


    
    /**
     * overloaded check function
     */
    public function check()
    {
        $jinput = Factory::getApplication()->input;

        if (trim($this->venue) == '') {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_NAME'));
            return false;
        }

        // Set alias
        $this->alias = PlanjeagendaHelper::stringURLSafe($this->alias);
        if (empty($this->alias)) {
            $this->alias = PlanjeagendaHelper::stringURLSafe($this->venue);
            if (trim(str_replace('-', '', $this->alias)) == '') {
                $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
            }
        }

        if ($this->map) {
            if (!trim($this->street) || !trim($this->city) || !trim($this->country) || !trim($this->postalCode)) {
                if ((!trim($this->latitude) && !trim($this->longitude))) {
                    $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_MAP_ADDRESS'));
                    return false;
                }
            }
        }

        if (trim($this->url)) {
            $this->url = strip_tags($this->url);

            if (strlen($this->url) > 199) {
                $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_URL_LENGTH'));
                return false;
            }

            // convert IDN Domain to punycode for validation
            $urlToValidate = $this->url;
            $parsed = parse_url($this->url);
            
            if (isset($parsed['host']) && function_exists('idn_to_ascii')) {
                // convert host to ASCII/punycode
                $asciiHost = idn_to_ascii($parsed['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                
                if ($asciiHost !== false) {
                    $urlToValidate = str_replace($parsed['host'], $asciiHost, $this->url);
                }
            }

            // validate with converted URL
            if (filter_var($urlToValidate, FILTER_VALIDATE_URL) === false) {
                $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_URL_FORMAT'));
                return false;
            }

            // allow http/https only
            if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
                $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_URL_SCHEME'));
                return false;
            }
        }

        $this->street = strip_tags($this->street);
        $streetlength = \Joomla\String\StringHelper::strlen($this->street);
        if ($streetlength > 50) {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_STREET'));
            return false;
        }

        $this->postalCode = strip_tags($this->postalCode);
        if (\Joomla\String\StringHelper::strlen($this->postalCode) > 10) {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_POSTALCODE'));
            return false;
        }

        $this->city = strip_tags($this->city);
        if (\Joomla\String\StringHelper::strlen($this->city) > 50) {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_CITY'));
            return false;
        }

        $this->state = strip_tags($this->state);
        if (\Joomla\String\StringHelper::strlen($this->state) > 50) {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_STATE'));
            return false;
        }

        $this->country = strip_tags($this->country);
        if (\Joomla\String\StringHelper::strlen($this->country) > 2) {
            $this->setError(Text::_('com_planjeagenda_VENUE_ERROR_COUNTRY'));
            return false;
        }
        

        // 
        if (isset($this->modified_by)) {
            $this->modified_by = \Joomla\CMS\Factory::getApplication()->getIdentity()->id;
        }
        
        
        // Fix voor created_by
        if (empty($this->created_by)) {
            $this->created_by = \Joomla\CMS\Factory::getApplication()->getIdentity()->id;
        }
        
        // Fix voor version: als hij leeg of 0 is, maak er 1 van
        if (empty($this->version)) {
            $this->version = 1;
        }
        
        // De latitude/longitude fix (voor de zekerheid)
        if (isset($this->latitude) && $this->latitude === '') { $this->latitude = null; }
        if (isset($this->longitude) && $this->longitude === '') { $this->longitude = null; }

        return true;
    }

    
    /*
     * DELETE
     */
    public function delete($pk = null)
    {
        $id = $pk ?: $this->id;
        
        if ($id) {
            $db = $this->getDatabase();
            
            // 1. Haal bestandsnamen op om ze fysiek te verwijderen (roadmap)
            // 2. Verwijder de database records van de bijlagen
            $query = $db->getQuery(true)
            ->delete($db->quoteName('#__pja_attachments'))
            ->where($db->quoteName('object') . ' = ' . $db->quote('venue'))
            ->where($db->quoteName('object_id') . ' = ' . (int) $id);
            
            $db->setQuery($query);
            $db->execute();
        }
        
        return parent::delete($pk);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Overloaded store method for the Venue table.
     */
    public function store($updateNulls = false)
    {
        $date        = Factory::getDate();
        $user        = Factory::getApplication()->getIdentity();
        $userid      = $user->id;
        $app         = Factory::getApplication();
        $jinput      = $app->input;
        $jemsettings = PlanjeagendaHelper::config();

        // Check if we're in the front or back
        if ($app->isClient('administrator')) {
            $backend = true;
        } else {
            $backend = false;
        }

        if ($this->id) {
            // Existing venue
            $this->modified = $date->toSql();
            $this->modified_by = $userid;
        } else {
            // New venue
            if (!intval($this->created)) {
                $this->created = $date->toSql();
            }
            if (empty($this->created_by)) {
                $this->created_by = $userid;
            }
        }

        // Check if image was selected
        $image_dir = JPATH_SITE.'/images/klevents/venues/';
        $filetypes = $jemsettings->image_filetypes ?: 'jpg,gif,png,webp';
        $allowable = explode(',', strtolower($filetypes));
        array_walk($allowable, function(&$v){$v = trim($v);});
        $image_to_delete = false;

        // get image (frontend) - allow "removal on save" (Hoffi, 2014-06-07)
        if (!$backend) {
            if (($jemsettings->imageenabled == 2 || $jemsettings->imageenabled == 1)) {
                $file = $jinput->files->get('userfile', array(), 'array');
                $removeimage = $jinput->getInt('removeimage', 0);
                $locimage = $jinput->getCmd('locimage', '');

                if (empty($file)) {
                    $file2 = $jinput->files->get('jform', array(), 'array');
                    if (!empty($file2['userfile'])) {
                        $file = $file2['userfile'];
                    }
                }

                if (!empty($file['name'])) {
                    //check the image
                    $check = \PlanjeagendaImage::check($file, $jemsettings);

                    if ($check !== false) {
                        //sanitize the image filename
                        $filename = \PlanjeagendaImage::sanitize($image_dir, $file['name']);
                        $filepath = $image_dir . $filename;

                        if (File::upload($file['tmp_name'], $filepath)) {
                            $image_to_delete = $this->locimage; // delete previous image
                            $this->locimage = $filename;
                        }
                    }
                } elseif (!empty($removeimage)) {
                    // if removeimage is non-zero remove image from venue
                    // (file will be deleted later (e.g. housekeeping) if unused)
                    $image_to_delete = $this->locimage;
                    $this->locimage = '';
                } elseif (!$this->id && is_null($this->locimage) && !empty($locimage)) {
                    // venue is a copy so copy locimage too
                    if (File::exists($image_dir . $locimage)) {
                        // if it's already within image folder it's safe
                        $this->locimage = $locimage;
                    }
                }
            } // end image if
        } // if (!backend)

        $format = File::getExt($image_dir . $this->locimage);
        if (!in_array($format, $allowable))
        {
            $this->locimage = '';
        }

        if (!$backend) {
            // check if the user has the required rank to publish this venue
            if (!$this->id && !$user->authorise('core.edit.state', 'com_planjeagenda')) {
                $this->published = 0;
            }
        }

        // item must be stored BEFORE image deletion
        $ret = parent::store($updateNulls);
        if ($ret && $image_to_delete) {
            PlanjeagendaHelper::delete_unused_image_files('venue', $image_to_delete);
        }

        return $ret;
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

        try {
            $ret = $this->_insertIgnoreObject($this->_tbl, $this, $this->_tbl_key);
        } catch (\RuntimeException $e){
            $this->setError(get_class($this).'::store failed - '.$e->getMessage());
            return false;
        }
        return true;
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
        $fields = array();

        foreach (get_object_vars($object) as $k => $v) {
            if (is_array($v) or is_object($v) or $v === NULL) {
                continue;
            }
            if ($k[0] == '_') { // internal field
                continue;
            }
            $fields[] = $this->_db->quoteName($k);
            $values[] = $this->_db->quote($v);
        }

        $this->_db->setQuery(sprintf($fmtsql, implode(",", $fields), implode(",", $values)));
        if ($this->_db->execute() === false) {
            return false;
        }
        $id = $this->_db->insertid();
        if ($keyName && $id) {
            $object->$keyName = $id;
        }

        return $this->_db->getAffectedRows();
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


        // Check for a database error.
        // TODO: use exception handling
        // if ($this->_db->getErrorNum()) {
        //     $this->setError($this->_db->getErrorMsg());
        //     return false;
        // }

        try
        {
            $this->_db->setQuery($query . $checkin);
            $this->_db->execute();
        }
        catch (\RuntimeException $e)
        {
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
}
