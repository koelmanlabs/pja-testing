<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaConfig;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

class SettingsModel extends AdminModel
{
    public function getTable($type = '', $prefix = 'Table', $config = [])
    {
        try {
            $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
            return new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\SettingsTable($db);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }


    /**
     * Method to get the record form.
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_planjeagenda.settings', 'settings', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Loading the table data
     */
    public function getData()
    {
        $config = PlanjeagendaConfig::getInstance();
        $data = $config->toObject();

        return $data;
    }

    /**
     * Method to get the data that should be injected in the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_planjeagenda.edit.settings.data', array());

        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
    }

    /**
     * Saves the settings
     */
    public function store($data)
    {
        // If the source value is an object, get its accessible properties.
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        // additional data:
        $jinput = Factory::getApplication()->input;
        $varmetakey = $jinput->get('meta_keywords','','');
        $data['meta_keywords'] = implode(', ', array_filter($varmetakey));
        $data['lastupdate'] = $jinput->get('lastupdate','',''); // 'lastupdate' indicates last cleanup etc., not when config as stored.

        // sanitize
        if (empty($data['imagewidth'])) {
            $data['imagewidth'] = 100;
        }
        if (empty($data['imagehight'])) {
            $data['imagehight'] = 100;
        }

        // Cap recurrence anticipation values to prevent runaway event generation (max 24 months)
        $anticipationFields = [
            'recurrence_anticipation_day', 'recurrence_anticipation_week',
            'recurrence_anticipation_month', 'recurrence_anticipation_year',
            'recurrence_anticipation_lastday',
        ];
        foreach ($anticipationFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = max(1, min(24, (int)$data[$field]));
            }
        }

        //
        // Store into new table
        //
        $config = PlanjeagendaConfig::getInstance();

        // Bind the form fields to the table
        if (!$config->bind($data)) {
            $this->setError(Text::_('?'));
            return false;
        }
        if (!$config->store()) {
            $this->setError(Text::_('?'));
            return false;
        }

        //
        // Old table - deprecated, maybe already removed
        //
        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $settings = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\SettingsTable($db);

            $fields = $settings->getFields();
            if (!empty($fields)) {
                // Bind the form fields to the table
                if (!$settings->bind($data,'')) {
                    $this->setError($settings->getError());
                    return false;
                }

                $varmetakey = $jinput->get('meta_keywords','','');
                $settings->meta_keywords = $varmetakey;

                $meta_key="";
                foreach ($settings->meta_keywords as $meta_keyword) {
                    if ($meta_key != "") {
                        $meta_key .= ", ";
                    }
                    $meta_key .= $meta_keyword;
                }

                // binding the input fields (outside the jform)
                $varlastupdate = $jinput->get('lastupdate','','');
                $settings->lastupdate = $varlastupdate;

                $settings->meta_keywords = $meta_key;
                $settings->id = 1;

                if (!$settings->store()) {
                    $this->setError($settings->getError());
                    return false;
                }
            }
            // else: ok, old table removed - simply ignore
        }
        catch (\Exception $e) {
            // ok, old table removed - simply ignore
        }

        return true;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @Note Calling getState in this method will result in recursion.
     *
     * @since 1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the parameters.
        $params = ComponentHelper::getParams('com_planjeagenda');
        $this->setState('params', $params);
    }

    /**
     * Return config information
     */
    public function getConfigInfo()
    {
        $config = new \stdClass();

        // Get PHP version
        $phpversion = phpversion();
        $config->vs_php = $phpversion;

        // Magic quotes have been discontinued since PHP 5.4, so simply leave it blank
        $config->vs_php_magicquotes = '';

        // Get GD version
        $gd_version = '?';
        if (function_exists('gd_info')) {
            $gd_info = gd_info();
            $gd_version = $gd_info['GD Version'] ?? '?';
        }
        $config->vs_gd = $gd_version;

        // Get info about all JEM parts
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select(['name', 'type', 'enabled', 'manifest_cache'])
            ->from('#__extensions')
            ->where('name LIKE ' . $db->quote('%klevents%'));
        $db->setQuery($query);
        $extensions = $db->loadObjectList('name');

        $known_extensions = [
            'pkg_jem', 'com_planjeagenda', 'mod_jem', 'mod_jem_cal', 'mod_jem_calajax',
            'mod_jem_banner', 'mod_jem_jubilee', 'mod_jem_teaser', 'mod_jem_wide', 'mod_jem_map',
            'plg_content_jem', 'plg_content_jemlistevents', 'plg_content_jemembed',
            'plg_finder_jem', 'plg_search_jem',
            'plg_quickicon_jem', 'Quick Icon - JEM',
            'plg_jem_comments', 'plg_jem_mailer', 'plg_jem_demo'
        ];

        foreach ($extensions as $name => $extension) {
            if (in_array($name, $known_extensions)) {
                $manifest = json_decode($extension->manifest_cache, true);
                $extension->version      = (!empty($manifest) && array_key_exists('version',      $manifest)) ? $manifest['version']      : '?';
                $extension->creationDate = (!empty($manifest) && array_key_exists('creationDate', $manifest)) ? $manifest['creationDate'] : '?';
                $extension->author       = (!empty($manifest) && array_key_exists('author',       $manifest)) ? $manifest['author']       : '?';
                $config->$name = clone $extension;
            }
        }
        return $config;
    }
}
