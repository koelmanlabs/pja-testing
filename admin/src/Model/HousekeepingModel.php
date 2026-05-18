<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Client\ClientHelper;
use Joomla\Filesystem\Path;
use Joomla\CMS\Filter\InputFilter;

class HousekeepingModel extends ListModel
{
    const EVENTS = 1;
    const VENUES = 2;
    const CATEGORIES = 3;

    /**
     * Map logical name to folder and db names
     * @var stdClass
     */
    private $map = null;

    /**
     * Get the type map (lazy init)
     */
    protected function getMap(): array
    {
        if ($this->map === null) {
            $this->map = [
                self::EVENTS     => ["folder" => "events",     "table" => "events",     "field" => "datimage"],
                self::VENUES     => ["folder" => "venues",     "table" => "venues",     "field" => "locimage"],
                self::CATEGORIES => ["folder" => "categories", "table" => "categories", "field" => "image"],
            ];
        }
        return $this->map;
    }

    /**
     * Method to delete the images
     *
     * @access public
     * @return int
     */
    public function delete($type)
    {
        // Set FTP credentials, if given
        // jimport() removed: Joomla 6 uses PSR-4 autoloading. Add 'use' statement instead.
        ClientHelper::setCredentialsFromRequest('ftp');

        // Get some data from the request
        $images    = $this->getImages($type);
        $folder = $this->getMap()[$type]['folder'];

        $count = count($images);
        $fail = 0;

        foreach ($images as $image)
        {
            if ($image !== InputFilter::getInstance()->clean($image, 'path')) {
                Factory::getApplication()->enqueueMessage(Text::_('com_planjeagenda_UNABLE_TO_DELETE').' '.htmlspecialchars($image, ENT_COMPAT, 'UTF-8'), 'warning');
                $fail++;
                continue;
            }

            $fullPath = Path::clean(JPATH_SITE.'/images/klevents/'.$folder.'/'.$image);
            $fullPaththumb = Path::clean(JPATH_SITE.'/images/klevents/'.$folder.'/small/'.$image);

            if (is_file($fullPath)) {
                File::delete($fullPath);
                if (File::exists($fullPaththumb)) {
                    File::delete($fullPaththumb);
                }
            }
        }

        $deleted = $count - $fail;

        return $deleted;
    }

    /**
     * Deletes zombie cats_event_relations with no existing event or category
     * @return boolean
     */
    public function cleanupCatsEventRelations()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery('DELETE cat FROM #__pja_cats_event_relations as cat'
                .' LEFT OUTER JOIN #__pja_events as e ON cat.itemid = e.id'
                .' WHERE e.id IS NULL');
        $db->execute();

        $db->setQuery('DELETE cat FROM #__pja_cats_event_relations as cat'
                .' LEFT OUTER JOIN #__pja_categories as c ON cat.catid = c.id'
                .' WHERE c.id IS NULL');
        $db->execute();

        return true;
    }

    /**
     * Truncates JEM tables with exception of settings table
     */
    public function truncateAllData()
    {
        $result = true;
        $tables = array('attachments', 'categories', 'cats_event_relations', 'events', 'groupmembers', 'groups', 'register', 'venues');
        $db = Factory::getContainer()->get('DatabaseDriver');

        foreach ($tables as $table) {
            $db->setQuery('TRUNCATE #__pja_'.$table);

            if ($db->execute() === false) {
                // report but continue
                \PlanjeagendaHelper::addLogEntry('Error truncating #__pja_'.$table, __METHOD__, Log::ERROR);
                $result = false;
            }
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $categoryTable = new \KoelmanLabs\Component\Planjeagenda\Administrator\Table\CategoryTable($db);
        $categoryTable->addRoot();

        return $result;
    }

    /**
     * Method to count the cat_relations table
     *
     * @access public
     * @return int
     */
    public function getCountcats()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(array('*'));
        $query->from('#__pja_cats_event_relations');
        $db->setQuery($query);
        $db->execute();

        $total = $db->loadObjectList();

        return is_array($total) ? count($total) : 0;
    }

    /**
     * Method to determine the images to delete
     *
     * @access private
     * @return array
     */
    private function getImages($type)
    {
        $images = array_diff($this->getAvailable($type), $this->getAssigned($type));

        return $images;
    }

    /**
     * Method to determine the assigned images
     *
     * @access private
     * @return array
     */
    private function getAssigned($type)
    {
        $query = 'SELECT '.$this->getMap()[$type]['field'].' FROM #__pja_'.$this->getMap()[$type]['table'];

        $this->_db->setQuery($query);
        $assigned = $this->_db->loadColumn();

        return $assigned;
    }

    /**
     * Method to determine the unassigned images
     *
     * @access private
     * @return array
     */
    private function getAvailable($type)
    {
        // Initialize variables
        $basePath = JPATH_SITE.'/images/klevents/'.$this->getMap()[$type]['folder'];

        $images = array ();

        // Get the list of files and folders from the given folder
        $fileList = Folder::files($basePath);

        // Iterate over the files if they exist
        if ($fileList !== false) {
            foreach ($fileList as $file)
            {
                if (is_file($basePath.'/'.$file) && substr($file, 0, 1) != '.') {
                    $images[] = $file;
                }
            }
        }

        return $images;
    }
}
