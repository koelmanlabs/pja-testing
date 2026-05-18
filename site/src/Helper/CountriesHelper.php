<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Site\Helper;

defined('_JEXEC') or die;

// Load the legacy helper file (global namespace classes)
$_file = JPATH_SITE . '/components/com_planjeagenda/helpers/countries.php';
if (file_exists($_file)) {
    require_once $_file;
}
