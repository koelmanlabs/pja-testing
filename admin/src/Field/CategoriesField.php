<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

class CategoriesField extends ListField
{
    protected $type = 'Categories';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     *
     */
    protected function getInput()
    {
        $app      = Factory::getApplication();
        $document = $app->getDocument();
        $wa       = $document->getWebAssetManager();

        // Build the script.
        $script = array();
        $script[] = '    function jSelectCategory_'.$this->id.'(id, category, object) {';
        $script[] = '        document.getElementById("'.$this->id.'_id").value = id;';
        $script[] = '        document.getElementById("'.$this->id.'_name").value = category;';
        // $script[] = '        SqueezeBox.close();';
        $script[] = '        $("#categories-modal").modal("hide");';

        $script[] = '    };';

        // Add the script to the document head.
        $wa->addInlineScript(implode("\n", $script));

        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_planjeagenda&amp;view=categoryelement&amp;tmpl=component&amp;function=jSelectCategory_'.$this->id;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('catname');
        $query->from('#__pja_categories');
        $query->where('id='.(int)$this->value);


        try
        {
            $db->setQuery($query);
            $category = $db->loadResult();
        }
        catch (\RuntimeException $e)
        {
            $app->enqueueMessage($e->getMessage(), 'warning');
        }
        // if ($error = $db->getErrorMsg()) {
        //     Factory::getApplication()->enqueueMessage($error, 'warning');
        // }

        if (empty($category)) {
            $category = Text::_('com_planjeagenda_SELECT_CATEGORY');
        }
        $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');

        // The current user display field.
        $html[] = '<div class="fltlft">';
        $html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$category.'" disabled="disabled" size="35" class="form-control valid form-control-success" />';
        $html[] = '</div>';

        // The user select button.
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';
        $html[] = HTMLHelper::_(
            'bootstrap.renderModal',
            'categories-modal',
            array(
                'url'    => $link.'&amp;'.Session::getFormToken().'=1',
                'title'  => Text::_('com_planjeagenda_SELECT_CATEGORY'),
                'width'  => '800px',
                'height' => '450px',
                'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('com_planjeagenda_CLOSE') . '</button>'
            )
        );
        $html[] ='<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#categories-modal">'.Text::_('com_planjeagenda_SELECT_CATEGORY').'
</button>';
        $html[] = '  </div>';
        $html[] = '</div>';

        // The active category-id field.
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
