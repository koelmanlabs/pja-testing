<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

class CountryOptionsField extends ListField
{
    protected $type = 'CountryOptions';

    public function getOptions()
    {
        return \KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper::getCountryOptions();
    }
}
