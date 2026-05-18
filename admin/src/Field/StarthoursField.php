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


class StarthoursField extends FormField
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
