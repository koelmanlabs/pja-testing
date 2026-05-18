<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('JPATH_BASE') or die;

if (!class_exists('PlanjeagendaHelper')) {
    require_once JPATH_SITE . '/components/com_planjeagenda/helpers/helper.php';
}

use Joomla\CMS\Form\FormField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

//JFormHelper::loadFieldClass('list');

// jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.

/**
 * CountryOptions Field class.
 *
 *
 */
class JFormFieldStarttime extends FormField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'Starttime';

    public function getInput()
    {
        $starthours = PlanjeagendaHelper::buildtimeselect(23, 'starthours', substr( $this->value, 0, 2 ),array('class'=>'form-select','class'=>'select-time'));
        $startminutes = PlanjeagendaHelper::buildtimeselect(59, 'startminutes', substr($this->value, 3, 2 ),array('class'=>'form-select','class'=>'select-time'));
        $var2 = $starthours.$startminutes;
        return $var2;
    }
}