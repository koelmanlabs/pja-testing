<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

class VenueoptionsField extends ListField
{
    /**
     * A venue list
     */
    public $type = 'Venueoptions';

    /**
     * @return    array    The field option objects.
     */
    protected function getOptions()
    {
        // Initialise variables.
        $options = array();
        $published = $this->element['published']? $this->element['published'] : array(0,1);
        $name = (string) $this->element['name'];

        // Let's get the id for the current item
        $jinput = Factory::getApplication()->input;

        // Create SQL
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query    = $db->getQuery(true);

        $query->select('l.id AS value, l.venue AS text, l.published');
        $query->from('#__pja_venues AS l');

        // Filter on the published state
        if (is_numeric($published))
        {
            $query->where('l.published = ' . (int) $published);
        }
        elseif (is_array($published))
        {
            \Joomla\Utilities\ArrayHelper::toInteger($published);
            $query->where('l.published IN (' . implode(',', $published) . ')');
        }

        $query->group('l.id');
        $query->order('l.venue');

        // Get the options.
        $db->setQuery($query);

        try
        {
            $options = $db->loadObjectList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage, 'warning');
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
