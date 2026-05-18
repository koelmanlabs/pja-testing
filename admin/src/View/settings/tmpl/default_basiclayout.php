<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="width-100" style="padding: 10px 1vw;">
    <fieldset class="options-form">
        <legend><?php echo Text::_( 'com_planjeagenda_LAYOUT_STYLE_SETTINGS' ); ?></legend>
        <ul class="adminformlist">
            <li><div class="label-form"><?php echo $this->form->renderfield('layoutstyle'); ?></div></li>
            <li><div class="label-form"><?php echo $this->form->renderfield('useiconfont'); ?></div></li>
        </ul>
    </fieldset>
</div>
