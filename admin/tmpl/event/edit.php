<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * @todo: move js to a file
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\RecurrenceHelper;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\OutputHelper;

$this->document->addScript(Uri::root(true) . '/media/com_planjeagenda/js/recurrence.js');
$this->document->addScript(Uri::root(true) . '/media/com_planjeagenda/js/event-edit.js');

$objectId = (int) $this->item->id; // Gebruiken we in de template

$options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'opacityTransition' => true,
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('inlinehelp')
    ->useScript('multiselect');

?>


<script>
    Joomla.submitbutton = function(task)
    {
        if (task == 'event.cancel' || document.formvalidator.isValid(document.getElementById('event-form'))) {
            Joomla.submitform(task, document.getElementById('event-form'));

            <?php //echo $this->form->getField('articletext')->save(); ?>

            document.getElementById("meta_keywords").value = $keywords;
            document.getElementById("meta_description").value = $description;
        }
    }
</script>

<form
        action="<?php echo Route::_('index.php?option=com_planjeagenda&layout=edit&id='.(int) $this->item->id); ?>"
        class="form-validate" method="post" name="adminForm" id="event-form" enctype="multipart/form-data">

    <?php $recurr = empty($this->item->recurr_bak) ? $this->item : $this->item->recurr_bak; ?>
    <?php if (!empty($recurr->recurrence_number) || !empty($recurr->recurrence_type)) : ?>
        <div class="description">
            <div style="float:left;">
                <?php echo OutputHelper::recurrenceicon($recurr, false, false); ?>
            </div>
            <div class="floattext" style="margin-left:36px;">
                <strong><?php echo Text::_('com_planjeagenda_EDITEVENT_WARN_RECURRENCE_TITLE'); ?></strong>
                <br>
                <?php
                if (!empty($recurr->recurrence_type) && empty($recurr->recurrence_first_id)) {
                    echo nl2br(Text::_('com_planjeagenda_EDITEVENT_WARN_RECURRENCE_FIRST_TEXT'));
                } else {
                    echo nl2br(Text::_('com_planjeagenda_EDITEVENT_WARN_RECURRENCE_TEXT'));
                }
                ?>
            </div>
        </div>
        <div class="clear"></div>
    <?php endif; ?>

    <!-- START OF LEFT DIV -->
    <div class="row">
        <div class="col-md-7">

            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'info', 'recall' => true, 'breakpoint' => 768]); ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'info', Text::_('com_planjeagenda_EVENT_INFO_TAB')); ?>

            <!-- START OF LEFT FIELDSET -->
            <fieldset class="adminform">
                <legend>
                    <?php echo empty($this->item->id) ? Text::_('com_planjeagenda_NEW_EVENT') : Text::sprintf('com_planjeagenda_EVENT_DETAILS', $this->item->id); ?>
                </legend>

                <ul class="adminformlist">
                    <li><div class="label-form"><?php echo $this->form->renderfield('title'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('alias'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('dates'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('enddates'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('times'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('endtimes'); ?></div></li>
                    <?php if($this->jemsettings->defaultCategory && empty($item->id)) {
                        $this->form->setFieldAttribute('cats', 'default', $this->jemsettings->defaultCategory);
                    } ?>
                    <li><div class="label-form"><?php echo $this->form->renderfield('cats'); ?></div></li>
                </ul>
            </fieldset>

            <fieldset class="adminform">
                <ul class="adminformlist">
                    <?php if($this->jemsettings->defaultVenue && empty($item->id)) {
                        $this->form->setFieldAttribute('locid', 'default', $this->jemsettings->defaultVenue);
                    } ?>
                    <li><div class="label-form"><?php echo $this->form->renderfield('municipality_id'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('locid'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('contactid'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('published'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('featured'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('publish_up'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('publish_down'); ?></div></li>
                    <li><div class="label-form"><?php echo $this->form->renderfield('access'); ?></div></li>
                </ul>
            </fieldset>

            <fieldset class="adminform">
                <div class="clr"></div>
                <?php echo $this->form->getLabel('articletext'); ?>
                <div class="clr"></div>
                <?php echo $this->form->getInput('articletext'); ?>
                <!-- END OF FIELDSET -->
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'attachments', Text::_('com_planjeagenda_EVENT_ATTACHMENTS_TAB')); ?>
            <?php //echo HTMLHelper::_('tabs.panel',Text::_('com_planjeagenda_EVENT_ATTACHMENTS_TAB'), 'attachments' ); ?>
            <?php echo $this->loadTemplate('attachments'); ?>

            <?php //echo HTMLHelper::_('tabs.panel',Text::_('com_planjeagenda_EVENT_SETTINGS_TAB'), 'event-settings' ); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'event-settings', Text::_('com_planjeagenda_EVENT_SETTINGS_TAB')); ?>
            <?php echo $this->loadTemplate('settings'); ?>

            <?php //echo HTMLHelper::_('tabs.end'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <!-- END OF LEFT DIV -->
        </div>

        <!--  START RIGHT DIV -->
        <div class="col-md-5">

            <!-- START OF SLIDERS -->
            <?php //echo HTMLHelper::_('sliders.start', 'event-sliders-'.$this->item->id, $options); ?>

            <!-- START OF PANEL PUBLISHING -->
            <?php //echo HTMLHelper::_('sliders.panel', Text::_('com_planjeagenda_FIELDSET_PUBLISHING'), 'publishing-details'); ?>

            <!-- RETRIEVING OF FIELDSET PUBLISHING -->
            <div class="accordion" id="accordionEventForm">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="publishing-details-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#publishing-details" aria-expanded="true" aria-controls="publishing-details">
                            <?php echo Text::_('com_planjeagenda_FIELDSET_PUBLISHING'); ?>
                        </button>
                    </h2>
                    <div id="publishing-details" class="accordion-collapse collapse show" aria-labelledby="publishing-details-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <ul class="adminformlist">
                                <li><div class="label-form"><?php echo $this->form->renderfield('id'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('created_by'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('hits'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('created'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('modified'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('version'); ?></div></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="custom-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#custom" aria-expanded="true" aria-controls="custom">
                            <?php echo Text::_('com_planjeagenda_CUSTOMFIELDS'); ?>
                        </button>
                    </h2>
                    <div id="custom" class="accordion-collapse collapse" aria-labelledby="custom-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <ul class="adminformlist">
                                <?php foreach($this->form->getFieldset('custom') as $field): ?>
                                    <li><?php echo $field->label; ?> <?php echo $field->input; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="registra-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#registra" aria-expanded="true" aria-controls="registra">
                            <?php echo Text::_('com_planjeagenda_REGISTRATION'); ?>
                        </button>
                    </h2>
                    <div id="registra" class="accordion-collapse collapse" aria-labelledby="registra-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <ul class="adminformlist" style="margin-bottom: 60px;">
                                <li><div class="label-form"><?php echo $this->form->renderfield('registra'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('registra_from'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('registra_until'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('unregistra'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('unregistra_until'); ?></div></li>
                                <?php if($this->jemsettings->regallowinvitation) { ?>
                                    <li><div class="label-form"><?php echo $this->form->renderfield('reginvitedonly'); ?></div></li>
                                <?php } ?>
                                <li><div class="label-form"><?php echo $this->form->renderfield('maxplaces'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('minbookeduser'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('maxbookeduser'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('reservedplaces'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('waitinglist'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('requestanswer'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('seriesbooking'); ?></div></li>
                                <li><div class="label-form"><?php echo $this->form->renderfield('singlebooking'); ?></div></li>
                                <li>
                                    <div class="label-form"><div class="control-group">
                                            <div class="control-label">
                                                <label id="availableplaces-lbl"><?php echo Text::_ ('com_planjeagenda_AVAILABLE_PLACES') . ':';?></label>
                                            </div>
                                            <div class="controls">
                                                <input type="number" name="availableplaces" id="availableplaces" value=<?php echo  ($this->item->maxplaces? ($this->item->maxplaces-$this->item->booked-$this->item->reservedplaces):'0'); ?> class="form-control inputbox" size="4" aria-describedby="jform_reservedplaces-desc" readonly>
                                                <div id="availableplaces-desc" class="hide-aware-inline-help d-none">
                                                    <small class="form-text">
                                                        <?php echo Text::_ ('com_planjeagenda_AVAILABLE_PLACES_DESC') ;?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- START OF PANEL IMAGE -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="image-event-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#image-event" aria-expanded="true" aria-controls="image-event">
                            <?php echo Text::_('com_planjeagenda_IMAGE'); ?>
                        </button>
                    </h2>

                    <div id="image-event" class="accordion-collapse collapse" aria-labelledby="image-event-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <ul class="adminformlist" style="margin-bottom: 130px;">
                                <li><div class="label-form"><?php echo $this->form->renderfield('datimage'); ?></div></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="recurrence-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#recurrence" aria-expanded="true" aria-controls="recurrence">
                            <?php echo Text::_('com_planjeagenda_RECURRING_EVENTS'); ?>
                        </button>
                    </h2>

                    <div id="recurrence" class="accordion-collapse collapse" aria-labelledby="recurrence-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <ul class="adminformlist">
                                <li><div class="label-form"><?php echo $this->form->renderfield('recurrence_type', null, $recurr->recurrence_type); ?></div></li>
                                <li id="recurrence_output" class="m-3">
                                    <?php if ($recurr->recurrence_number){ ?>
                                        <input type="hidden" name="recurrence_number" id="recurrence_number" value="<?php echo $recurr->recurrence_number;?>">
                                    <?php } ?>
                                    <label></label>
                                </li>
                                <?php
                                $anticipation = RecurrenceHelper::anticipation(
                                    (int) $recurr->recurrence_type, $this->jemsettings
                                );
                                $limitdate = (new \DateTime('now'))
                                    ->modify('+' . $anticipation . ' month')
                                    ->format('d-m-Y');
                                ?>
                                <li id="counter_row" style="display: none;">
                                    <div class="label-form"><?php echo $this->form->renderfield('recurrence_limit_date', null, $recurr->recurrence_limit_date ?? $recurr->recurrence_limit_date); ?></div>
                                    <br><div><small>
                                            <?php
                                            echo Text::sprintf(Text::_('com_planjeagenda_EVENT_NOTICE_GENSHIELD'),$limitdate);
                                            ?></small></div>
                                </li>
                            </ul>

                            <input type="hidden" name="recurrence_number" id="recurrence_number" value="<?php echo $this->item->recurrence_number;?>" />
                            <input type="hidden" name="recurrence_number_saved" id="recurrence_number_saved" value="<?php echo $this->item->recurrence_number;?>" />
                            <input type="hidden" name="recurrence_byday" id="recurrence_byday" value="<?php echo $this->item->recurrence_byday;?>" />
                            <input type="hidden" name="recurrence_bylastday" id="recurrence_bylastday" value="<?php echo $this->item->recurrence_bylastday;?>" />

                            <script
                                    type="text/javascript">
                                <!--
                                var $select_output = new Array();
                                $select_output[1] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_DAY'); ?>";
                                $select_output[2] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_WEEK'); ?>";
                                $select_output[3] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_MONTH'); ?>";
                                $select_output[4] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_WEEKDAY'); ?>";
                                $select_output[5] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_YEAR'); ?>";
                                $select_output[6] = "<?php echo Text::_ ('com_planjeagenda_OUTPUT_LASTDAY'); ?>";

                                var $weekday = new Array();
                                $weekday[0]  = new Array("MO", "<?php echo Text::_ ('com_planjeagenda_MONDAY'); ?>");
                                $weekday[1]  = new Array("TU", "<?php echo Text::_ ('com_planjeagenda_TUESDAY'); ?>");
                                $weekday[2]  = new Array("WE", "<?php echo Text::_ ('com_planjeagenda_WEDNESDAY'); ?>");
                                $weekday[3]  = new Array("TH", "<?php echo Text::_ ('com_planjeagenda_THURSDAY'); ?>");
                                $weekday[4]  = new Array("FR", "<?php echo Text::_ ('com_planjeagenda_FRIDAY'); ?>");
                                $weekday[5]  = new Array("SA", "<?php echo Text::_ ('com_planjeagenda_SATURDAY'); ?>");
                                $weekday[6]  = new Array("SU", "<?php echo Text::_ ('com_planjeagenda_SUNDAY'); ?>");

                                var $before_last = "<?php echo Text::_ ('com_planjeagenda_BEFORE_LAST'); ?>";
                                var $last = "<?php echo Text::_ ('com_planjeagenda_LAST'); ?>";

                                var $lastday = new Array();
                                $lastday[0]  = new Array("L1", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY'); ?>");
                                $lastday[1]  = new Array("L2", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_SECOND'); ?>");
                                $lastday[2]  = new Array("L3", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_THIRD'); ?>");
                                $lastday[3]  = new Array("L4", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_FOURTH'); ?>");
                                $lastday[4]  = new Array("L5", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_FIFTH'); ?>");
                                $lastday[5]  = new Array("L6", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_SIXTH'); ?>");
                                $lastday[6]  = new Array("L7", "<?php echo Text::_ ('com_planjeagenda_LAST_DAY_SEVEN'); ?>");

                                start_recurrencescript("jform_recurrence_type");
                                -->
                            </script>
                            <?php /* show "old" recurrence settings for information */
                            if (!empty($this->item->recurr_bak->recurrence_type)) {
                                $recurr_desc = RecurrenceHelper::describe($this->item->recurr_bak);
                                $recurrence_first_id = $this->item->recurr_bak->recurrence_first_id ?? 0;
                                if ($recurr_desc) {
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!-- START OF PANEL META -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="meta-event-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#meta-event" aria-expanded="true" aria-controls="meta-event">
                            <?php echo Text::_('com_planjeagenda_METADATA_INFORMATION'); ?>
                        </button>
                    </h2>

                    <div id="meta-event" class="accordion-collapse collapse" aria-labelledby="meta-event-header" data-bs-parent="#accordionEventForm">
                        <div class="accordion-body">
                            <fieldset class="panelform">
                                <input class="inputbox" type="button" onclick="insert_keyword('[title]')" value="<?php echo Text::_ ( 'com_planjeagenda_EVENT_TITLE' );    ?>" />
                                <input class="inputbox" type="button" onclick="insert_keyword('[a_name]')" value="<?php    echo Text::_ ( 'com_planjeagenda_VENUE' );?>" />
                                <input class="inputbox" type="button" onclick="insert_keyword('[categories]')" value="<?php    echo Text::_ ( 'com_planjeagenda_CATEGORIES' );?>" />
                                <input class="inputbox" type="button" onclick="insert_keyword('[dates]')" value="<?php echo Text::_ ( 'com_planjeagenda_STARTDATE' );?>" />

                                <p>
                                    <input class="inputbox" type="button" onclick="insert_keyword('[times]')" value="<?php echo Text::_ ( 'com_planjeagenda_STARTTIME' );?>" />
                                    <input class="inputbox" type="button" onclick="insert_keyword('[enddates]')" value="<?php echo Text::_ ( 'com_planjeagenda_ENDDATE' );?>" />
                                    <input class="inputbox" type="button" onclick="insert_keyword('[endtimes]')" value="<?php echo Text::_ ( 'com_planjeagenda_ENDTIME' );?>" />
                                </p>
                                <br>

                                <br>
                                <label for="meta_keywords"><?php echo Text::_ ('com_planjeagenda_META_KEYWORDS') . ':';?></label>
                                <br>

                                <?php
                                if (! empty ( $this->item->meta_keywords )) {
                                    $meta_keywords = $this->item->meta_keywords;
                                } else {
                                    $meta_keywords = $this->jemsettings->meta_keywords;
                                }
                                ?>
                                <textarea class="inputbox form-control" name="meta_keywords" id="meta_keywords" rows="6" cols="40" maxlength="150" onfocus="get_inputbox('meta_keywords')" onblur="change_metatags()"><?php echo $meta_keywords; ?></textarea>

                                <label for="meta_description"><?php echo Text::_ ('com_planjeagenda_META_DESCRIPTION') . ':';?></label>
                                <br>

                                <?php
                                if (! empty ( $this->item->meta_description )) {
                                    $meta_description = $this->item->meta_description;
                                } else {
                                    $meta_description = $this->jemsettings->meta_description;
                                }
                                ?>
                                <textarea class="inputbox form-control" name="meta_description" id="meta_description" rows="6" cols="40" maxlength="200"    onfocus="get_inputbox('meta_description')" onblur="change_metatags()"><?php echo $meta_description;?></textarea>
                            </fieldset>

                            <fieldset class="panelform">
                                <ul class="adminformlist">
                                    <?php foreach($this->form->getGroup('metadata') as $field): ?>
                                        <li>
                                            <?php if (!$field->hidden): ?>
                                                <?php echo $field->label; ?>
                                            <?php endif; ?>
                                            <?php echo $field->input; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </fieldset>

                            <script>
                                <!--
                                starter("<?php
                                    echo Text::_ ( 'com_planjeagenda_META_ERROR' );
                                    ?>");    // window.onload is already in use, call the function manualy instead
                                -->
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="task" value="" />
            <!-- author_ip removed from form: always set server-side on save -->
            <?php echo HTMLHelper::_('form.token'); ?>
            <!--  END RIGHT DIV -->
        </div>
        <div class="clr"></div>
</form>
<script>
    output_recurrencescript();
</script>