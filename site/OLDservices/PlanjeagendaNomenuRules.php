<?php
/**
 * @package    Planjeagenda
 * @copyright  (C) 2026 Koelman Labs
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 *
 * SEF routing rules for URLs without a dedicated menu item.
 *
 * BUILD: converts query vars  → URL segments (alias-only, no numeric ID)
 * PARSE: converts URL segments → query vars  (looks up ID from alias)
 *
 * URL patterns produced:
 *   event    → /event/mijn-activiteit
 *   venue    → /venue/mijn-locatie
 *   category → /category/mijn-categorie
 *   day      → /day/2026-05-13
 *   attendees→ /attendees/5
 *   other    → /view-name
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Factory;

class PlanjeagendaNomenuRules implements RulesInterface
{
    protected RouterView $router;

    public function __construct(RouterView $router)
    {
        $this->router = $router;
    }

    // ── Preprocess ────────────────────────────────────────────────────────
    // Normalise Itemid to scalar before StandardRules/MenuRules process it.

    public function preprocess(&$query): void
    {
        if (isset($query['Itemid'])) {
            $v = $query['Itemid'];
            $query['Itemid'] = is_array($v) ? (int) array_values($v)[0] : (int) $v;
        }
    }

    // ── Build ─────────────────────────────────────────────────────────────
    // Called by Joomla's router when generating a URL.
    // Converts query vars into path segments.
    // Input:  $query = ['option'=>'com_planjeagenda','view'=>'event','id'=>'5:mijn-activiteit']
    // Output: $segments = ['event','mijn-activiteit']  (numeric ID stripped)

    public function build(&$query, &$segments): void
    {
        if (!isset($query['view'])) return;

        $view = $query['view'];
        $segments[] = $view;
        unset($query['view'], $query['tmpl']);

        // Views that carry an id segment
        $viewsWithId = ['event','venue','category','categories','attendees','day','editevent','editvenue'];

        if (isset($query['id']) && in_array($view, $viewsWithId, true)) {
            $id = (string) $query['id'];

            // Always use numeric ID as segment for reliable parsing.
            // Extract the numeric part from formats like "5:alias" or "5"
            if (strpos($id, ':') !== false) {
                $numericId = (int) explode(':', $id)[0];
                $alias     = substr($id, strpos($id, ':') + 1);
                // Use alias if available, numeric ID as fallback
                $segments[] = $alias ?: $numericId;
            } else {
                // Plain numeric or plain alias — keep as-is
                $segments[] = $id;
            }
            unset($query['id']);
        }

        // Remove Itemid so it doesn't appear in the query string
        unset($query['Itemid']);
    }

    // ── Parse ─────────────────────────────────────────────────────────────
    // Called by Joomla's router when parsing an incoming URL.
    // Converts path segments back into query vars.
    // Input:  $segments = ['event','mijn-activiteit']
    // Output: $vars = ['view'=>'event','id'=>5]

    public function parse(&$segments, &$vars): void
    {
        if (empty($segments)) return;

        $view = array_shift($segments);
        $vars['view'] = $view;

        if (empty($segments)) return;

        $slug = array_shift($segments);

        switch ($view) {
            case 'event':
                // If slug is numeric, use it directly — no DB lookup needed
                $vars['id'] = is_numeric($slug)
                    ? (int) $slug
                    : $this->resolveEventId($slug);
                break;

            case 'venue':
                $vars['id'] = $this->resolveVenueId($slug);
                break;

            case 'category':
            case 'categories':
                $vars['id'] = $this->resolveCategoryId($slug);
                break;

            case 'attendees':
            case 'day':
            case 'editevent':
            case 'editvenue':
                // These pass numeric IDs or date strings directly
                $vars['id'] = $slug;
                break;

            default:
                // Unknown view with extra segment — put it back
                array_unshift($segments, $slug);
                break;
        }
    }

    // ── ID lookups ────────────────────────────────────────────────────────
    // Each resolver tries the alias first (SEF URL), then falls back to
    // treating the segment as a numeric ID (legacy / direct links).

    private function resolveEventId(string $slug): int
    {
        // Already numeric
        if (is_numeric($slug)) return (int) $slug;

        // id:alias format
        if (strpos($slug, ':') !== false) {
            return (int) explode(':', $slug)[0];
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $q  = $db->getQuery(true)->select('id')->from('#__pja_events');

            // Try exact alias match first
            $db->setQuery((clone $q)->where('alias = ' . $db->quote($slug))->setLimit(1));
            $id = (int) $db->loadResult();
            if ($id) return $id;

            // Try title match as fallback (slug may be generated from title)
            $titleSlug = str_replace('-', ' ', $slug);
            $db->setQuery((clone $q)->where('LOWER(title) = ' . $db->quote(strtolower($slug)))->setLimit(1));
            $id = (int) $db->loadResult();
            if ($id) return $id;

            // Try title with hyphens replaced by spaces
            $db->setQuery((clone $q)->where('LOWER(title) = ' . $db->quote(strtolower($titleSlug)))->setLimit(1));
            return (int) ($db->loadResult() ?: 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function resolveVenueId(string $slug): int
    {
        if (is_numeric($slug)) return (int) $slug;

        if (strpos($slug, ':') !== false) {
            return (int) explode(':', $slug)[0];
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__pja_venues')
                    ->where('alias = ' . $db->quote($slug))
                    ->setLimit(1)
            );
            return (int) ($db->loadResult() ?: 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function resolveCategoryId(string $slug): int
    {
        if (is_numeric($slug)) return (int) $slug;

        if (strpos($slug, ':') !== false) {
            return (int) explode(':', $slug)[0];
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                $db->getQuery(true)
                    ->select('id')
                    ->from('#__pja_categories')
                    ->where('alias = ' . $db->quote($slug))
                    ->setLimit(1)
            );
            return (int) ($db->loadResult() ?: 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
