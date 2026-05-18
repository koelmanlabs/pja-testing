<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

if (empty($this->jemsettings->tablewidth)) :
  echo $this->loadTemplate('jem_eventslist'); // The new layout
else :
  echo $this->loadTemplate('jem_eventslist_small'); // Similar to the old table-layout
endif;

echo PlanjeagendaOutput::lightbox();
