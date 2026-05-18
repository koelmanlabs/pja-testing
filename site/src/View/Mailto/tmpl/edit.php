<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$app = \Factory::getApplication();
$document = $app->getDocument();
$wa = $document->getWebAssetManager();
        $wa->useScript('keepalive')
            ->useScript('form.validate');


// Create shortcut to parameters.
$params        = $this->params;

?>
<script>
    Joomla.submitbutton = function(task) {
        if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
            $(".sbmit-btn").prop('disabled',true);
            $(".sbmit-btn .spinner-border").removeClass('d-none');
            Joomla.submitform(task);
        }
    }
</script>

<div id="klevents" class="jem_editevent<?php echo $this->pageclass_sfx; ?>">
    <div class="edit item-page p-3">
        <form enctype="multipart/form-data" action="<?php echo \Route::_('index.php?option=com_planjeagenda&view=mailto&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

            <div id="mailto-window">
                <h2>
                    <?php echo \Text::_('com_planjeagenda_MAILTO_EMAIL_TO_A_FRIEND'); ?>
                </h2>
                <fieldset style="margin: 0px;">
                    <?php foreach ($this->form->getFieldset('') as $field) : ?>
                        <?php if (!$field->hidden) : ?>
                            <?php echo $field->renderField(); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="btn btn-primary sbmit-btn" onclick="Joomla.submitbutton('mailto.save')"><?php echo \Text::_('com_planjeagenda_MAILTO_SEND') ?>
                            <div class="spinner-border spinner-grow-sm d-none" role="status">
                            <span class="visually-hidden"></span>
                            </div></button>
                        </div>
                    </div>
                </fieldset>

                <input type="hidden" name="task" value="" />
                <input type="hidden" name="link" value="<?php echo $this->link; ?>" />
                <?php echo \HTMLHelper::_('form.token'); ?>
            </div>
        </form>
    </div>

    <div class="copyright">
        <?php echo \PlanjeagendaOutput::footer(); ?>
    </div>
</div>
