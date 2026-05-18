<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class CalendarModel extends ListModel
{
    /**
     * Bouwt de SQL-query specifiek voor de FullCalendar weergave
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Selecteer de benodigde velden (overeenkomstig met je JEM-databasestructuur)
        $query->select($this->getState('list.select', 'a.*'))
            ->from($db->quoteName('#__pja_events', 'a'));
    
        // Join met categorieën voor de kleuren en namen
            $query->select($db->quoteName('c.title', 'catname'))
            ->select($db->quoteName('c.color', 'cat_color'))
            ->leftJoin($db->quoteName('#__pja_categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));
            
       // Join met venues/locaties (indien van toepassing in je database)
            $query->select($db->quoteName('l.venue', 'venue'))
            ->select($db->quoteName('l.city', 'city'))
            ->leftJoin($db->quoteName('#__pja_venues', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('a.venueid'));
        
            
       // Filter for villages
       $query->join('LEFT', $db->quoteName('#__pja_villages', 'vi') . ' ON ' . $db->quoteName('vi.id') . ' = ' . $db->quoteName('v.village_id'));
            
        // Alleen gepubliceerde events tonen
        $query->where($db->quoteName('a.published') . ' = 1');

 
        // Haal de FullCalendar datums op uit de model-state
        $start = $this->getState('filter.start_date');
        $end   = $this->getState('filter.end_date');

        if (!empty($start) && !empty($end)) {
            // Haal events op die overlappen met de actieve kalendermaand
            $query->where('(' 
                . $db->quoteName('a.dates') . ' <= ' . $db->quote($end) . ' AND (' 
                . $db->quoteName('a.enddates') . ' >= ' . $db->quote($start) 
                . ' OR ' . $db->quoteName('a.enddates') . ' IS NULL '
                . ' OR ' . $db->quoteName('a.enddates') . ' = ' . $db->quote('0000-00-00')
                . ')'
            .')');
        }

        // Sorteer netjes op datum en tijd
        $query->order($db->quoteName('a.dates') . ' ASC')
              ->order($db->quoteName('a.times') . ' ASC');

              // Server-side Gemeente Filter (Alleen filteren als er écht een ID boven de 0 wordt meegegeven)
              $municipalityId = $this->getState('filter.municipality');
              if (!empty($municipalityId) && is_numeric($municipalityId) && (int) $municipalityId > 0) {
                 $query->where($db->quoteName('a.municipality_id') . ' = ' . (int) $municipalityId);
              }
              
              // Server-side Categorie Filter
              $catId = $this->getState('filter.category');
              if (is_numeric($catId) && (int) $catId > 0) {
                 $query->where($db->quoteName('a.catid') . ' = ' . (int) $catId);
              }
              
              
              
              // --- SLIMME FILTERING ---
              if ($villageId > 0)
              {
                  $query->where($db->quoteName('l.village_id') . ' = ' . (int) $villageId);
              }
              elseif ($municipalityId > 0)
              {
                  $query->where($db->quoteName('vi.municipality_id') . ' = ' . (int) $municipalityId);
              }
              
              
        return $query;
    }

    /**
     * Initialiseert de states (voorkomt dat de standaard Joomla limiet van 20 de kalender afkapt)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        
        // Dwing een hoge limiet af zodat alle events in de maand zichtbaar worden
        $this->setState('list.start', 0);
        $this->setState('list.limit', 1000);
    }
    
    
    
    /**
     * Method to get a list of events.
     */
    public function getItems()
    {
        $items = parent::getItems();
        
        return $items;
    }
    
    
    
    public function getMunicipalities()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        
        // Haal de unieke gemeentes op die gekoppeld zijn aan actieve locaties
        $query->select($db->quoteName(['id', 'title']))
        ->from($db->quoteName('#__pja_municipalities')) // Pas aan naar jouw exacte tabelnaam
        ->order($db->quoteName('title') . ' ASC');
        
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
}