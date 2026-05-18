<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('JPATH_BASE') or die;
// Load PlanjeagendaCategories from site classes
if (!class_exists('PlanjeagendaCategories')) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/categories.class.php';
}


use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;

//JFormHelper::loadFieldClass('list');

// jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.


/**
 * CountryOptions Field class.
 *
 *
 */
class JFormFieldCatOptions2 extends FormField
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

        //$categories = PlanjeagendaCategories::getCategoriesTree(0);
        //$Lists['parent_id']         = PlanjeagendaCategories::buildcatselect($categories, 'parent_id', $row->parent_id, 1);

        $currentid = Factory::getApplication()->input->getInt('id');
        $categories = PlanjeagendaCategories::getCategoriesTree(0);

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query    = $db->getQuery(true);
        $query = 'SELECT DISTINCT parent_id FROM #__pja_categories WHERE id = '. $db->quote($currentid);

        $db->setQuery($query);
        $currentparent_id = $db->loadColumn();

        return PlanjeagendaCategories::buildcatselect($categories, 'parent_id', $currentparent_id, 1);
    }
}
