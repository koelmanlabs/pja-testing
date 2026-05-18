<?php
namespace KoelmanLabs\Component\Planjeagenda\Administrator\View\Imagehandler;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Object\CMSObject;

class HtmlView extends BaseHtmlView
{
    public $_tmp_img;

    public function display($tpl = null)
    {
        $app    = Factory::getApplication();
        $option = $app->input->getString('option', 'com_planjeagenda');

        if ($this->getLayout() === 'uploadimage') {
            return $this->_displayuploadimage($tpl);
        }

        $task   = $app->input->get('task', '');
        $search = $app->getUserStateFromRequest($option.'.filter_search', 'filter_search', '', 'string');
        $search = trim(\Joomla\String\StringHelper::strtolower($search));

        $folder = 'events'; $redi = 'selecteventimg';
        if ($task === 'selectvenueimg')       { $folder = 'venues';     $redi = 'selectvenueimg'; }
        if ($task === 'selectcategoriesimg')  { $folder = 'categories'; $redi = 'selectcategoriesimg'; }
        $app->input->set('folder', $folder);
        $app->allowCache(false);

        $app->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');

        $images     = $this->get('images');
        $pagination = $this->get('Pagination');

        if ($search || (is_array($images) && count($images) > 0)) {
            $this->images     = $images;
            $this->folder     = $folder;
            $this->task       = $redi;
            $this->search     = $search;
            $this->state      = $this->get('state');
            $this->pagination = $pagination;
            return parent::display($tpl);
        }
        $app->enqueueMessage(Text::_('COM_PLANJEAGENDA_NO_IMAGES_AVAILABLE'), 'notice');
        $this->setLayout('uploadimage');
        return $this->_displayuploadimage($tpl);
    }

    public function setImage($index = 0)
    {
        $this->_tmp_img = $this->images[$index] ?? new CMSObject;
    }

    protected function _displayuploadimage($tpl = null)
    {
        $this->task        = Factory::getApplication()->input->get('task', '');
        $this->request_url = Uri::getInstance()->toString();
        $this->ftp         = ClientHelper::setCredentialsFromRequest('ftp');
        Factory::getApplication()->getDocument()->getWebAssetManager()
            ->registerStyle('planjeagenda.backend', 'com_planjeagenda/backend.css')
            ->useStyle('planjeagenda.backend');
        return parent::display($tpl);
    }
}
