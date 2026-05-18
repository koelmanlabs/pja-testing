<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

// jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.
// jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.

FormHelper::loadFieldClass('list');

/**
 * Renders an event element
 *
 * @package JEM
 *
 */
class JFormFieldEvent extends ListField
{
    protected $type = 'title';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     *
     */
    protected function getInput()
    {
        // Build the script.
        $script = array();
        $script[] = '    function elSelectEvent(id, title, object) {';
        $script[] = '        document.getElementById("'.$this->id.'_id").value = id;';
        $script[] = '        document.getElementById("'.$this->id.'_name").value = title;';
        // $script[] = '        SqueezeBox.close();';
        $script[] = '        $("#event-modal").modal("hide");';
        $script[] = '    }';

        // Add the script to the document head.
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(implode("\n", $script));

        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_planjeagenda&amp;view=eventelement&amp;tmpl=component&amp;object='.$this->id;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery(
            'SELECT title' .
            ' FROM #__pja_events' .
            ' WHERE id = '.(int) $this->value
        );

        try
        {
            $title = $db->loadResult();
        }
        catch (RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        if (empty($title)) {
            $title = Text::_('com_planjeagenda_SELECT_EVENT');
        }
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current user display field.
        $html[] = '<div class="fltlft">';
        $html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" class="form-control valid form-control-success" />';
        $html[] = '</div>';

        // The user select button.
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';
        $html[] = HTMLHelper::_(
            'bootstrap.renderModal',
            'event-modal',
            array(
                'url'    => $link.'&amp;'.Session::getFormToken().'=1',
                'title'  => Text::_('com_planjeagenda_SELECT_EVENT'),
                'width'  => '800px',
                'height' => '450px',
                'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('com_planjeagenda_CLOSE') . '</button>'
            )
        );
        $html[] ='<button type="button" class="btn btn-link" data-bs-toggle="modal"  data-bs-target="#event-modal">'.Text::_('com_planjeagenda_SELECT_EVENT').'
</button>';
        $html[] = '  </div>';
        $html[] = '</div>';

        // The active event-id field.
        if (0 == (int)$this->value) {
            $value = '';
        } else {
            $value = (int)$this->value;
        }

        // class='required' for client side validation
        $class = '';
        if ($this->required) {
            $class = ' class="required modal-value"';
        }

        $html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

        return implode("\n", $html);
    }
}
