<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;


class EndtimeField extends FormField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'Endtime';


    public function getInput()
    {

        $endhours = PlanjeagendaHelper::buildtimeselect(23, 'endhours', substr( $this->value, 0, 2 ),array('class'=>'form-select','class'=>'select-time'));
        $endminutes = PlanjeagendaHelper::buildtimeselect(59, 'endminutes', substr($this->value, 3, 2 ),array('class'=>'form-select','class'=>'select-time'));

        $var2 = $endhours.$endminutes;

        return $var2;

    }

}
