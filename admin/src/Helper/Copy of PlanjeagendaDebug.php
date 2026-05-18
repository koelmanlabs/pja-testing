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

use KoelmanLabs\Component\Planjeagenda\Administrator\Helper\PlanjeagendaDebug;


class PlanjeagendaDebug
{
    private static string $component = 'com_planjeagenda';
    // ... rest van je functies

    /**
     * Debug level log entry.
     */
    public static function log(string $label, $value = null): void
    {
        self::write($label, $value, 'debug');
    }

    /**
     * Info level log entry.
     */
    public static function info(string $label, $value = null): void
    {
        self::write($label, $value, 'info');
    }

    /**
     * Warning level log entry.
     */
    public static function warning(string $label, $value = null): void
    {
        self::write($label, $value, 'warning');
    }

    /**
     * Error level log entry.
     */
    public static function error(string $label, $value = null): void
    {
        self::write($label, $value, 'error');
    }

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
     * Leeg de log tabel.
     */
    public static function clear(): void
    {
        if (!self::isActive()) {
            return;
        }

        self::getLogger()::clear();
    }

    /**
     * Schrijf een entry via de DbLogger.
     */
    private static function write(string $label, $value, string $level): void
    {
        if (!self::isActive()) {
            return;
        }

        self::getLogger()::write($label, $value, $level, self::$component);
    }

    /**
     * Is debug logging actief voor dit request?
     */
    private static function isActive(): bool
    {
        return defined('PJA_DEBUG_ACTIVE') && PJA_DEBUG_ACTIVE === true;
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
