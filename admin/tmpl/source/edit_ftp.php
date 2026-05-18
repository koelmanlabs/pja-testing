<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<fieldset class="adminform" title="<?php echo Text::_('com_planjeagenda_CSSMANAGER_FTP_TITLE'); ?>">
    <legend><?php echo Text::_('com_planjeagenda_CSSMANAGER_FTP_TITLE'); ?></legend>

    <?php echo Text::_('com_planjeagenda_CSSMANAGER_FTP_DESC'); ?>

    <?php if ($this->ftp instanceof Exception): ?>
        <p class="error"><?php echo Text::_($this->ftp->message); ?></p>
    <?php endif; ?>

    <table class="adminform">
        <tbody>
            <tr>
                <td style="width:120px">
                    <label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
                </td>
                <td>
                    <input type="text" id="username" name="username" class="inputbox" size="70" value="" />
                </td>
            </tr>
            <tr>
                <td style="width:120px">
                    <label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                </td>
                <td>
                    <input type="password" id="password" name="password" class="inputbox" size="70" value="" />
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>
