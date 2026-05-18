<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>

<div id="klevents" class="jem_myevents<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->needLoginFirst) {
        $uri = \Uri::getInstance();
        $returnUrl = $uri->toString();
        $urlLogin = 'index.php?option=com_users&view=login&return=' . base64_encode($returnUrl); ?>
        <button class="btn btn-warning" onclick="location.href='<?php echo htmlspecialchars($uri->root() . $urlLogin, ENT_QUOTES, 'UTF-8'); ?>'"
                type="button"><?php echo \Text::_('com_planjeagenda_LOGIN_TO_ACCESS'); ?></button>

    <?php } else { ?>
        <div class="buttons">
            <?php
            $btn_params = array('task' => $this->task, 'print_link' => $this->print_link, 'archive_link' => $this->archive_link);
            echo \PlanjeagendaOutput::createButtonBar($this->getName(), $this->permissions, $btn_params);
            ?>
        </div>

        <?php if ($this->params->get('show_page_heading', 1)) : ?>
            <h1 class="componentheading">
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        <?php endif; ?>

        <div class="clr"></div>

        <!--table-->
        <?php echo $this->loadTemplate('events');?>

        <!--footer-->
        <div class="copyright">
            <?php echo \PlanjeagendaOutput::footer( ); ?>
        </div>
    <?php } ?>
</div>