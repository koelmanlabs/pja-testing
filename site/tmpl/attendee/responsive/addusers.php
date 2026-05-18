<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Form\Form;

$function = \Factory::getApplication()->input->getCmd('function', 'jSelectUsers');
$checked = 0;

// Get the form.
Form::addFormPath(JPATH_SITE . '/components/com_planjeagenda' . '/forms');
$form = Form::getInstance('com_planjeagenda.addusers', 'addusers');

if (empty($form)) {
    return false;
}
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
        <?php echo \Text::_('com_planjeagenda_SELECT_USERS_AND_STATUS'); ?>
    </h1>

    <div class="clr"></div>

    <form action="<?php echo \Route::_('index.php?option=com_planjeagenda&view=attendees&layout=addusers&tmpl=component&function='.$this->escape($function).'&id='.$this->event->id.'&'.Session::getFormToken().'=1'); ?>" method="post" name="adminForm" id="adminForm">

        <?php if(1) : ?>
    <div class="klevents-row valign-baseline">
      <div id="jem_filter" class="klevents-form klevents-row klevents-justify-start">
        <div>
          <?php
          echo '<label for="filter_type">'.\Text::_('com_planjeagenda_FILTER').'</label>';
          ?>
        </div>
        <div class="klevents-row klevents-justify-start klevents-nowrap">
          <?php echo $this->searchfilter; ?>
          <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="inputbox" onChange="document.adminForm.submit();" />
        </div>
        <div class="klevents-row klevents-justify-start klevents-nowrap">
          <button type="submit" class="pointer btn btn-primary"><?php echo \Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
          <button type="button" class="pointer btn btn-secondary" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo \Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
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

    </div>
        <?php endif; ?>

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
              <?php echo \HTMLHelper::_('grid.id', $i, $row->id); ?>
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

        <?php
        if($this->event->maxbookeduser!=0)
        {
            $placesavailableuser = $this->event->maxbookeduser;
        }else{
            $placesavailableuser= null;
        }
        ?>

        <div class="klevents-row klevents-justify-start valign-baseline">
            <div class="choose-status">
                <?php echo \Text::_('com_planjeagenda_SELECT');?> <?php echo $form->getLabel('status'); ?> <?php echo $form->getInput('status'); ?>
            </div>
            <div class="choose-places">
                <?php echo \Text::_('com_planjeagenda_SELECT');?> <?php echo \Text::_('com_planjeagenda_PLACES'); ?> <input id="places" name="places" type="number" style="text-align: center; width:auto;" value="<?php echo $this->event->minbookeduser; ?>" max="<?php echo ($placesavailableuser > 0 ? $placesavailableuser : ($placesavailableuser ?? '')); ?>" min="<?php echo $this->event->minbookeduser; ?>">
            </div>
            <?php if ($this->event->recurrence_type && $this->event->seriesbooking): ?>
                <div class="choose-places">
                    <?php echo \Text::_('com_planjeagenda_SERIES_BOOKED').':'; ?>
                    <input type="checkbox" id="seriesbooking" name="seriesbooking" />
                </div>
            <?php else : ?>
                <input type="hidden" name="seriesbooking" value=-1 />
            <?php endif; ?>
        </div>

        <input type="hidden" name="task" value="selectusers" />
        <input type="hidden" name="option" value="com_planjeagenda" />
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="function" value="<?php echo $this->escape($function); ?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
        <input type="hidden" name="boxchecked" value="<?php echo $checked; ?>" />

    <div class="pagination">
        <?php echo $this->pagination?->getPagesLinks(); ?>
    </div>

    <div class="klevents-row klevents-justify-end">
        <button type="button" class="pointer btn btn-primary" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>_newusers(checkList(document.adminForm),document.adminForm.boxchecked.value,document.adminForm.status.value, document.adminForm.places.value, <?php echo $this->event->id; ?>, document.adminForm.seriesbooking.value, '<?php echo Session::getFormToken(); ?>');">
      <?php echo \Text::_('com_planjeagenda_SAVE'); ?>
    </button>
    </div>
    </form>
</div>
