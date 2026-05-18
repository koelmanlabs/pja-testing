<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
?>

<?php if (isset($this->attachments) && is_array($this->attachments) && (count($this->attachments) > 0)) : ?>
    <div class="files">
        <h2 class="description"><?php echo Text::_('com_planjeagenda_FILES'); ?></h2>
        <table class="file">
            <tbody>
            <?php foreach ($this->attachments as $file) : ?>
                <tr>
                    <td>
                        <?php
                        $overlib = Text::_('com_planjeagenda_FILE').': '.$this->escape($file->file);
                        if (!empty($file->name)) {
                            $overlib .= '<br>'.Text::_('com_planjeagenda_FILE_NAME').': '.$this->escape($file->name);
                        }
                        if (!empty($file->description)) {
                            $overlib .= '<br>'.Text::_('com_planjeagenda_FILE_DESCRIPTION').': '.$this->escape($file->description);
                        }
                        ?>
                        <span <?php echo PlanjeagendaOutput::tooltip(Text::_('com_planjeagenda_DOWNLOAD'), $overlib, 'file-dl-icon file-name'); ?>>
                    <?php
                    $filename    = $this->escape($file->name ? $file->name : $file->file);
                    $image        = HTMLHelper::_('image','com_planjeagenda/download_16.webp', Text::_('com_planjeagenda_DOWNLOAD'),NULL,true)." "."<span class=file-name>".$filename."</span>";
                    $attribs    = array('class'=>'file-name');
                    echo HTMLHelper::_('link','index.php?option=com_planjeagenda&task=getfile&format=raw&file='.$file->id.'&'.Session::getFormToken().'=1',$image,$attribs);
                    ?>
                </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif;
