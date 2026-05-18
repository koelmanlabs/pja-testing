<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Help;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app        = Factory::getApplication();
        $lang       = $app->getLanguage();
        $helpsearch = $app->input->getString('filter_search', '');
        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $langTag = $lang->getTag();
        $helpDir = JPATH_ADMINISTRATOR . '/components/com_planjeagenda/help/';
        if (!is_dir($helpDir . $langTag)) { $langTag = 'en-GB'; }

        $this->langTag    = $langTag;
        $this->helpsearch = $helpsearch;
        $this->toc        = $this->getHelpToc($helpsearch, $helpDir . $langTag);
        $this->addToolbar();
        return parent::display($tpl);
    }

    protected function getHelpToc($helpsearch, $dir)
    {
        if (!is_dir($dir)) { return []; }
        $files = Folder::files($dir, '\.xml$|\.html$');
        $toc = [];
        foreach ($files as $file) {
            $buffer = file_get_contents($dir . '/' . $file);
            if (preg_match('#<title>(.*?)</title>#', $buffer, $m)) {
                $title = trim($m[1]);
                if ($title && (!$helpsearch || \Joomla\String\StringHelper::strpos(strip_tags($buffer), $helpsearch) !== false)) {
                    $toc[$file] = $title;
                }
            }
        }
        asort($toc);
        return $toc;
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_HELP'), 'help');
        ToolbarHelper::cancel('settings.cancel', 'JTOOLBAR_CLOSE');
    }
}
