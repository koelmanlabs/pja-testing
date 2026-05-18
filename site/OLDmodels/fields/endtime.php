<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

/**
 * Endtime Field class.
 *
 *
 */
class JFormFieldEndtime extends FormField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'Endtime';

    public function getInput()
    {
        $endhours = PlanjeagendaHelper::buildtimeselect(23, 'endhours', substr( $this->value, 0, 2 ),array('class'=>'form-select valid form-control-success'));
        $endminutes = PlanjeagendaHelper::buildtimeselect(59, 'endminutes', substr($this->value, 3, 2 ),array('class'=>'form-select valid form-control-success'));
        $var2 = $endhours.$endminutes;
        return $var2;
    }
}
