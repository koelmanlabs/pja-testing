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
?>


<!-- IMAGE -->
<?php if ($this->item->locimage || $this->jemsettings->imageenabled != 0) : ?>
    <fieldset class="jem_fldst_image">
        <legend><?php echo \Text::_('com_planjeagenda_EDITVENUE_IMAGE_LEGEND'); ?></legend>
        <?php if ($this->jemsettings->imageenabled != 0) : ?>
            <dl class="adminformlist klevents-dl">
                <dt><?php echo $this->form->getLabel('userfile'); ?></dt>
                <?php if ($this->item->locimage) : ?>
                    <dd>
                        <?php echo \PlanjeagendaOutput::flyer($this->item, $this->limage, 'venue', 'locimage'); ?>
                        <input type="hidden" name="locimage" id="locimage" value="<?php echo $this->item->locimage; ?>" />
                    </dd>
                    <dt> </dt>
                <?php endif; ?>
                <dd><?php echo $this->form->getInput('userfile'); ?></dd>
                <dt> </dt>
                <dd><button type="button" class="button3 btn" onclick="document.getElementById('jform_userfile').value = ''"><?php echo \Text::_('JSEARCH_FILTER_CLEAR') ?></button></dd>
                <?php if ($this->item->locimage) : ?>
                    <dt><?php echo \Text::_('com_planjeagenda_REMOVE_IMAGE'); ?></dt>
                    <dd><?php
                        echo \HTMLHelper::image('media/com_planjeagenda/images/publish_r.webp', null, array('id' => 'userfile-remove', 'data-id' => $this->item->id, 'data-type' => 'venues', 'title' => \Text::_('com_planjeagenda_REMOVE_IMAGE'), 'class' =>'btn')); ?>
                    </dd>
                <?php endif; ?>
            </dl>
            <input type="hidden" name="removeimage" id="removeimage" value="0" />
        <?php endif; ?>
    </fieldset>
<?php endif;
