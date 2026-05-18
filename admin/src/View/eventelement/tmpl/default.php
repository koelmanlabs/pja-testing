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

<form action="index.php?option=com_planjeagenda&amp;view=eventelement&amp;tmpl=component" method="post" name="adminForm" id="adminForm">

<table class="adminform">
    <tr>
        <td style="width: 100%;">
            <?php echo Text::_('com_planjeagenda_SEARCH').' '.$this->lists['filter']; ?>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="text_area" onChange="document.adminForm.submit();" />
            <button class="buttonfilter" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button class="buttonfilter" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </td>
        <td nowrap="nowrap">
            <select name="filter_state" class="inputbox" onchange="this.form.submit()">
            <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
            <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions',array('all' => 0, 'trash' => 0)), 'value', 'text', $this->filter_state, true);?>
            </select>
        </td>
    </tr>
</table>

<table class="table table-striped" id="articleList">
    <thead>
        <tr>
            <th class="center" style="width: 5px;"><?php echo Text::_('com_planjeagenda_NUM'); ?></th>
            <th class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_EVENT_TITLE', 'a.title', $this->lists['order_Dir'], $this->lists['order'], 'eventelement' ); ?></th>
            <th class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_DATE', 'a.dates', $this->lists['order_Dir'], $this->lists['order'], 'eventelement' ); ?></th>
            <th class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_START', 'a.times', $this->lists['order_Dir'], $this->lists['order'], 'eventelement' ); ?></th>
            <th class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_VENUE', 'loc.venue', $this->lists['order_Dir'], $this->lists['order'], 'eventelement' ); ?></th>
            <th class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_CITY', 'loc.city', $this->lists['order_Dir'], $this->lists['order'], 'eventelement' ); ?></th>
            <th class="title"><?php echo Text::_('com_planjeagenda_CATEGORY'); ?></th>
            <th class="center" style="width: 1%" nowrap="nowrap"><?php echo Text::_('JSTATUS'); ?></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="8">
                <?php echo (method_exists($this->pagination, 'getPaginationLinks') ? $this->pagination->getPaginationLinks() : $this->pagination->getListFooter()); ?>
            </td>
        </tr>
    </tfoot>

    <tbody>
            <?php foreach ($this->rows as $i => $row) : ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
            <td>
                <span <?php echo PlanjeagendaOutput::tooltip(Text::_('com_planjeagenda_SELECT'), $row->title, 'editlinktip'); ?>>
                <a style="cursor:pointer" onclick="window.parent.elSelectEvent('<?php echo $row->id; ?>', '<?php echo str_replace( array("'", "\""), array("\\'", ""), $row->title ); ?>');">
                    <?php echo $this->escape($row->title); ?>
                </a></span>
            </td>
            <td>
                <?php
                    //Format date
                    echo PlanjeagendaOutput::formatLongDateTime($row->dates, null, $row->enddates, null);
                ?>
            </td>
            <td>
                <?php
                    //Prepare time
                    if (!$row->times) {
                        $displaytime = '-';
                    } else {
                        $time = date( $this->jemsettings->formattime, strtotime( $row->times ));
                        $displaytime = $time.' '.$this->jemsettings->timename;
                    }
                    echo $displaytime;
                ?>
            </td>
            <td><?php echo $row->venue ? $this->escape($row->venue) : '-'; ?></td>
            <td><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>
            <td>
            <?php
            # we're referring to the helper due to the multi-cat feature
            echo implode(", ",PlanjeagendaOutput::getCategoryList($row->categories, false));
            ?>
            </td>
            <td class="center">
                <?php echo HTMLHelper::_('jgrid.published', $row->published, $i,'',false); ?>
            </td>
        </tr>
            <?php endforeach; ?>
    </tbody>

</table>

<p class="copyright">
</p>

<input type="hidden" name="task" value="" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
