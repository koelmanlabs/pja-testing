<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

//$max_custom_fields = $this->settings->get('global_editvenue_maxnumcustomfields', -1); // default to All
?>

<fieldset>
    <legend><?php echo \Text::_('com_planjeagenda_EDITVENUE_PUBLISHING_LEGEND'); ?></legend>
    <dl class="adminformlist klevents-dl">
        <dt><?php echo $this->form->getLabel('published'); ?></dt>
        <dd><?php echo $this->form->getInput('published'); ?></dd>
        <dt><?php echo $this->form->getLabel('access'); ?></dt>
        <dd><?php echo $this->form->getInput('access'); ?></dd>
    </dl>
</fieldset>

<!-- META -->
<fieldset class="">
    <legend><?php echo \Text::_('com_planjeagenda_METADATA'); ?></legend>
    <input type="button" class="button btn" value="<?php echo \Text::_('com_planjeagenda_ADD_VENUE_CITY'); ?>" onclick="meta()" />
    <p>&nbsp;</p>
    <?php foreach ($this->form->getFieldset('meta') as $field) : ?>
        <dl class="klevents-dl">
            <dt class="control-label"><?php echo $field->label; ?></dt>
            <dd class="controls"><?php echo $field->input; ?></dd>
        </dl>
    <?php endforeach; ?>
</fieldset>
