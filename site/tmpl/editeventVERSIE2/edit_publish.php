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

<fieldset class="adminform">
    <legend><?php echo \Text::_('com_planjeagenda_EDITEVENT_PUBLISH_TAB'); ?></legend>
    <dl class="klevents-dl">

        <dt><?php echo $this->form->getLabel('published'); ?></dt>
        <dd><?php echo $this->form->getInput('published'); ?></dd>
        <dt><?php echo $this->form->getLabel('featured'); ?></dt>
        <dd><?php echo $this->form->getInput('featured'); ?></dd>
        <dt><?php echo $this->form->getLabel('publish_up'); ?></dt>
        <dd><?php echo $this->form->getInput('publish_up'); ?></dd>
        <dt><?php echo $this->form->getLabel('publish_down'); ?></dt>
        <dd><?php echo $this->form->getInput('publish_down'); ?></dd>
        <dt><?php echo $this->form->getLabel('access'); ?></dt>
        <dd><?php
            echo \HTMLHelper::_(
                'select.genericlist',
                $this->access,
                'jform[access]',
                array('list.attr' => ' class="form-select inputbox" size="1"', 'list.select' => $this->item->access, 'option.attr' => 'disabled', 'id' => 'access')
            );
            ?>
        </dd>

        <p>&nbsp;</p>
</fieldset>
<!-- START META FIELDSET -->
<fieldset class="adminform">
    <legend><?php echo \Text::_('com_planjeagenda_METADATA'); ?></legend>
    <div class="formelm-area">
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[title]')" value="<?php echo \Text::_('com_planjeagenda_TITLE');    ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[a_name]')" value="<?php echo \Text::_('com_planjeagenda_VENUE'); ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[categories]')" value="<?php echo \Text::_('com_planjeagenda_CATEGORIES'); ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[dates]')" value="<?php echo \Text::_('com_planjeagenda_DATE'); ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[times]')" value="<?php echo \Text::_('com_planjeagenda_TIME'); ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[enddates]')" value="<?php echo \Text::_('com_planjeagenda_ENDDATE'); ?>" />
        <input class="inputbox btn btn-secondary" type="button" onclick="insert_keyword('[endtimes]')" value="<?php echo \Text::_('com_planjeagenda_ENDTIME'); ?>" />
        <br>
        <br>
        <?php
        if (!empty($this->item->meta_keywords)) {
            $meta_keywords = $this->item->meta_keywords;
        } else {
            $meta_keywords = $this->jemsettings->meta_keywords;
        }
        ?>
        <dl class="klevents-dl">
            <dt>
                <label for="meta_keywords">
                    <?php echo \Text::_('com_planjeagenda_META_KEYWORDS') . ':'; ?>
                </label>
            </dt>
            <dd><textarea class="inputbox" name="meta_keywords" id="meta_keywords" rows="5" cols="40" maxlength="150" onfocus="get_inputbox('meta_keywords')" onblur="change_metatags()"><?php echo htmlspecialchars($meta_keywords, ENT_QUOTES, 'UTF-8'); ?></textarea></dd>
        </dl>
    </div>
    <div class="formelm-area">
        <?php
        if (!empty($this->item->meta_description)) {
            $meta_description = $this->item->meta_description;
        } else {
            $meta_description = $this->jemsettings->meta_description;
        }
        ?>
        <dl class="klevents-dl">
            <dt>
                <label for="meta_description">
                    <?php echo \Text::_('com_planjeagenda_META_DESCRIPTION') . ':'; ?>
                </label>
            </dt>
            <dd><textarea class="inputbox" name="meta_description" id="meta_description" rows="5" cols="40" maxlength="200" onfocus="get_inputbox('meta_description')" onblur="change_metatags()"><?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?></textarea></dd>
        </dl>
    </div>
    <!-- include the metatags end-->

    <script>
        <!--
        starter("<?php
            echo \Text::_('com_planjeagenda_META_ERROR');
            ?>"); // window.onload is already in use, call the function manualy instead
        -->
    </script>

</fieldset>
