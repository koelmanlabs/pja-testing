<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Export;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $categories;

    public function display($tpl = null)
    {
        $model            = $this->getModel();
        $this->categories = $model->getCategoryOptions();

        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_EXPORT'), 'download');
        ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_planjeagenda');

        return parent::display($tpl);
    }
}
