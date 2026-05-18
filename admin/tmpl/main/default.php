<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Styling voor de professionele Atum-look
?>
<style>
    .dashboard-card { border: 1px solid #ced4da !important; transition: all 0.2s; }
    .dashboard-card:hover { background-color: #f8f9fa !important; border-color: #0d6efd !important; transform: translateY(-2px); }
    .tracking-wider { letter-spacing: 0.05rem; }
    .x-small { font-size: 0.75rem; }
    .bg-success-soft { background-color: #e8f5e9; color: #2e7d32; }
    .bg-warning-soft { background-color: #fff3e0; color: #ef6c00; }
</style>

<div id="j-main-container" class="container-fluid py-4">
    <div class="row">
        
        <!-- LINKERKANT: Acties & Beheer -->
        <div class="col-lg-8 col-xl-9">
            
            <!-- Sectie 1: Dagelijks Beheer -->
            <div class="mb-5">
                <h5 class="mb-4 text-dark fw-bold border-bottom pb-2">
                    <span class="icon-calendar me-2 text-primary"></span><?php echo Text::_('COM_PLANJEAGENDA_MANAGEMENT'); ?>
                </h5>
                <div class="row g-3">
                    <?php
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=events', 'calendar', 'Events');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&task=event.add', 'plus', 'Nieuw Event');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=venues', 'location', 'Locaties');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&task=venue.add', 'plus', 'Locatie +');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=categories', 'folder', 'Categorieën');
                    ?>
                </div>
            </div>

            <!-- Sectie 2: Systeem & Onderhoud -->
            <div class="mb-5">
                <h5 class="mb-4 text-dark fw-bold border-bottom pb-2">
                    <span class="icon-wrench me-2 text-primary"></span><?php echo Text::_('COM_PLANJEAGENDA_SYSTEM_MAINTENANCE'); ?>
                </h5>
                <div class="row g-3">
                    <?php
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=housekeeping', 'cleanup', 'Opschonen');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=logs', 'list-2', 'Systeemlog');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=settings', 'options', 'Instellingen');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=updatecheck', 'loop', 'Update Check');
                        echo $this->quickiconButton('index.php?option=com_planjeagenda&view=info', 'info', 'Systeeminformatie');
                    ?>
                </div>
            </div>
        </div>

        <!-- RECHTERKANT: Info & Status -->
        <div class="col-lg-4 col-xl-3">
            
            <!-- Statistieken Kaart -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <span class="icon-chart me-2"></span> Database Status
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div><span class="fw-bold d-block">Totaal Events</span><small class="text-muted">In de database</small></div>
                            <span class="h4 mb-0 fw-bold"><?php echo $this->events->total; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span>Gepubliceerd</span>
                            <span class="badge bg-success-soft rounded-pill"><?php echo $this->events->published; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <span>Verwijderd</span>
                            <span class="badge bg-danger rounded-pill"><?php echo $this->events->trashed; ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recente Activiteit (Compact in Sidebar) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light fw-bold small text-uppercase">Recente Events</div>
                <div class="list-group list-group-flush x-small">
                    <?php if (!empty($this->latestEvents)) : ?>
                        <?php foreach ($this->latestEvents as $item) : ?>
                            <a href="index.php?option=com_planjeagenda&task=event.edit&id=<?php echo $item->id; ?>" class="list-group-item list-group-item-action py-3">
                                <div class="fw-bold text-dark text-truncate"><?php echo $item->title; ?></div>
                                <div class="text-muted"><?php echo HTMLHelper::_('date', $item->created, 'd M Y'); ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="list-group-item text-muted text-center py-3">Geen recente items</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Support / Donatie (Hersteld uit eerdere versie) -->
            <div class="card bg-primary text-white border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    <span class="icon-heart fs-1"></span>
                </div>
                <h5 class="fw-bold mb-2">Support Koelman Labs</h5>
                <p class="small mb-3 opacity-75">Help ons Planjeagenda te blijven verbeteren met een vrijwillige bijdrage.</p>
                <a href="https://www.koelmanlabs.nl/project/donate" target="_blank" class="btn btn-light btn-sm w-100 fw-bold text-primary">
                    <span class="icon-heart me-1"></span> DONEER NU
                </a>
                <div class="mt-3 x-small opacity-50 text-uppercase tracking-wider">
                    Koelman Labs &copy; 2026
                </div>
            </div>

        </div>
    </div>
</div>