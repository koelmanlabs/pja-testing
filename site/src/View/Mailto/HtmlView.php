<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\View\Mailto;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
/**
 * mailto-View
 */
class HtmlView extends BaseHtmlView
{

    protected $form = null;
    protected $canDo;

    /**
     * Display the Hello World view
     *
     * @param   string  $tpl  The name of the layout file to parse.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Default view properties
        $this->pageclass_sfx = '';
        $this->pageheading   = '';
        $this->pagetitle     = '';
        $jemsettings = \PlanjeagendaHelper::config();
        $settings    = \PlanjeagendaHelper::globalattribs();
        $app         = Factory::getApplication();
        $user        = Factory::getApplication()->getIdentity();
        $userId      = $user->id;
        $document    = $app->getDocument();
        $model       = $this->getModel();
        $menu        = $app->getMenu();
        $menuitem    = $menu->getActive();
        $pathway     = $app->getPathway();
        $uri         = Uri::getInstance();

        $this->state = $this->get('State');
        $this->params = $this->state?->get('params');
        $this->link = urldecode($app->input->get('link', '', 'BASE64'));

        $layout = $app->input->get('layout', 'edit');

        $params = $this->params;
        $this->pageclass_sfx = $params->get('pageclass_sfx');
        // Get the form to display
        $this->form = $this->get('Form');


        $title = Text::_('com_planjeagenda_MAILTO_EMAIL_TO_A_FRIEND');

        $params->def('page_title', $title);
        $params->def('page_heading', $title);

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->setLayout($layout);
        // Call the parent display to display the layout file
        parent::display($tpl);

        // Set properties of the html document
        $this->_prepareDocument();
    }

    /**
     * Method to set up the html document properties
     *
     * @return void
     */
    protected function _prepareDocument()
    {
        $document = \Joomla\CMS\Factory::getApplication()->getDocument();
        $app = Factory::getApplication();

        $title = $this->params->get('page_title');
        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $document->setTitle($title);

        // TODO: Is it useful to have meta data in an edit view?
        //       Also shouldn't be "robots" set to "noindex, nofollow"?
        if ($this->params->get('menu-meta_description')) {
            $document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
