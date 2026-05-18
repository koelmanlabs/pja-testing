<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

if (!class_exists('PlanjeagendaCategories', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/categories.class.php';
}

class CatOptions2Field extends FormField
{
    /**
     * The form field type.
     *
     */
    protected $type = 'CatOptions2';

    public function getInput()
    {
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
            $attr .= ' disabled="disabled"';
        }

        //$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
        $attr .= $this->multiple ? ' multiple="multiple"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


        //$attr .= $this->element['required'] ? ' class="required modal-value"' : "";

//         if ($this->required) {
//             $class = ' class="required modal-value"';
//         }

        // Output

        //$categories = \PlanjeagendaCategories::getCategoriesTree(0);
        //$Lists['parent_id']         = \PlanjeagendaCategories::buildcatselect($categories, 'parent_id', $row->parent_id, 1);

        $currentid = Factory::getApplication()->input->getInt('id');
        $categories = \PlanjeagendaCategories::getCategoriesTree(0);

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query    = $db->getQuery(true);
        $query = 'SELECT DISTINCT parent_id FROM #__pja_categories WHERE id = '. $db->quote($currentid);

        $db->setQuery($query);
        $currentparent_id = $db->loadColumn();

        return \PlanjeagendaCategories::buildcatselect($categories, 'parent_id', $currentparent_id, 1);
    }
}
