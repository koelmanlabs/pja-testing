<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 * 
 * view-admin: event
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use KoelmanLabs\Component\Planjeagenda\Site\Classes\Categories;
use PlanjeagendaCategories;




/**
 * Catoptions form field — multi-select category picker for com_planjeagenda.
 * Registered via addfieldprefix="KoelmanLabs\Component\Planjeagenda\Administrator\Field"
 */
class CatoptionsField extends ListField
{
    protected $type = 'Catoptions';

    /**
     * Load site-side global classes needed for category queries.
     * Called lazily at method time, not at class load time, to ensure
     * all Joomla bootstrapping is complete.
     */
    private static function bootSiteClasses(): void
    {
        
    }

    public function getInput(): string
    {
        self::bootSiteClasses();

        $app       = Factory::getApplication();
        $currentId = $app->input->getInt('id', 0);

        // HTML attributes
        $attr  = !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr .= !empty($this->size)  ? ' size="' . $this->size . '"'   : '';
        $attr .= $this->multiple      ? ' multiple'                      : '';
        $attr .= $this->required      ? ' required aria-required="true"' : '';
        if ($this->readonly || $this->disabled) {
            $attr .= ' disabled="disabled"';
        }
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        $attr2  = $this->multiple ? ' multiple' : '';
        $attr2 .= $this->required ? ' required aria-required="true"' : '';
        $attr2 .= ' placeholder="' . Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS') . '"';
        if ($this->readonly || $this->disabled) {
            $attr2 .= ' disabled="disabled"';
        }

        // Resolve selected categories
        if (!empty($this->value) && is_array($this->value)) {
            $selectedCats = array_map('intval', $this->value);
        } elseif (empty($currentId)) {
            $selectedCats = $this->default ? (array) $this->default : [];
        } else {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                $db->getQuery(true)
                    ->select('catid')
                    ->from('#__pja_events')
                    ->where('id = ' . (int)$currentId)
            );
            $catid = (int)$db->loadResult();
            $selectedCats = $catid ? [$catid] : [];
        }

        $options = (array) $this->getOptions();

        $app->getDocument()->getWebAssetManager()
            ->usePreset('choicesjs')
            ->useScript('webcomponent.field-fancy-select');

        $select = HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            trim($attr),
            'value',
            'text',
            $selectedCats,
            $this->id
        );

        return '<joomla-field-fancy-select ' . $attr2 . '>' . $select . '</joomla-field-fancy-select>';
    }

    protected function getOptions(): array
    {
        if (!class_exists('PlanjeagendaCategories')) {
            require_once JPATH_SITE . '/components/com_planjeagenda/classes/categories.class.php';
        }
       
        self::bootSiteClasses();   
        $rawOptions = PlanjeagendaCategories::getCategoriesTree();

        
        foreach ($rawOptions as &$opt) {
            $opt->text = $opt->treename ?? $opt->catname ?? $opt->text ?? '';
        }
        unset($opt);

        return array_merge(parent::getOptions(), array_values($rawOptions));
    }
}
