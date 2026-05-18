<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;

class HitsField extends FormField
{
    /**
     * The form field type.
     * @var        string
     */
    protected $type = 'Hits';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        $onclick    = ' onclick="document.getElementById(\''.$this->id.'\').value=\'0\';"';

        return '<input type="text" name="'.$this->name.'" id="'.$this->id.'" class="form-control field-user-input-name valid form-control-success w-20" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" readonly="readonly" style="display:inline-block;" /><input type="button"'.$onclick.' value="'.Text::_('com_planjeagenda_RESET_HITS').'" class="btn btn-primary selectcat" />';
    }
}
