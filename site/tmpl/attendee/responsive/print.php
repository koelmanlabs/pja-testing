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

<table class="table" style="width: 100%">
    <tr>
        <td class="sectionname" style="width: 100%"><span
            style="color: #C24733; font-size: 18px; font-weight: bold;"><?php echo \Text::_( 'com_planjeagenda_REGISTERED_USER' ); ?>
            </span>
        </td>
        <td><div class="button2-left">
                <div class="blank">
                    <a href="#" onclick="window.print();return false;"><span class="icon icon-print"></span></a>
                </div>
            </div>
        </td>
    </tr>
</table>
<br>
<table class="adminlist">
    <tr>
        <td style="text-align: left;"><b><?php echo \Text::_( 'com_planjeagenda_TITLE' ).':'; ?> </b>&nbsp;<?php echo $this->escape($this->event->title); ?><br>
            <b><?php echo \Text::_( 'com_planjeagenda_DATE' ).':'; ?> </b>&nbsp;<?php echo \PlanjeagendaOutput::formatLongDateTime($this->event->dates, $this->event->times,
                    $this->event->enddates, $this->event->endtimes, $this->settings->get('global_show_timedetails', 1)); ?></td>
    </tr>
</table>
<br>
<?php $regname = $this->settings->get('global_regname', '1'); ?>
<table class="table table-striped" id="articleList">
    <thead>
        <tr>
            <th class="title"><?php echo \Text::_( 'com_planjeagenda_NUM' ); ?></th>
            <th class="title"><?php echo \Text::_( $regname ? 'com_planjeagenda_NAME' : 'com_planjeagenda_USERNAME' ); ?></th>
            <?php if ($this->enableemailaddress == 1) : ?>
            <th class="title"><?php echo \Text::_( 'com_planjeagenda_EMAIL' ); ?></th>
            <?php endif; ?>
            <th class="title"><?php echo \Text::_( 'com_planjeagenda_REGDATE' ); ?></th>
            <th class="title"><?php echo \Text::_('com_planjeagenda_STATUS' ); ?></th>
            <th class="title"><?php echo \Text::_('com_planjeagenda_PLACES' ); ?></th>
            <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
            <th class="title"><?php echo \Text::_('com_planjeagenda_COMMENT'); ?></th>
            <?php endif; ?>
        </tr>
    </thead>

    <tbody>
        <?php
        $regname = $this->settings->get('global_regname', '1');
        $k = 0;
    $i = 0;
        foreach ($this->rows as $row) :
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td><?php echo ++$i; ?></td>
            <td><?php echo $regname ? $row->name : $row->username; ?></td>
            <?php if ($this->enableemailaddress == 1) : ?>
            <td><?php echo $row->email; ?></td>
            <?php endif; ?>
            <td><?php if (!empty($row->uregdate)) { echo \HTMLHelper::_('date', $row->uregdate, \Text::_('DATE_FORMAT_LC5')); } ?></td>
            <?php
            switch ($row->status) :
            case -1: // explicitely unregistered
                $text = 'com_planjeagenda_ATTENDEES_NOT_ATTENDING';
                break;
            case  0: // invited, not answered yet
                $text = 'com_planjeagenda_ATTENDEES_INVITED';
                break;
            case  1: // registered
                $text = $row->waiting ? 'com_planjeagenda_ATTENDEES_ON_WAITINGLIST' : 'com_planjeagenda_ATTENDEES_ATTENDING';
                break;
            default: // oops...
                $text = 'com_planjeagenda_ATTENDEES_STATUS_UNKNOWN';
                break;
            endswitch; ?>
            <td><?php echo \Text::_($text); ?></td>
            <td><?php echo $row->places; ?></td>
            <?php if (!empty($this->jemsettings->regallowcomments)) : ?>
            <td><?php echo (strlen($row->comment) > 256) ? (substr($row->comment, 0, 254).'&hellip;') : $row->comment; ?></td>
            <?php endif; ?>
        </tr>
        <?php $k = 1 - $k;
        endforeach; ?>
    </tbody>
</table>

<div class="copyright">
    <?php echo \PlanjeagendaOutput::footer(); ?>
</div>
