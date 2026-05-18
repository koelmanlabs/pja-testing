<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$function = \Factory::getApplication()->input->getCmd('function', 'jSelectUsers');
$checked = 0;

?>

<script>
    function tableOrdering( order, dir, view )
    {
        var form = document.getElementById("adminForm");

        form.filter_order.value     = order;
        form.filter_order_Dir.value    = dir;
        form.submit( view );
    }
</script>
<script>
    function checkList(form)
    {
        var r='', i, n, e;
        for (i=0, n=form.elements.length; i<n; i++)
        {
            e = form.elements[i];
            if (e.type == 'checkbox' && e.id.indexOf('cb') === 0 && e.checked)
            {
                if (r) { r += ','; }
                r += e.value;
            }
        }
        return r;
    }
</script>

<div id="klevents" class="jem_select_users">
    <h1 class='componentheading'>
        <?php echo \Text::_('com_planjeagenda_SELECT_USERS_TO_INVITE'); ?>
    </h1>

    <div class="clr"></div>

    <form action="<?php echo \Route::_('index.php?option=com_planjeagenda&view=editevent&layout=chooseusers&tmpl=component&function='.$this->escape($function).'&'.Session::getFormToken().'=1'); ?>" method="post" name="adminForm" id="adminForm">
        <?php if(0) : ?>
            <div class="klevents-row valign-baseline">
                <div id="jem_filter" class="klevents-form klevents-row klevents-justify-start">
                    <div>
                        <?php
                        echo '<label for="filter_type">'.\Text::_('com_planjeagenda_FILTER').'</label>&nbsp;';
                        ?>
                    </div>
                    <div class="klevents-row klevents-justify-start klevents-nowrap">
                        <?php echo $this->searchfilter.'&nbsp;'; ?>
                        <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="inputbox" onChange="document.adminForm.submit();" />
                    </div>
                    <div class="klevents-row klevents-justify-start klevents-nowrap">
                        <button type="submit" class="pointer btn btn-primary"><?php echo \Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                        <button type="button" class="pointer btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo \Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
                        <?php /*<button type="button" class="pointer btn btn-primary" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '0');"><?php echo \Text::_('com_planjeagenda_NOUSERS')?></button>*/ ?>
                    </div>
                </div>
                <div class="klevents-row klevents-justify-start klevents-nowrap">
                    <div>
                        <?php echo '<label for="limit">'.\Text::_('com_planjeagenda_DISPLAY_NUM').'</label>&nbsp;'; ?>
                    </div>
                    <div>&nbsp;</div>
                    <div>
                        <?php echo $this->pagination?->getLimitBox(); ?>
                    </div>
                </div>
            </div>
        <?php endif;?>

        <hr class="klevents-hr"/>

        <div class="klevents-sort klevents-sort-small">
            <div class="klevents-list-row klevents-small-list">
                <div class="sectiontableheader klevents-users-number"><?php echo \Text::_('com_planjeagenda_NUM'); ?></div>
                <div class="sectiontableheader klevents-users-checkall"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo \Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></div>
                <div class="sectiontableheader klevents-users-name"><?php echo \Text::_('com_planjeagenda_NAME'); ?></div>
                <div class="sectiontableheader klevents-users-state"><?php echo \Text::_('com_planjeagenda_STATUS'); ?></div>
                <div class="sectiontableheader klevents-users-state"><?php echo \Text::_('com_planjeagenda_PLACES'); ?></div>
            </div>
        </div>

        <ul class="eventlist eventtable">
            <?php if (empty($this->rows)) : ?>
                <li class="klevents-event klevents-list-row klevents-small-list"><?php echo \Text::_('com_planjeagenda_NOUSERS'); ?></li>
            <?php else :?>
                <?php foreach ($this->rows as $i => $row) : ?>
                    <li class="klevents-event klevents-list-row klevents-small-list row<?php echo $i % 2; ?>">
                        <div class="klevents-event-info-small klevents-users-number">
                            <?php echo $this->pagination->getRowOffset( $i ); ?>
                        </div>

                        <div class="klevents-event-info-small klevents-users-checkall">
                            <?php
                            //echo \HTMLHelper::_('grid.id', $i, $row->id);
                            $cb = \HTMLHelper::_('grid.id', $i, $row->id);
                            if ($row->status == 0) {
                                //    \PlanjeagendaHelper::addLogEntry('before: '.$cb, __METHOD__);
                                $cb = preg_replace('/(onclick=)/', 'checked $1', $cb);
                                ++$checked;
                                //    \PlanjeagendaHelper::addLogEntry('after:  '.$cb, __METHOD__);
                            }
                            echo $cb;
                            ?>
                        </div>

                        <div class="klevents-event-info-small klevents-users-name">
                            <?php echo $this->escape($row->name); ?>
                        </div>

                        <div class="klevents-event-info-small klevents-users-state">
                            <?php echo jemhtml::toggleAttendanceStatus( 0, $row->status, false); ?>
                        </div>

                        <div class="klevents-event-info-small klevents-users-places">
                            <?php echo $this->escape($row->places); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <hr class="klevents-hr"/>

        <div class="klevents-row klevents-justify-start valign-baseline">
            <div style="padding-right:5px;">
                <?php echo \Text::_('com_planjeagenda_SELECT');?>
            </div>
            <div style="padding-right:10px;">
                <?php echo \Text::_('com_planjeagenda_PLACES'); ?>
            </div>
            <div style="padding-right:10px;">
                <input id="places" name="places" type="number" style="text-align: center; width:auto;"  value="0" max="1" min="0">
            </div>
        </div>

        <input type="hidden" name="task" value="selectusers" />
        <input type="hidden" name="option" value="com_planjeagenda" />
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="function" value="<?php echo $this->escape($function); ?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
        <input type="hidden" name="boxchecked" value="<?php echo $checked; ?>" />
    </form>

    <div class="pagination">
        <?php echo $this->pagination?->getPagesLinks(); ?>
    </div>

    <hr class="klevents-hr"/>

    <div class="klevents-row klevents-justify-end">
        <button type="button" class="pointer btn btn-primary" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>(checkList(document.adminForm), document.adminForm.boxchecked.value);"><?php echo \Text::_('com_planjeagenda_SAVE'); ?></button>
    </div>
</div>
