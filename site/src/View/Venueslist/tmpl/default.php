<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;
?>

<div id="klevents" class="jem_venueslist<?php echo $this->pageclass_sfx; ?>">
    <div class="buttons">
        <?php
        $btn_params = array('task' => $this->task, 'print_link' => $this->print_link);
        echo \PlanjeagendaOutput::createButtonBar($this->getName(), $this->permissions, $btn_params);
        ?>
    </div>

    <?php
    if ($this->params->get('show_page_heading', 1)) : ?>
        <h1 class="componentheading">
            <?php
            echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php
    endif; ?>

    <div class="clr"></div>

    <?php
    if ($this->params->get('showintrotext')) : ?>
        <div class="description no_space floattext">
            <?php
            echo $this->params->get('introtext'); ?>
        </div>
    <?php
    endif; ?>
    <!--table-->
    <?php echo $this->loadTemplate('venues'); ?>

    <!--footer-->

    <div class="pagination">
        <?php
        echo $this->pagination?->getPagesLinks(); ?>
    </div>
    <div class="copyright">
        <?php
        echo \PlanjeagendaOutput::footer(); ?>
    </div>
</div>
