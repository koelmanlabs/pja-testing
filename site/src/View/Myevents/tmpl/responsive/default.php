<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
?>
<div id="klevents" class="jem_myevents<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->needLoginFirst):
    $uri = Uri::getInstance();
    $returnUrl = $uri->toString();
    $urlLogin = Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl)); ?>
    <div class="pja-login-cta">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="1.5" style="margin:0 auto 1rem;display:block;"><circle cx="12" cy="8" r="4"/><path d="M6 20v-1a6 6 0 0112 0v1"/></svg>
        <h2>Inloggen vereist</h2>
        <p>Log in om je activiteiten te beheren.</p>
        <a href="<?php echo $urlLogin; ?>" class="pja-btn-green" style="display:inline-flex;width:auto;padding:.65rem 1.75rem;">Inloggen</a>
    </div>
<?php else: ?>
    <div class="pja-my-header">
        <h1 class="pja-page-title" style="margin:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:.4rem;color:#2e7d32;"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            Mijn activiteiten
        </h1>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=editevent&a_id=0'); ?>"
           class="pja-btn-green" style="width:auto;padding:.5rem 1.1rem;font-size:.85rem;">
            <svg width="12" height="12" viewBox="0 0 448 512" fill="currentColor"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
            Nieuwe activiteit
        </a>
    </div>
    <div class="pja-event-table-wrap"><?php echo $this->loadTemplate('events'); ?></div>
<?php endif; ?>
</div>
