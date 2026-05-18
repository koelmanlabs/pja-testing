<?php
namespace KoelmanLabs\Component\Planjeagenda\Site\Classes;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Modernized Category Class for Planjeagenda
 */
class Categories
{
    public int $id;
    protected $db;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Fetches the breadcrumb path using Nested Sets (lft/rgt)
     * Optimized for PHP 8.3 performance.
     */
    public function getPath(): array
    {
        if ($this->id <= 0) return [];

        $user = Factory::getApplication()->getIdentity();
        $levels = implode(',', $user->getAuthorisedViewLevels());

        $query = $this->db->getQuery(true)
            ->select('parent.id, parent.catname')
            ->select($this->db->qn('parent.id') . ' || ' . $this->db->q(':') . ' || ' . $this->db->qn('parent.alias') . ' AS slug')
            ->from('#__pja_categories AS node')
            ->join('INNER', '#__pja_categories AS parent ON node.lft BETWEEN parent.lft AND parent.rgt')
            ->where('node.id = ' . $this->id)
            ->where('parent.published = 1')
            ->where('parent.access IN (' . $levels . ')')
            ->order('parent.lft ASC');

        return $this->db->setQuery($query)->loadColumn(2) ?: [];
    }
}