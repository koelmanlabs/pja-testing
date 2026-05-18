<?php

namespace KoelmanLabs\Component\Planjeagenda\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class EventsIcsModel extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        // FORCE deterministic export (NO SESSION)
        $this->setState('list.limit', $input->getInt('limit', 500));
        $this->setState('list.start', $input->getInt('start', 0));

        // FIXED ordering for ICS
        $this->setState('filter.orderby', [
            'a.dates ASC',
            'a.times ASC',
            'a.id ASC'
        ]);

        // no session filters in ICS → only request-based filters
        $this->setState('filter.filter_search', $input->getString('filter_search', ''));
        $this->setState('filter.filter_catid', $input->getInt('filter_catid', 0));

        $this->setState('params', $app->getParams());
    }

    /**
     * Reuse SAME query logic as HTML model
     */
    protected function getListQuery()
    {
        // reuse your existing HTML model logic
        $htmlModel = new EventslistModel();

        // copy state into HTML model
        foreach ($this->getState() as $k => $v) {
            $htmlModel->setState($k, $v);
        }

        return $htmlModel->getListQuery();
    }
}