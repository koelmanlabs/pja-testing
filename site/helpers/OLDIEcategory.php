<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Content Component Category Tree
 */
class JEM2Categories extends Categories
{
    public function __construct($options = array())
    {
        $options['table'] = '#__pja_categories';
        $options['extension'] = 'com_planjeagenda';
        parent::__construct($options);
    }
}
