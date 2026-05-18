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

if (isset($this->attachments) && is_array($this->attachments) && (count($this->attachments) > 0)) : ?>
    <hr class="klevents-hr" style="display: none;" />
    <div class="klevents-files">
        <?php if (count($this->attachments) > 1) : ?>
            <h2 class="klevents-files"><?php echo Text::_('com_planjeagenda_FILES') ; ?></h2>
        <?php else : ?>
            <h2 class="klevents-files"><?php echo Text::_('com_planjeagenda_FILE') ; ?></h2>
        <?php endif; ?>
        <dl class="klevents-dl">
            <?php foreach ($this->attachments as $index=>$file) : ?>
                <dt class="klevents-files" data-placement="bottom" data-original-title="<?php echo Text::_('com_planjeagenda_FILE'); ?>"><?php echo Text::_('com_planjeagenda_FILE').' '.($index+1); ?>:</dt>
                <dd class="klevents-files">
                    <?php
                    $overlib = Text::_('com_planjeagenda_FILE').': '.$this->escape($file->file);
                    if (!empty($file->name)) {
                        $overlib .= '<br>'.Text::_('com_planjeagenda_FILE_NAME').': '.$this->escape($file->name);
                    }
                    if (!empty($file->description)) {
                        $overlib .= '<br>'.Text::_('com_planjeagenda_FILE_DESCRIPTION').': '.$this->escape($file->description);
                    }
                    ?>
                    <span <?php echo PlanjeagendaOutput::tooltip(Text::_('com_planjeagenda_DOWNLOAD'), $overlib, 'klevents-files'); ?>>
                    <?php
                    $filename    = $this->escape($file->name ? $file->name : $file->file);
                    $image        = $filename.'&nbsp;<i class="fa fa-download"></i>';
                    $attribs    = array('class'=>'klevents-files');
                    echo HTMLHelper::_('link','index.php?option=com_planjeagenda&task=getfile&format=raw&file='.$file->id.'&'.Session::getFormToken().'=1',$image, $attribs);
                    ?>
                    </span>
                </dd>
            <?php endforeach; ?>
        </dl>
    </div>
<?php endif;
