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
 * StartHours Field class.
 *
 *
 */
class JFormFieldStarthours extends FormField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'Starthours';


    public function getInput()
    {

        $starthours = PlanjeagendaHelper::buildtimeselect(23, 'starthours', substr( $this->name, 0, 2 ));
        $startminutes = PlanjeagendaHelper::buildtimeselect(59, 'startminutes', substr( $this->name, 3, 2 ));

        $var2 = $starthours.$startminutes;

        return $var2;

    }

}
