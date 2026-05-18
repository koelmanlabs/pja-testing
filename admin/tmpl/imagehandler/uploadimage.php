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

<form method="post" action="<?php echo htmlspecialchars($this->request_url); ?>" enctype="multipart/form-data" name="adminForm" id="adminForm">

<table class="noshow">
    <tr>
        <td style="width: 50%; vertical-align: top;">

            <?php if($this->ftp): ?>
                <fieldset class="adminform">
                    <legend><?php echo Text::_('com_planjeagenda_FTP_TITLE'); ?></legend>

                    <?php echo Text::_('com_planjeagenda_FTP_DESC'); ?>

                    <?php if($this->ftp INSTANCEOF Exception): ?>
                        <p><?php echo Text::_($this->ftp->message); ?></p>
                    <?php endif; ?>

                    <table class="adminform nospace">
                        <tbody>
                            <tr>
                                <td style="width: 120px">
                                    <label for="username"><?php echo Text::_('com_planjeagenda_USERNAME'); ?>:</label>
                                </td>
                                <td>
                                    <input type="text" id="username" name="username" class="input_box" size="70" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 120px">
                                    <label for="password"><?php echo Text::_('com_planjeagenda_PASSWORD'); ?>:</label>
                                </td>
                                <td>
                                    <input type="password" id="password" name="password" class="input_box" size="70" value="" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            <?php endif; ?>

            <fieldset class="adminform">
                <legend><?php echo Text::_('com_planjeagenda_SELECT_IMAGE_UPLOAD'); ?></legend>
                <table class="admintable">
                    <tbody>
                        <tr>
                            <td>
                                <input class="inputbox" name="userfile" id="userfile" type="file" />
                                <br><br>
                                <input class="btn btn-primary" type="submit" value="<?php echo Text::_('com_planjeagenda_UPLOAD') ?>" name="adminForm" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>

        </td>
        <td style="width: 50%; vertical-align: top;">

            <fieldset class="adminform">
                <legend><?php echo Text::_('com_planjeagenda_ATTENTION'); ?></legend>
                <table class="admintable">
                    <tbody>
                        <tr>
                            <td>
                                <b><?php echo Text::_('com_planjeagenda_TARGET_DIRECTORY').':'; ?></b>
                                <?php
                                if($this->task == 'venueimg') {
                                    echo "/images/klevents/venues/";
                                    $this->task = 'imagehandler.venueimgup';
                                } else if($this->task == 'eventimg') {
                                    echo "/images/klevents/events/";
                                    $this->task = 'imagehandler.eventimgup';
                                } else if($this->task == 'categoriesimg') {
                                    echo "/images/klevents/categories/";
                                    $this->task = 'imagehandler.categoriesimgup';
                                }
                                ?>
                                <br>
                                <b><?php echo Text::_('com_planjeagenda_IMAGE_FILESIZE').':'; ?></b> <?php echo $this->jemsettings->sizelimit; ?> kb<br>

                                <?php
                                if($this->jemsettings->gddisabled == 0 || (imagetypes() & IMG_PNG)) {
                                    echo "<br><span style='color:green'>".Text::_('com_planjeagenda_PNG_SUPPORT')."</span>";
                                } else {
                                    echo "<br><span style='color:red'>".Text::_('com_planjeagenda_NO_PNG_SUPPORT')."</span>";
                                }
                                if($this->jemsettings->gddisabled == 0 || (imagetypes() & IMG_JPEG)) {
                                    echo "<br><span style='color:green'>".Text::_('com_planjeagenda_JPG_SUPPORT')."</span>";
                                } else {
                                    echo "<br><span style='color:red'>".Text::_('com_planjeagenda_NO_JPG_SUPPORT')."</span>";
                                }
                                if($this->jemsettings->gddisabled == 0 || (imagetypes() & IMG_GIF)) {
                                    echo "<br><span style='color:green'>".Text::_('com_planjeagenda_GIF_SUPPORT')."</span>";
                                } else {
                                    echo "<br><span style='color:red'>".Text::_('com_planjeagenda_NO_GIF_SUPPORT')."</span>";
                                }
                                if($this->jemsettings->gddisabled == 0 || (imagetypes() & IMG_WEBP)) {
                                    echo "<br><span style='color:green'>".Text::_('com_planjeagenda_WEBP_SUPPORT')."</span>";
                                } else {
                                    echo "<br><span style='color:red'>".Text::_('com_planjeagenda_NO_WEBP_SUPPORT')."</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>

        </td>
    </tr>
</table>

<?php if($this->jemsettings->gddisabled) { ?>

<table class="noshow">
    <tr>
        <td>

            <fieldset class="adminform">
                <legend><?php echo Text::_('com_planjeagenda_ATTENTION'); ?></legend>
                <table class="admintable">
                    <tbody>
                        <tr>
                            <td class="center">
                                <?php echo Text::_('com_planjeagenda_GD_WARNING'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>

        </td>
    </tr>
</table>

<?php } ?>

<?php echo HTMLHelper::_('form.token'); ?>
<input type="hidden" name="option" value="com_planjeagenda" />
<input type="hidden" name="task" value="<?php echo $this->task;?>" />
</form>

<p class="copyright">
</p>
