<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */
defined('_JEXEC') or die;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\OutputHelper;

HTMLHelper::_('jquery.framework');

$app = Factory::getApplication();
$document = $app->getDocument();
$wa = $document->getWebAssetManager();
        $wa->useScript('jquery')
        ->useScript('keepalive')
            ->useScript('form.validate');

$selectuser_link = Route::_('index.php?option=com_planjeagenda&task=attendee.selectuser&tmpl=component');
echo HTMLHelper::_(
    'bootstrap.renderModal',
    'user-modal',
    array(
        'url'    => $selectuser_link.'&amp;'.Session::getFormToken().'=1',
        'title'  => Text::_('com_planjeagenda_SELECT'),
        'width'  => '800px',
        'height' => '450px',
        'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('com_planjeagenda_CLOSE') . '</button>'
    )
);
?>

<script>
function modalSelectUser(id, username)
{
        jQuery('#uid').val(id)  ;
        jQuery('#username').val(username);
        // window.parent.SqueezeBox.close();
        jQuery("#user-modal").modal("hide");
}
Joomla.submitbutton = function(task)
    {
        if (task == 'attendee.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            if (task == 'attendee.cancel' || document.getElementById('adminForm').uid.value != 0) {
                Joomla.submitform(task, document.getElementById('adminForm'));
            } else {
                alert("<?php echo Text::_('com_planjeagenda_SELECT_AN_USER', true); ?>");
                return false;
            }
        } else {
            alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>


<form action="<?php echo Route::_('index.php?option=com_planjeagenda&view=attendee'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <fieldset>
        <h3><?php echo Text::_('com_planjeagenda_DETAILS'); ?></h3>
        <?php if (!empty($this->row->id)) : ?>
        <p>
            <?php echo Text::_('com_planjeagenda_EDITATTENDEE_NOTICE'); ?>
        </p>
        <?php endif; ?>

        <table  class="admintable">
            <tr>
                <td class="key">
                    <label for="eventtitle" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_EVENT'), Text::_('com_planjeagenda_EVENT_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_EVENT').':'; ?>
                    </label>
                </td>
                <td>
                    <input type="text" name="eventtitle" id="eventtitle" class="form-control inputbox required valid form-control-success" readonly="readonly"
                           value="<?php echo !empty($this->row->eventtitle) ? $this->row->eventtitle : '?'; ?>"
                    />
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="username" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_USER'), Text::_('com_planjeagenda_USER_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_USER').':'; ?>
                    </label>
                </td>
                <td>
                    <input type="text" name="username" id="username" class="form-control inputbox required valid form-control-success" readonly="readonly" value="<?php echo $this->row->username; ?>" />
                    <input type="hidden" name="uid" id="uid" value="<?php echo $this->row->uid; ?>" />
                    <a class="usermodal" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-modal">
                        <span class="btn btn-primary"><?php echo Text::_('com_planjeagenda_SELECT_USER')?></span>
                    </a>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="status" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_STATUS'), Text::_('com_planjeagenda_STATUS_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_STATUS').':'; ?>
                    </label>
                </td>
                <td>
                    <?php
                    $options = array(HTMLHelper::_('select.option',  0, Text::_('com_planjeagenda_ATTENDEES_INVITED')),
                                     HTMLHelper::_('select.option', -1, Text::_('com_planjeagenda_ATTENDEES_NOT_ATTENDING')),
                                     HTMLHelper::_('select.option',  1, Text::_('com_planjeagenda_ATTENDEES_ATTENDING')),
                                     HTMLHelper::_('select.option',  2, Text::_('com_planjeagenda_ATTENDEES_ON_WAITINGLIST'), array('disable' => empty($this->row->waitinglist))));

                    $selectOptions = array('id' => 'reg_status', 'class' => 'form-select');
                    echo HTMLHelper::_('select.genericlist', $options, 'status', $selectOptions);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="eventtitle" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_ATTENDEES_PLACES'), Text::_('com_planjeagenda_ATTENDEES_PLACES_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_ATTENDEES_PLACES').':'; ?>
                    </label>
                </td>
                <td>
                    <input type="number" name="places" id="places" class="form-control inputbox" min="<?php echo $this->row->minbookeduser; ?>" max="<?php echo $this->row->maxbookeduser; ?>"
                           value="<?php echo !empty($this->row->places) ? $this->row->places : $this->row->minbookeduser; ?>"
                    />
                </td>
            </tr>
            <?php if (!empty($this->jemsettings->regallowcomments)): ?>
            <tr>
                <td class="key" style="vertical-align: baseline;">
                    <label for="comment" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_COMMENT'), Text::_('com_planjeagenda_COMMENT_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_COMMENT').':'; ?>
                    </label>
                </td>
                <td>
                    <textarea name="comment" id="reg_comment" rows="3" cols="30" maxlength="255"
                        ><?php if (!empty($this->row->comment)) { echo $this->row->comment; }
                        /* looks crazy, but required to prevent unwanted white spaces within textarea content! */
                    ?></textarea>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($this->row->recurrence_type && $this->row->seriesbooking): ?>
            <tr>
                <td class="key">
                    <label for="seriesbooking" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_EDITEVENT_FIELD_BOOKED_SERIES'), Text::_('com_planjeagenda_EDITEVENT_FIELD_BOOKED_SERIES')); ?>>
                        <?php echo Text::_('com_planjeagenda_EDITEVENT_FIELD_BOOKED_SERIES').':'; ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" id="seriesbooking" name="seriesbooking" value="1""/>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="key">
                    <label for="sendemail" <?php echo OutputHelper::tooltip(Text::_('com_planjeagenda_SEND_REGISTRATION_NOTIFICATION_EMAIL'), Text::_('com_planjeagenda_SEND_REGISTRATION_NOTIFICATION_EMAIL_DESC')); ?>>
                        <?php echo Text::_('com_planjeagenda_SEND_REGISTRATION_NOTIFICATION_EMAIL').':'; ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" id="sendemail" name="sendemail" value="1" checked="checked"/>
                </td>
            </tr>

        </table>
    </fieldset>

    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
    <input type="hidden" name="event" value="<?php echo ($this->row->event ? $this->row->event : $this->event); ?>" />
    <input type="hidden" name="task" value="" />
</form>
