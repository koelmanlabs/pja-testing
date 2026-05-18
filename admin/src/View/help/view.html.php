<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;

/**
 * View class for the JEM Help screen
 *
 * @package JEM
 */
class PlanjeagendaViewHelp extends PlanjeagendaAdminView
{

    public function display($tpl = null)
    {
        //initialise variables
        $lang = Factory::getApplication()->getLanguage();
        $app = Factory::getApplication();
        $this->document = $app->getDocument();

        //get vars
        $helpsearch = Factory::getApplication()->input->getString('filter_search', '');

        // Load css
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')->useStyle('planjeagenda.backend');

        // Check for files in the actual language
        $langTag = $lang->getTag();

        if (!is_dir(JPATH_SITE .'/administrator/components/com_planjeagenda/help/'.$langTag)) {
            $langTag = 'en-GB';        // use english as fallback
        }

        //search the keyword in the files
        $toc = PlanjeagendaViewHelp::getHelpToc($helpsearch);

        //assign data to template
        $this->langTag    = $langTag;
        $this->helpsearch = $helpsearch;
        $this->toc        = $toc;

        // add toolbar
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Compiles the help table of contents
     * Based on the Joomla admin component
     *
     * @param  string A specific keyword on which to filter the resulting list
     */
    public function getHelpTOC($helpsearch)
    {
        $lang = Factory::getApplication()->getLanguage();

        // Check for files in the actual language
        $langTag = $lang->getTag();

        if (!is_dir(JPATH_SITE .'/administrator/components/com_planjeagenda/help/'.$langTag)) {
            $langTag = 'en-GB';        // use english as fallback
        }
        $files = Folder::files(JPATH_SITE .'/administrator/components/com_planjeagenda/help/'.$langTag, '\.xml$|\.html$');

        $toc = array();
        foreach ($files as $file) {
            $buffer = file_get_contents(JPATH_SITE .'/administrator/components/com_planjeagenda/help/'.$langTag.'/'.$file);
            if (preg_match('#<title>(.*?)</title>#', $buffer, $m)) {
                $title = trim($m[1]);
                if ($title) {
                    if ($helpsearch) {
                        if (\Joomla\String\StringHelper::strpos(strip_tags($buffer), $helpsearch) !== false) {
                            $toc[$file] = $title;
                        }
                    } else {
                        $toc[$file] = $title;
                    }
                }
            }
        }
        asort($toc);
        return $toc;
    }

    /**
     * Add Toolbar
     */
    protected function addToolbar()
    {
        //create the toolbar
        ToolbarHelper::title(Text::_('com_planjeagenda_HELP'), 'help');
        ToolbarHelper::cancel('settings.cancel', 'JTOOLBAR_CLOSE');
        ToolBarHelper::divider();
        ToolBarHelper::help('help', true, 'https://www.koelmanlabs.nl/documentation/manual/backend/control-panel/help');
    }
}
?>
