<?php
/**
 * Event print layout — clean, stripped version for printing
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$params      = $this->item->params;
$app         = Factory::getApplication();
$jemsettings = \PlanjeagendaHelper::config();

// Format date
$dateStr = \PlanjeagendaOutput::formatLongDateTime(
    $this->item->dates, $this->item->times,
    $this->item->enddates, $this->item->endtimes
);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($this->item->title); ?> — Planjeagenda</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 14px; color: #1a2e5a;
            max-width: 700px; margin: 2rem auto; padding: 0 1.5rem;
        }
        .print-header {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2px solid #2e7d32; padding-bottom: .75rem;
            margin-bottom: 1.5rem;
        }
        .print-logo { font-size: 1.2rem; font-weight: 800; color: #1a2e5a; }
        .print-logo span { color: #2e7d32; }
        .print-date-printed { font-size: .75rem; color: #9ca3af; }
        h1 {
            font-size: 1.6rem; font-weight: 800;
            color: #1a2e5a; margin-bottom: 1rem;
            letter-spacing: -.03em;
        }
        .accent { height: 3px; background: #2e7d32; border-radius: 3px;
                  width: 48px; margin-bottom: 1.25rem; }
        .meta-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: .5rem 1.5rem; margin-bottom: 1.5rem;
            background: #f8fafd; border: 1px solid #e0e8f0;
            border-radius: 10px; padding: 1rem;
        }
        .meta-item { }
        .meta-label {
            font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em;
            color: #9ca3af; margin-bottom: .15rem;
        }
        .meta-value { font-size: .9rem; font-weight: 600; color: #1a2e5a; }
        .section-title {
            font-size: .85rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .06em;
            color: #6b7280; margin: 1.5rem 0 .75rem;
            border-top: 1px solid #e0e8f0; padding-top: 1rem;
        }
        .description { font-size: .9rem; line-height: 1.7; color: #374151; }
        .description h1, .description h2, .description h3 { color: #1a2e5a; margin: 1rem 0 .5rem; }
        .description p { margin-bottom: .75rem; }
        .venue-card {
            background: #f8fafd; border: 1px solid #e0e8f0;
            border-radius: 8px; padding: .85rem; margin-top: .5rem;
        }
        .venue-name { font-weight: 700; margin-bottom: .25rem; }
        .venue-addr { font-size: .83rem; color: #6b7280; line-height: 1.6; }
        .print-footer {
            margin-top: 2rem; padding-top: .75rem;
            border-top: 1px solid #e0e8f0;
            font-size: .72rem; color: #9ca3af;
            display: flex; justify-content: space-between;
        }
        .cat-badge {
            display: inline-block; padding: .2rem .7rem;
            border-radius: 20px; font-size: .72rem; font-weight: 700;
            color: #fff; margin-bottom: 1rem;
        }
        @media print {
            body { margin: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="print-header">
    <div class="print-logo">Plan<span>je</span>agenda</div>
    <div class="print-date-printed">Afgedrukt op <?php echo date('d-m-Y H:i'); ?></div>
</div>

<?php if (!empty($this->item->catname)): ?>
<span class="cat-badge" style="background:#2e7d32">
    <?php echo htmlspecialchars($this->item->catname); ?>
</span>
<?php endif; ?>

<h1><?php echo htmlspecialchars($this->item->title); ?></h1>
<div class="accent"></div>

<!-- Meta info grid -->
<div class="meta-grid">
    <?php if ($dateStr): ?>
    <div class="meta-item">
        <div class="meta-label">Datum &amp; tijd</div>
        <div class="meta-value"><?php echo $dateStr; ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->item->venue)): ?>
    <div class="meta-item">
        <div class="meta-label">Locatie</div>
        <div class="meta-value">
            <?php echo htmlspecialchars($this->item->venue);
            if ($this->item->city) echo ', ' . htmlspecialchars($this->item->city); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->categories)): ?>
    <div class="meta-item">
        <div class="meta-label">Categorie</div>
        <div class="meta-value">
            <?php foreach ((array)$this->categories as $i => $cat):
                echo ($i > 0 ? ', ' : '') . htmlspecialchars($cat->catname);
            endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($params->get('event_show_author') && !empty($this->item->author)): ?>
    <div class="meta-item">
        <div class="meta-label">Geplaatst door</div>
        <div class="meta-value"><?php echo htmlspecialchars($this->item->created_by_alias ?: $this->item->author); ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Description -->
<?php
$bodyText = '';
if ($params->get('event_show_description', '1')) {
    if (!$params->get('event_show_intro') && $this->item->fulltext) {
        $bodyText = $this->item->fulltext;
    } else {
        $bodyText = $this->item->text ?? $this->item->introtext ?? '';
    }
}
if ($bodyText && $bodyText !== '<br>'): ?>
<div class="section-title">Beschrijving</div>
<div class="description"><?php echo $bodyText; ?></div>
<?php endif; ?>

<!-- Venue address -->
<?php if (!empty($this->item->locid) && !empty($this->item->venue) && $params->get('event_show_venue', '1') && $this->item->user_has_access_venue): ?>
<div class="section-title">Locatiedetails</div>
<div class="venue-card">
    <div class="venue-name"><?php echo htmlspecialchars($this->item->venue); ?></div>
    <div class="venue-addr">
        <?php if ($this->item->street) echo htmlspecialchars($this->item->street) . '<br>'; ?>
        <?php if ($this->item->postalCode || $this->item->city) echo htmlspecialchars(trim($this->item->postalCode . ' ' . $this->item->city)) . '<br>'; ?>
        <?php if ($this->item->country) echo htmlspecialchars($this->item->country); ?>
    </div>
</div>
<?php endif; ?>

<!-- Contact -->
<?php if ($params->get('event_show_contact') && !empty($this->item->conname)): ?>
<div class="section-title">Contact</div>
<div><?php echo htmlspecialchars($this->item->conname); ?>
<?php if ($this->item->contelephone): ?>
 · <a href="tel:<?php echo htmlspecialchars($this->item->contelephone); ?>"><?php echo htmlspecialchars($this->item->contelephone); ?></a>
<?php endif; ?></div>
<?php endif; ?>

<div class="print-footer">
    <span>planjeagenda.nl</span>
    <span><?php echo htmlspecialchars(\Joomla\CMS\Uri\Uri::getInstance()->toString()); ?></span>
</div>

</body>
</html>
<?php
// Prevent any Joomla template wrapping
$app->getDocument()->setMimeEncoding('text/html');
