<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

if (!class_exists('PlanjeagendaOutput', false)) {
    require_once JPATH_SITE . '/components/com_planjeagenda/classes/output.class.php';
}


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
$function = Factory::getApplication()->input->getCmd('function', 'jSelectContact');
?>

<form action="index.php?option=com_planjeagenda&amp;view=contactelement&amp;tmpl=component" method="post" name="adminForm" id="adminForm">

<table class="adminform">
    <tr>
        <td style="width: 100%;">
            <?php echo Text::_('com_planjeagenda_SEARCH').' '.$this->lists['filter']; ?>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo htmlspecialchars($this->lists['search'], ENT_QUOTES, 'UTF-8'); ?>" class="text_area" onChange="document.adminForm.submit();" />
            <button class="buttonfilter" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button class="buttonfilter" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
            <button class="buttonfilter" type="button" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '<?php echo Text::_('com_planjeagenda_SELECTCONTACT') ?>');"><?php echo Text::_('com_planjeagenda_NOCONTACT')?></button>
        </td>
    </tr>
</table>

<table class="table table-striped" id="articleList">
    <thead>
        <tr>
            <th style="width: 7px" class="center"><?php echo Text::_('com_planjeagenda_NUM'); ?></th>
            <th style="text-align: left;" class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_NAME', 'con.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
            <th style="text-align: left;" class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_ADDRESS', 'con.address', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
            <th style="text-align: left;" class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_CITY', 'con.suburb', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
            <th style="text-align: left;" class="title"><?php echo HTMLHelper::_('grid.sort', 'com_planjeagenda_STATE', 'con.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
            <th style="text-align: left;" class="title"><?php echo Text::_('com_planjeagenda_EMAIL'); ?></th>
            <th style="text-align: left;" class="title"><?php echo Text::_('com_planjeagenda_TELEPHONE'); ?></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="12">
                <?php echo (method_exists($this->pagination, 'getPaginationLinks') ? $this->pagination->getPaginationLinks() : $this->pagination?->getListFooter()); ?>
            </td>
        </tr>
    </tfoot>

    <tbody>
        <?php foreach ($this->rows as $i => $row) : ?>
         <tr class="row<?php echo $i % 2; ?>">
            <td class="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
            <td style="text-align: left;">
                <span <?php echo \PlanjeagendaOutput::tooltip(Text::_('com_planjeagenda_SELECT'), $row->name, 'editlinktip'); ?>>
                <a style="cursor:pointer;" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->name)); ?>');"><?php echo $this->escape($row->name); ?></a>
                </span>
            </td>
            <td style="text-align: left;"><?php echo $this->escape($row->address); ?></td>
            <td style="text-align: left;"><?php echo $this->escape($row->suburb); ?></td>
            <td style="text-align: left;"><?php echo $this->escape($row->state); ?></td>
            <td style="text-align: left;"><?php echo $this->escape($row->email_to); ?></td>
            <td style="text-align: left;"><?php echo $this->escape($row->telephone); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<input type="hidden" name="task" value="" />
<input type="hidden" name="function" value="<?php echo $this->escape($function); ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
