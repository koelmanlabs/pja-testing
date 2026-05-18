<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>

<div id="klevents" class="jem_venueslist<?php echo $this->pageclass_sfx;?>">
    <div class="buttons">
        <?php
        //$btn_params = array('task' => $this->task, 'print_link' => $this->print_link);
        // Modern action bar
        ?>
        <div class="pja-ev-actionbar" style="margin-bottom:1rem;">
            <div class="pja-ev-actionbar__left"></div>
            <div class="pja-ev-actionbar__right">
                <?php if (!empty($btn_params['print_link'] ?? '')): ?>
                <a href="<?php echo $btn_params['print_link']; ?>"
                   class="pja-ev-action-icon"
                   title="<?php echo Text::_('JGLOBAL_PRINT'); ?>"
                   onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480');return false;"
                   aria-label="<?php echo Text::_('JGLOBAL_PRINT'); ?>">
                    <svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M128 0C92.7 0 64 28.7 64 64v96h64V64H354.7L384 93.3V160h64V93.3c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0H128zM384 352v32 64H128V384 368 352H384zm64 32h32c17.7 0 32-14.3 32-32V256c0-35.3-28.7-64-64-64H64c-35.3 0-64 28.7-64 64v96c0 17.7 14.3 32 32 32H64v64c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V384z"/></svg>
                </a>
                <?php endif; ?>
                <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=eventslist&format=raw&layout=ics'); ?>"
                   class="pja-ev-action-icon"
                   title="<?php echo Text::_('com_planjeagenda_ICAL'); ?>">
                    <svg width="15" height="15" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"/></svg>
                    <span>iCal</span>
                </a>
            </div>
        </div>
        <?php
        ?>
    </div>



    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1 class="componentheading">
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <div class="clr"></div>

    <?php if ($this->params->get('showintrotext')) : ?>
        <div class="description no_space floattext">
            <?php echo $this->params->get('introtext'); ?>
        </div>
    <?php endif; ?>
    <!--table-->
    <form action="<?php echo htmlspecialchars($this->action); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo $this->loadTemplate('venues');?>

    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_planjeagenda" />
    <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <?php if ($this->params->get('showfootertext')) : ?>
        <div class="description no_space floattext">
            <?php echo $this->params->get('footertext'); ?>
        </div>
    <?php endif; ?>
    <!--footer-->
    <div class="copyright">
        <?php echo PlanjeagendaOutput::footer( ); ?>
    </div>
</div>