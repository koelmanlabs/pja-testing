<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
?>

<div id="j-main-container" class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Sectie: Extensie Informatie -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <span class="icon-info me-2"></span> Planjeagenda Details
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th width="30%" class="ps-3">Versie</th>
                                <td><?php echo $this->extInfo->version ?? 'Onbekend'; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Installatiedatum</th>
                                <td><?php echo $this->extInfo->creationDate ?? '-'; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Auteur</th>
                                <td><?php echo $this->extInfo->author ?? 'Koelman Labs'; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Maprechten (images)</th>
                                <td>
                                    <?php if (is_writable(JPATH_ROOT . '/images/com_planjeagenda')) : ?>
                                        <span class="badge bg-success">Schrijfbaar</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Niet schrijfbaar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


<!-- Sectie: Database Gezondheid -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-info text-white fw-bold">
        <span class="icon-database me-2"></span> Database Structuur Controle
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0 small">
            <thead>
                <tr>
                    <th class="ps-3">Tabel</th>
                    <th>Status</th>
                    <th>Opmerkingen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->dbStatus as $tableName => $info) : ?>
                    <tr>
                        <td class="ps-3 fw-bold"><?php echo $tableName; ?></td>
                        <td>
                            <?php if ($info['healthy']) : ?>
                                <span class="badge bg-success"><span class="icon-check me-1"></span> OK</span>
                            <?php else : ?>
                                <span class="badge bg-danger"><span class="icon-warning me-1"></span> Fout</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if (!$info['exists']) {
                                echo '<span class="text-danger">Tabel ontbreekt in database!</span>';
                            } elseif (!empty($info['missing'])) {
                                echo '<span class="text-warning">Missende kolommen: ' . implode(', ', $info['missing']) . '</span>';
                            } else {
                                echo '<span class="text-muted">Structuur is correct.</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
  
<?php 
    // Controleer of er fouten zijn in de database status
    $hasErrors = false;
    foreach ($this->dbStatus as $status) {
        if (!$status['healthy']) {
            $hasErrors = true;
            break;
        }
    }
    ?>

    <?php if ($hasErrors) : ?>
        <div class="card-footer bg-light text-center">
            <div class="alert alert-warning mb-2 small">
                <span class="icon-warning me-1"></span> 
                Er zijn inconsistenties gevonden in de database structuur.
            </div>
            <button type="button" class="btn btn-warning btn-sm fw-bold">
                <span class="icon-loop me-1"></span> Database herstellen (SQL Fix)
            </button>
        </div>
    <?php endif; ?>
</div>




            <!-- Sectie: Server Omgeving -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    <span class="icon-options me-2"></span> Systeemomgeving
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th width="30%" class="ps-3">PHP Versie</th>
                                <td><?php echo $this->sysInfo['php_version']; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Database</th>
                                <td><?php echo $this->sysInfo['db_type'] . ' ' . $this->sysInfo['db_version']; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Joomla! Versie</th>
                                <td><?php echo $this->sysInfo['joomla_version']; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Geheugenlimiet</th>
                                <td><?php echo $this->sysInfo['memory_limit']; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-3">Max. Upload</th>
                                <td><?php echo $this->sysInfo['upload_max']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar: Snelle acties -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Hulp nodig?</h5>
                    <p class="small text-muted">Heb je een probleem met de agenda? Kopieer de bovenstaande gegevens wanneer je contact opneemt met Koelman Labs.</p>
                    <a href="https://www.koelmanlabs.nl/docs/planjeagenda" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <span class="icon-question me-1"></span> Documentatie
                    </a>
                </div>
            </div>
            
            <div class="alert alert-info shadow-sm small">
                <h6 class="fw-bold">Tip:</h6>
                Zorg dat je PHP-versie altijd op 8.1 of hoger staat voor de beste prestaties en veiligheid van deze component.
            </div>
        </div>
    </div>
</div>
