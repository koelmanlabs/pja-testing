<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Main;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $events;
    protected $venue;
    protected $category;
    protected $updatedata;

    public function display($tpl = null)
    {
        // Haal data op uit het model (zorg dat deze functies in je MainModel staan)
        $this->events     = $this->get('EventStats');
        $this->venue      = $this->get('VenueStats');
        $this->category   = $this->get('CategoryStats');
        $this->updatedata = $this->get('UpdateData');

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $canConfig = Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_planjeagenda');
        
        ToolbarHelper::title(Text::_('COM_PLANJEAGENDA_DASHBOARD'), 'home');
        
        if ($canConfig) {
            ToolbarHelper::preferences('com_planjeagenda');
        }
    }

    // Helper functie voor de icoontjes (modernere versie van quickiconButton)
    public function quickiconButton($link, $icon, $text, $badge = '')
    {
        $iconMap = [
            'calendar' => 'icon-calendar', 'plus' => 'icon-plus', 'location' => 'icon-location',
            'folder' => 'icon-folder', 'cleanup' => 'icon-purge', 'list-2' => 'icon-list',
            'options' => 'icon-options', 'loop' => 'icon-loop', 'info' => 'icon-info'
        ];
        $iconClass = $iconMap[$icon] ?? 'icon-cog';
        
        ob_start();
        ?>
    <div class="col-6 col-md-4 col-xl-3 mb-3">
        <a href="<?php echo $link; ?>" class="card h-100 shadow-sm border dashboard-card text-decoration-none">
            <div class="card-body text-center p-3 position-relative">
                <?php if ($badge) : ?>
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger">
                        <?php echo $badge; ?>
                    </span>
                <?php endif; ?>
                <div class="icon-container mb-2 text-secondary">
                    <span class="<?php echo $iconClass; ?>" style="font-size: 2rem;"></span>
                </div>
                <div class="fw-bold text-dark small text-uppercase tracking-wider"><?php echo $text; ?></div>
            </div>
        </a>
    </div>
    <?php
    return ob_get_clean();
}
}