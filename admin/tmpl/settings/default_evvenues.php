<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
$group = 'globalattribs';
?>

<div class="width-100" style="padding: 10px 1vw;">
    <fieldset class="options-form">
        <legend><?php echo Text::_('com_planjeagenda_SETTINGS_VENUE_PART'); ?></legend>
        <ul class="adminformlist">
            <li><div class="label-form"><?php echo $this->form->renderfield('event_show_venue',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_show_detailsadress',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_show_locdescription',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_show_detlinkvenue',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_show_mapserv',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_tld',$group); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('event_lg',$group); ?></div></li>
        </ul>
    </fieldset>
</div>
