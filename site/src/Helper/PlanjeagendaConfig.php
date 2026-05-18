<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Exception;

/**
 * PlanjeagendaConfig class to handle configuration
 */
class PlanjeagendaConfig
{
    /**
     * Data Object
     * @var Registry 
     */
    protected $_data;

    /**
     * Class instance.
     * @var PlanjeagendaConfig
     */
    protected static $instance;

    /**
     * Singleton instance
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        // Laad de data in een Registry object
        $this->_data = new Registry($this->loadData());
    }

    public function toRegistry()
    {
        return $this->_data;
    }

    public function toObject()
    {
        return $this->_data->toObject();
    }

    /**
     * Laden van de configuratie uit de database
     */
    protected function loadData()
    {
        $db   = Factory::getContainer()->get('Joomla\Database\DatabaseInterface');
        $data = new \stdClass();

        // Probeer de nieuwe key-value tabel
        $query = $db->getQuery(true)
            ->select($db->quoteName(['keyname', 'value']))
            ->from($db->quoteName('#__pja_config'));
        
        try {
            $db->setQuery($query);
            $list = $db->loadAssocList('keyname', 'value');
        } catch (\Exception $e) {
            $list = null;
        }

        if (!empty($list)) {
            $data = (object) $list;
        } else {
            // Fallback naar de oude settings tabel
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__pja_settings'))
                ->where('id = 1');

            try {
                $db->setQuery($query);
                $data = $db->loadObject() ?: new \stdClass();
            } catch (\Exception $e) {
                $data = new \stdClass();
            }
        }

        // Decodeer specifieke velden als ze bestaan
        foreach (['globalattribs', 'css'] as $field) {
            if (!empty($data->$field)) {
                $registry = new Registry;
                $registry->loadString($data->$field);
                $data->$field = $registry->toObject();
            }
        }

        return $data;
    }

    public function bind($data)
    {
        $reg = new Registry($data);
        $this->_data->loadObject($reg->toObject());

        return true;
    }

    public function set($key, $value)
    {
        $this->_data->set($key, $value);
        return $this->store();
    }

    /**
     * Opslaan naar de database
     */
    public function store()
    {
        $data = $this->_data->toArray();
        $db   = Factory::getContainer()->get('Joomla\Database\DatabaseInterface');

        // Velden weer omzetten naar string voor opslag
        foreach (['globalattribs', 'css'] as $field) {
            if (isset($data[$field])) {
                $registry = new Registry($data[$field]);
                $data[$field] = $registry->toString();
            }
        }

        // Haal bestaande keys op om te bepalen of we moeten updaten of inserten
        $query = $db->getQuery(true)
            ->select($db->quoteName('keyname'))
            ->from($db->quoteName('#__pja_config'));
        
        $db->setQuery($query);
        $existingKeys = $db->loadColumn() ?: [];

        foreach ($data as $k => $v) {
            $query = $db->getQuery(true);
            
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v);
            }

            if (in_array($k, $existingKeys)) {
                $query->update($db->quoteName('#__pja_config'))
                    ->set($db->quoteName('value') . ' = ' . $db->quote($v))
                    ->where($db->quoteName('keyname') . ' = ' . $db->quote($k));
            } else {
                $query->insert($db->quoteName('#__pja_config'))
                    ->columns($db->quoteName(['keyname', 'value']))
                    ->values($db->quote($k) . ', ' . $db->quote($v));
            }

            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }
}