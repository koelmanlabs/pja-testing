<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>
<div id="klevents" class="jem_myvenues<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->needLoginFirst):
    $uri = Uri::getInstance();
    $returnUrl = $uri->toString();
    $urlLogin = Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl)); ?>
    <div class="pja-login-cta">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="1.5" style="margin:0 auto 1rem;display:block;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        <h2>Inloggen vereist</h2>
        <p>Log in om je locaties te beheren.</p>
        <a href="<?php echo $urlLogin; ?>" class="pja-btn-green" style="display:inline-flex;width:auto;padding:.65rem 1.75rem;">Inloggen</a>
    </div>
<?php else: ?>
    <div class="pja-my-header">
        <h1 class="pja-page-title" style="margin:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:.4rem;color:#2e7d32;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            Mijn locaties
        </h1>
        <a href="<?php echo Route::_('index.php?option=com_planjeagenda&view=editvenue&a_id=0'); ?>"
           class="pja-btn-green" style="width:auto;padding:.5rem 1.1rem;font-size:.85rem;">
            <svg width="12" height="12" viewBox="0 0 448 512" fill="currentColor"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
            Nieuwe locatie
        </a>
    </div>
    <?php echo $this->loadTemplate('venues'); ?>
<?php endif; ?>
</div>
