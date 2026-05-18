<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

class FootertextField extends FormField
{
    /**
     * The form field type.
     */
    protected $type = 'Footertext';

    /**
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $columns = $this->element['cols'] ? ' cols="' . (int) $this->element['cols'] . '"' : '';
        $rows = $this->element['rows'] ? ' rows="' . (int) $this->element['rows'] . '"' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        return '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class . $disabled . $onchange . '>'
            . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
    }
}
