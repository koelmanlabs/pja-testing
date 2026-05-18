<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

// Ensure site-side helper classes are loaded
if (!class_exists('PlanjeagendaHelper', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/helpers/helper.php';
}
if (!class_exists('PlanjeagendaCategories', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/categories.class.php';
}
if (!class_exists('PlanjeagendaAttachment', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/attachment.class.php';
}
if (!class_exists('PlanjeagendaImage', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/image.class.php';
}

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
// PlanjeagendaModelVenue aliases the namespaced admin VenueModel (PSR-4 autoloaded)
if (!class_exists('PlanjeagendaModelVenue', false)) {
    class_alias(
        'KoelmanLabs\\Component\\Planjeagenda\\Administrator\\Model\\VenueModel',
        'PlanjeagendaModelVenue'
    );
}

/**
 * Editvenue Model
 */
class PlanjeagendaModelEditvenue extends PlanjeagendaModelVenue
{
    /**
     * Model typeAlias string. Used for version history.
     * @var        string
     */
    public $typeAlias = 'com_planjeagenda.venue';


    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('a_id', 0);
        $this->setState('venue.id', $pk);

        $fromId = $app->input->getInt('from_id', 0);
        $this->setState('venue.from_id', $fromId);

        $return = $app->input->get('return', '', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->isClient('administrator') ? \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda') : $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', $app->input->getCmd('layout', ''));
    }

    /**
     * Method to get venue data.
     *
     * @param  integer The id of the venue.
     * @return mixed item data object on success, false on failure.
     */
    public function getItem($itemId = null)
    {
        $jemsettings = PlanjeagendaHelper::config();
        $user = Factory::getApplication()->getIdentity();

        // Initialise variables.
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('venue.id');

        $doCopy = false;
        if (!$itemId && $this->getState('venue.from_id')) {
            $itemId = $this->getState('venue.from_id');
            $doCopy = true;
        }

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = ArrayHelper::toObject($properties, 'stdClass');

        if ($doCopy) {
            $value->id = 0;
            $value->author_ip = '';
            $value->created = '';
            $value->created_by = '';
            $value->modified = '';
            $value->modified_by = '';
            $value->version = '';
        }

        // Convert attrib field to Registry.
        //$registry = new Registry();
        //$registry->loadString($value->attribs);

        $globalregistry = PlanjeagendaHelper::globalattribs();

        $value->params = clone $globalregistry;
        //$value->params->merge($registry);

        // Compute selected asset permissions.
        //  Check edit permission.
        $value->params->set('access-edit', $user->authorise('core.edit', 'com_planjeagenda'));
        //  Check edit state permission.
        $value->params->set('access-change', $user->authorise('core.edit.state', 'com_planjeagenda'));

        $value->author_ip = $jemsettings->storeip ? PlanjeagendaHelper::retrieveIP() : false;

        // Get attachments - but not on copied venues
        $files = PlanjeagendaAttachment::getAttachments('venue' . $value->id);
        $value->attachments = $files;

        // Preset values on new venues
        if (isset($jemsettings->defaultCountry) && empty($itemId)) {
            $value->country = $jemsettings->defaultCountry;
        }

        return $value;
    }

    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
    //    JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');

        return parent::loadForm($name, $source, $options, $clear, $xpath);
    }

    /**
     * Get the return URL.
     *
     * @return string return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }

}
