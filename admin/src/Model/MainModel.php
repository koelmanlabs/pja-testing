<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use stdClass;

/**
 * Planjeagenda Dashboard Model
 */
class MainModel extends BaseDatabaseModel
{
    /**
     * Algemene functie om statusstatistieken op te halen
     *
     * @param   string  $tablename  Naam van de tabel
     * @param   array   $map        Mapping van status naar naam
     * @return  stdClass
     */
    protected function getStateData($tablename, $map = null)
    {
        $db = $this->getDatabase();

        if ($map === null) {
            $map = [
                'published'   => 1, 
                'unpublished' => 0, 
                'archived'    => 2, 
                'trashed'     => -2
            ];
        }

        $query = $db->getQuery(true);
        $query->select([$db->quoteName('published'), 'COUNT(*) as num'])
              ->from($db->quoteName($tablename));

        // Specifieke uitzondering voor categorieën (geen root tonen)
        if ($tablename === '#__pja_categories') {
            $query->where($db->quoteName('alias') . ' NOT LIKE ' . $db->quote('root'));
        }

        $query->group($db->quoteName('published'));

        $db->setQuery($query);
        $result = $db->loadObjectList('published');

        $data = new \stdClass();
        $data->total = 0;

        foreach ($map as $key => $value) {
            if ($result && isset($result[$value])) {
                $data->$key = (int) $result[$value]->num;
                $data->total += $data->$key;
            } else {
                $data->$key = 0;
            }
        }

        return $data;
    }

    /**
     * Wordt aangeroepen via $this->get('EventStats') in de View
     */
    public function getEventStats()
    {
        return $this->getStateData('#__pja_events');
    }

    /**
     * Wordt aangeroepen via $this->get('VenueStats') in de View
     */
    public function getVenueStats()
    {
        return $this->getStateData('#__pja_venues');
    }

    /**
     * Wordt aangeroepen via $this->get('CategoryStats') in de View
     */
    public function getCategoryStats()
    {
        return $this->getStateData('#__pja_categories');
    }

    /**
     * Dummy data voor de update check (moet je later invullen met echte logica)
     */
    public function getUpdateData()
    {
        $data = new \stdClass();
        $data->current = 1; // 1 = Up to date, -1 = Update beschikbaar
        return $data;
    }
}