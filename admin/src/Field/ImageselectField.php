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

class ImageselectField extends ListField
{
    protected $type = 'Imageselect';

    public function getLabel() {
        // code that returns HTML that will be shown as the label
    }

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     *
     */
    public function getInput()
    {
        // ImageType
        $imagetype = $this->element['imagetype'];

        // Build the script.
        $script = array();
        $script[] = '    function SelectImage(image, imagename) {';
        $script[] = '        document.getElementById(\'a_image\').value = image';
        $script[] = '        document.getElementById(\'a_imagename\').value = imagename';
        $script[] = '        document.getElementById(\'imagelib\').src = \'../images/klevents/'.$imagetype.'/\' + image';
        // $script[] = '        window.parent.SqueezeBox.close()';
        $script[] = '        $(".btn-close").trigger("click");';
        $script[] = '    }';

        switch ($imagetype)
        {
            case 'categories':
                $task         = 'categoriesimg';
                $taskselect = 'selectcategoriesimg';
                break;
            case 'events':
                $task         = 'eventimg';
                $taskselect = 'selecteventimg';
                break;
            case 'venues':
                $task         = 'venueimg';
                $taskselect = 'selectvenueimg';
                break;
        }

        // Add the script to the document head.
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(implode("\n", $script));

        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_planjeagenda&amp;view=imagehandler&amp;layout=uploadimage&amp;task='.$task.'&amp;tmpl=component';
        $link2 = 'index.php?option=com_planjeagenda&amp;view=imagehandler&amp;task='.$taskselect.'&amp;tmpl=component';

        //
        $html[] = "<div class=\"fltlft\">";
        $html[] = "<input class=\"form-control\" style=\"background: #fff;\" type=\"text\" id=\"a_imagename\" value=\"$this->value\" disabled=\"disabled\" onchange=\"javascript:if (document.forms[0].a_imagename.value!='') {document.imagelib.src='../images/klevents/$imagetype/' + document.forms[0].a_imagename.value} else {document.imagelib.src='../media/com_planjeagenda/images/blank.webp'}\"; />";
        $html[] = "</div>";
        $html[] = "<div class=\"button2-left\"><div class=\"blank\">";
            $html[] = HTMLHelper::_(
                'bootstrap.renderModal',
                'imageupload-modal',
                array(
                    'url'    => $link,
                    'title'  => Text::_('com_planjeagenda_UPLOAD'),
                    'width'  => '650px',
                    'height' => '500px',
                    'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('com_planjeagenda_CLOSE') . '</button>'
                )
            );
            $html[] ='<button type="button" class="btn btn-primary btn-margin" data-bs-toggle="modal"  data-bs-target="#imageupload-modal">'.Text::_('com_planjeagenda_UPLOAD').'</button>';

        $html[] ='</div></div>';
        // $html[] = "<div class=\"button2-left\"><div class=\"blank\"><a class=\"modal\" title=\"".Text::_('com_planjeagenda_SELECTIMAGE')."\" href=\"$link2\" rel=\"{handler: 'iframe', size: {x: 650, y: 375}}\">".Text::_('com_planjeagenda_SELECTIMAGE')."</a></div></div>\n";
        $html[] = "<div class=\"button2-left\"><div class=\"blank\">";
        $html[] = HTMLHelper::_(
            'bootstrap.renderModal',
            'imageselect-modal',
            array(
                'url'    => $link2,
                'title'  => Text::_('com_planjeagenda_SELECTIMAGE'),
                'width'  => '650px',
                'height' => '500px',
                'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('com_planjeagenda_CLOSE') . '</button>'
            )
        );
        $html[] = "<button type=\"button\" class=\"btn btn-primary btn-margin\" data-bs-toggle=\"modal\" data-bs-target=\"#imageselect-modal\">".Text::_('com_planjeagenda_SELECTIMAGE')."
        </button>";
        $html[] = "</div></div>";
        $html[] = "\n&nbsp;<input class=\"btn btn-danger btn-margin\" type=\"button\" onclick=\"SelectImage('', '".Text::_('com_planjeagenda_SELECTIMAGE')."');\" value=\"".Text::_('com_planjeagenda_RESET')."\" />";
        $html[] = "\n<input type=\"hidden\" id=\"a_image\" name=\"$this->name\" value=\"$this->value\" />";
        $html[] = "<img src=\"../media/com_planjeagenda/images/blank.webp\" name=\"imagelib\" id=\"imagelib\" class=\"venue-image\" alt=\"".Text::_('com_planjeagenda_SELECTIMAGE_PREVIEW')."\" />";
        $html[] = "<script type=\"text/javascript\">";
        $html[] = "if (document.forms[0].a_imagename.value!='') {";
        $html[] = "var imname = document.forms[0].a_imagename.value;";
        $html[] = "jsimg='../images/klevents/$imagetype/' + imname;";
        $html[] = "document.getElementById('imagelib').src= jsimg;";
        $html[] = "}";
        $html[] = "</script>";

        return implode("\n", $html);
    }
}
