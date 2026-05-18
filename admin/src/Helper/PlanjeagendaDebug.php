<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 KoelmanLabs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * PlanjeagendaDebug — thin wrapper rondom de PjaDebug plugin DbLogger.
 *
 * Als de plugin actief is (PJA_DEBUG_ACTIVE constant gezet) schrijft deze
 * klasse naar de database via DbLogger. Anders doet hij niets.
 *
 * Gebruik:
 *   // debug log
 *   PlanjeagendaDebug::info('label', $waarde);
 *   PlanjeagendaDebug::warning('label', $waarde);
 *   PlanjeagendaDebug::error('label', $waarde);
 *   PlanjeagendaDebug::query('SELECT ...', 12.5);
 */

namespace KoelmanLabs\Component\Planjeagenda\Administrator\Helper; 
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaDebug;
use Joomla\CMS\Component\ComponentHelper;

class PlanjeagendaDebug
{
    private static string $component = 'com_planjeagenda';
    
    public static function log(string $label, $value = null): void    { self::write($label, $value, 'debug'); }
    public static function info(string $label, $value = null): void   { self::write($label, $value, 'info'); }
    public static function warning(string $label, $value = null): void{ self::write($label, $value, 'warning'); }
    public static function error(string $label, $value = null): void  { self::write($label, $value, 'error'); }

    
    /**
     * Log een SQL query apart (voor query analyse).
     *
     * @param  string  $sql      De SQL query
     * @param  float   $time_ms  Uitvoeringstijd in milliseconden
     */
    public static function query(string $sql, float $time_ms = 0): void
    {
        if (!self::isActive()) {
            return;
        }

        self::getLogger()::write(
            'Query',
            'tijd: ' . $time_ms . 'ms',
            'debug',
            self::$component,
            '',
            0,
            $sql
        );
    }

    
    /**
     * Schrijf de entry direct naar de database
     */
    private static function write(string $label, $value, string $level): void
    {
        if (!self::isActive()) {
            return;
        }
        
        $db   = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $app  = Factory::getApplication();
        $input = $app->input;
        
        // Waarde/Message voorbereiden
        $message = (is_array($value) || is_object($value)) ? json_encode($value) : (string)$value;
        
        // Haal debug backtrace op voor file en line info
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller    = $backtrace[1]; // De plek waar de log() functie werd aangeroepen
        
        $obj = new \stdClass();
        $obj->created_at  = (new \Joomla\CMS\Date\Date())->toSql();
        $obj->level       = $level;
        $obj->component   = self::$component;
        $obj->label       = $label;
        $obj->message     = $message;
        $obj->file        = $caller['file'] ?? '';
        $obj->line        = $caller['line'] ?? 0;
        $obj->memory_kb   = round(memory_get_usage() / 1024);
        $obj->time_ms     = round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000, 2);
        $obj->session_id  = Factory::getSession()->getId();
        $obj->request_url = $_SERVER['REQUEST_URI'] ?? '';
        // $obj->query    = ''; // Optioneel vullen via de query() methode
        
        try {
            $db->insertObject('#__pja_debug_log', $obj);
        } catch (\Exception $e) {
            // Silent fail
        }
    }
    
    
    /**
     * Leeg de log tabel (aanroepen vanuit cleanup)
     */
    public static function clear($days = 7): void
    {
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $date = new Date("-{$days} days");
        
        $query = $db->getQuery(true)
        ->delete($db->quoteName('#__pja_debug_logs'))
        ->where($db->quoteName('log_date') . ' < ' . $db->quote($date->toSql()));
        
        $db->setQuery($query);
        $db->execute();
    }
    
    
    

    private static function isActive(): bool
    {
        // Gebruik ComponentHelper om de parameters van com_planjeagenda op te halen
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_planjeagenda');
        
        return (bool) $params->get('debug_logging', 0);
    }

    /**
     * Haal de DbLogger class op.
     *
     * @return string Volledig gekwalificeerde klassenaam
     */
    private static function getLogger(): string
    {
        // @todo Fix
        // return \Koelmanlabs\Plugin\System\Pjadebug\Logger\DbLogger::class;
    }
}
