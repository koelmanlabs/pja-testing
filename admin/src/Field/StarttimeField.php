<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;


class StarttimeField extends FormField
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
