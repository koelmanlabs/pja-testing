<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>
<div id="klevents" class="jem_myattendances<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->needLoginFirst):
    $uri = Uri::getInstance();
    $returnUrl = $uri->toString();
    $urlLogin = Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl)); ?>
    <div class="pja-login-cta">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="1.5" style="margin:0 auto 1rem;display:block;"><circle cx="12" cy="8" r="4"/><path d="M6 20v-1a6 6 0 0112 0v1"/></svg>
        <h2>Inloggen vereist</h2>
        <p>Log in om je aanmeldingen te bekijken.</p>
        <a href="<?php echo $urlLogin; ?>" class="pja-btn-green" style="display:inline-flex;width:auto;padding:.65rem 1.75rem;">Inloggen</a>
    </div>
<?php else: ?>
    <div class="pja-my-header">
        <h1 class="pja-page-title" style="margin:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:.4rem;color:#2e7d32;"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Mijn aanmeldingen
        </h1>
    </div>
    <div class="pja-event-table-wrap"><?php echo $this->loadTemplate('attendances'); ?></div>
<?php endif; ?>
</div>
