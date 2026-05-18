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

$function = \Factory::getApplication()->input->getCmd('function', 'jSelectContact');
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

<div id="klevents" class="jem_select_contact">
    <h1 class='componentheading'>
        <?php echo \Text::_('com_planjeagenda_SELECT_CONTACT'); ?>
    </h1>

    <div class="clr"></div>

    <form action="<?php echo \Route::_('index.php?option=com_planjeagenda&view=editevent&layout=choosecontact&tmpl=component&function='.$this->escape($function).'&'.Session::getFormToken().'=1'); ?>" method="post" name="adminForm" id="adminForm">
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
                    <button type="button" class="pointer btn btn-primary" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '<?php echo \Text::_('com_planjeagenda_SELECT_CONTACT') ?>');"><?php echo \Text::_('com_planjeagenda_NOCONTACT')?></button>
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

        <hr class="klevents-hr"/>

        <div class="klevents-sort klevents-sort-small">
            <div class="klevents-list-row klevents-small-list">
                <div class="sectiontableheader klevents-contact-number"><?php echo \Text::_('com_planjeagenda_NUM'); ?></div>
                <div class="sectiontableheader klevents-contact-name"><?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_NAME', 'con.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <div class="sectiontableheader klevents-contact-city"><?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_CITY', 'con.suburb', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
                <div class="sectiontableheader klevents-contact-state"><?php echo \HTMLHelper::_('grid.sort', 'com_planjeagenda_STATE', 'con.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></div>
            </div>
        </div>

        <ul class="eventlist eventtable">
            <?php if (empty($this->rows)) : ?>
                <li class="klevents-event klevents-list-row klevents-small-list"><?php echo \Text::_('com_planjeagenda_NOCONTACTS'); ?></li>
            <?php else :?>
                <?php foreach ($this->rows as $i => $row) : ?>
                    <li class="klevents-event klevents-list-row klevents-small-list row<?php echo $i % 2; ?>">
                        <div class="klevents-event-info-small klevents-contact-number">
                            <?php echo $this->pagination->getRowOffset( $i ); ?>
                        </div>

                        <div class="klevents-event-info-small klevents-contact-name">
              <span <?php echo \PlanjeagendaOutput::tooltip(\Text::_('com_planjeagenda_SELECT'), $row->name, 'editlinktip selectcontact'); ?>>
                                 <a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->name)); ?>');"><?php echo $this->escape($row->name); ?></a>
                            </span>
                        </div>

                        <div class="klevents-event-info-small klevents-contact-city">
                            <?php echo $this->escape($row->suburb); ?>
                        </div>

                        <div class="klevents-event-info-small klevents-contact-state">
                            <?php echo $this->escape($row->state); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <input type="hidden" name="task" value="selectcontact" />
        <input type="hidden" name="option" value="com_planjeagenda" />
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="function" value="<?php echo $this->escape($function); ?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    </form>

    <div class="pagination">
        <?php echo $this->pagination?->getPagesLinks(); ?>
    </div>
</div>
