<?php
declare(strict_types=1);

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class WaitingListService
{
    public function apply(
        object $event,
        object $register,
        object $row,
        int $status
        ): void {
            
            if ($event->maxplaces <= 0 || $status !== 1) {
                $row->status = $status;
                return;
            }
            
            if ($register->booked >= $event->maxplaces) {
                
                if (!$event->waitinglist) {
                    throw new \RuntimeException(
                        Text::_('COM_PLANJEAGENDA_ERROR_REGISTER_EVENT_IS_FULL')
                        );
                }
                
                $row->waiting = 1;
                
                return;
            }
            
            $row->status = 1;
    }
}
