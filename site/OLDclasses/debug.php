<?php
/**
 * @package    Planjeagenda
 * PlanjeagendaDebug global stub for site-side use.
 * In production this is a no-op. Enable via debug plugin.
 */

defined('_JEXEC') or die;

if (!class_exists('PlanjeagendaDebug', false)):
class PlanjeagendaDebug
{
    public static function log($label, $value = null, $level = 'debug'): void
    {
        // No-op unless debug plugin is active
        if (defined('PJA_DEBUG_ACTIVE') && PJA_DEBUG_ACTIVE) {
            \Joomla\CMS\Log\Log::add(
                $label . (isset($value) ? ': ' . print_r($value, true) : ''),
                \Joomla\CMS\Log\Log::DEBUG,
                'com_planjeagenda'
            );
        }
    }

    public static function info($label, $value = null): void  { self::log($label, $value, 'info'); }
    public static function warning($label, $value = null): void { self::log($label, $value, 'warning'); }
    public static function error($label, $value = null): void { self::log($label, $value, 'error'); }
    public static function query($sql, $time = 0): void { self::log('query', $sql); }
}
endif;
