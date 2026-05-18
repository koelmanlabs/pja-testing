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
use Joomla\CMS\Router\Route;

?>
<div id="klevents" class="jem_categories<?php echo $this->pageclass_sfx;?>">
    <div class="buttons">
        <?php
        $btn_params = array('id' => $this->id, 'task' => $this->task, 'print_link' => $this->print_link, 'archive_link' => $this->archive_link);
        echo \PlanjeagendaOutput::createButtonBar($this->getName(), $this->permissions, $btn_params);
        ?>
    </div>

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1 class="componentheading">
        <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <div class="clr"></div>

    <?php foreach (($this->rows ?? []) as $row) : ?>
            <?php
            // has user access
            $categoriesaccess = '';
            if (!$row->user_has_access_category) {
                // show a closed lock icon
                $categoriesaccess = '<span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
            } ?>
    <div class="klevents cat_id<?php echo $row->id; ?>">
        <h2>
            <?php echo \HTMLHelper::_('link', \Route::_($row->linktarget), $this->escape($row->catname)); ?>
                    <?php echo $categoriesaccess; ?>
        </h2>
                <?php if ($row->user_has_access_category) : ?>
        <div class="floattext">
            <?php if ($this->jemsettings->discatheader) { ?>
                <div class="catimg">
                    <?php // flyer
                        if (empty($row->image)) {
                            $jemsettings = \PlanjeagendaHelper::config();
                            $imgattribs['width'] = $jemsettings->imagewidth;
                            $imgattribs['height'] = $jemsettings->imagehight;

                            echo \HTMLHelper::_('image', 'com_planjeagenda/noimage.webp', $row->catname, $imgattribs, true);
                        } else {
                            $cimage = \PlanjeagendaImage::flyercreator($row->image, 'category');
                            echo \PlanjeagendaOutput::flyer($row, $cimage, 'category');
                        }
                    ?>
                </div>
            <?php } ?>
            <div class="description cat<?php echo $row->id; ?>">
                <?php echo $row->description; ?>
                <p>
                    <?php echo \HTMLHelper::_('link', \Route::_($row->linktarget), $row->linktext); ?>
                    (<?php echo $row->assignedevents ? $row->assignedevents : '0'; ?>)
                </p>
            </div>
        </div>

        <?php if ($i = count($row->subcats)) : ?>
                        <?php
                        // has user access
                        $subcategoriesaccess = '';
                        if (!$row->user_has_access_category) {
                            // show a closed lock icon
                            $subcategoriesaccess = '<span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
                        } ?>
            <div class="subcategories">
                <?php echo \Text::_('com_planjeagenda_SUBCATEGORIES'); ?>
                            <?php echo $categoriesaccess; ?>
            </div>
            <div class="subcategorieslist">
                <?php foreach ($row->subcats as $sub) : ?>
                                <?php
                                // has user access
                                $eventsaccess = '';
                                if (!$sub->user_has_access_category ) {
                                    // show a closed lock icon
                                    $eventsaccess = '<span class="icon-lock klevents-lockicon" aria-hidden="true"></span>';
                                } ?>
                    <strong>
                        <a href="<?php echo \Route::_(\PlanjeagendaHelperRoute::getCategoryRoute($sub->slug, $this->task)); ?>">
                            <?php echo $this->escape($sub->catname); ?></a>
                    </strong> <?php echo '(' . ($sub->assignedevents != null ? $sub->assignedevents : 0) . (--$i ? '),' : ')'); ?>
                                <?php echo $eventsaccess; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!--table-->
        <?php
            if ($this->params->get('detcat_nr', 0) > 0) {
                $this->catrow = $row;
                echo $this->loadTemplate('table');
            }
        ?>
                <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!--pagination-->
    <div class="pagination">
        <?php echo $this->pagination?->getPagesLinks(); ?>
    </div>

    <!--copyright-->
    <div class="copyright">
        <?php echo \PlanjeagendaOutput::footer( ); ?>
    </div>
</div>
<?php echo \PlanjeagendaOutput::lightbox();
