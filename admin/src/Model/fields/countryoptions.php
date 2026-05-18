<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelperBackend;

FormHelper::loadFieldClass('list');



/**
 * CountryOptions Field class.
 *
 *
 */
class JFormFieldCountryOptions extends ListField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'CountryOptions';

    /**
     * Method to get the Country options.
     *
     */
    public function getOptions()
    {
        return PlanjeagendaHelperBackend::getCountryOptions();
    }
}
