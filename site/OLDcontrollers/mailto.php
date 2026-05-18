<?php
/**
 * @package    KLEvents
 * @copyright  (C) 2026 Koelman Labs
 * @copyright  (C) 2005-2009 Christoph Lukes
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\String\PunycodeHelper;

require_once (JPATH_COMPONENT_SITE.'/classes/controller.form.class.php');

/**
 * Event Controller
 */
class PlanjeagendaControllerMailto extends PlanjeagendaControllerForm
{
    // protected $view_item = 'editevent';
    // protected $view_list = 'eventslist';
    protected $_id = 0;


    public function getModel($name = 'mailto', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function save($key = NULL, $urlVar = NULL){
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app        = Factory::getApplication();
        $model      = $this->getModel('mailto');
        $data       = $model->getData();
        $uri        = Uri::getInstance();
        $form       = $model->getForm();
		
        $input = $app->getInput();
        $post_link = $input->post->get('link', '', 'raw');
        $currentUri = $uri->toString() . '&link=' . urlencode($post_link);

        if (!$form)
        {
            $app->enqueueMessage($model->getError(), 'error');

            return false;
        }

        if (!$model->validate($form, $data))
        {
            $errors = $model->getErrors();

            foreach ($errors as $error)
            {
                $errorMessage = $error;

                if ($error instanceof Exception)
                {
                    $errorMessage = $error->getMessage();
                }

                $app->enqueueMessage($errorMessage, 'error');
            }

            $this->setRedirect($currentUri);
        }

        $headers = array (
            'Content-Type:',
            'MIME-Version:',
            'Content-Transfer-Encoding:',
            'bcc:',
            'cc:'
        );
        foreach ($data as $key => $value)
        {
            foreach ($headers as $header)
            {
                if (is_string($value) && strpos($value, $header) !== false)
                {
                    $app->enqueueMessage(403, 'error');
                }
            }
        }

        unset($headers, $fields);

        $siteName = $app->get('sitename');
        $link = PlanjeagendaMailtoHelper::validateHash($input->post->get('link', '', 'raw'));

        // Verify that this is a local link
        if (!$link || !Uri::isInternal($link))
        {
            // Non-local url...
            $app->enqueueMessage( Text::_('com_planjeagenda_MAILTO_EMAIL_NOT_SENT'), 'error');
            $this->setRedirect($currentUri);
        }

        $subject_default = Text::sprintf('com_planjeagenda_MAILTO_SENT_BY', $data['sender']);
        $subject         = $data['subject'] !== '' ? $data['subject'] : $subject_default;
        $error = false;

        if (!$data['emailto'] || !\Joomla\CMS\Mail\MailHelper::isEmailAddress($data['emailto']))
        {
            $error = Text::sprintf('com_planjeagenda_MAILTO_EMAIL_INVALID', $data['emailto']);

            $app->enqueueMessage( $error, 'error');
        }

        // Check for a valid from address
        if (!$data['emailfrom'] || !\Joomla\CMS\Mail\MailHelper::isEmailAddress($data['emailfrom']))
        {
            $error = Text::sprintf('com_planjeagenda_MAILTO_EMAIL_INVALID', $data['emailfrom']);

            $app->enqueueMessage( $error, 'error');
        }

        if ($error)
        {
            return $this->setRedirect($currentUri);
            return false;
        }
        $msg  = Text::_('com_planjeagenda_MAILTO_EMAIL_MSG');
        $body = sprintf($msg, $siteName, $data['sender'], $data['emailfrom'], $link);

        // To send we need to use punycode.
        $data['emailfrom'] = PunycodeHelper::emailToPunycode($data['emailfrom']);
        $data['emailfrom'] = \Joomla\CMS\Mail\MailHelper::cleanAddress($data['emailfrom']);
        $data['emailto']   = PunycodeHelper::emailToPunycode($data['emailto']);
        $from = array($data['emailfrom'], $data['sender']);

        // Clean the email data
        $subject = \Joomla\CMS\Mail\MailHelper::cleanSubject($subject);
        $body    = \Joomla\CMS\Mail\MailHelper::cleanBody($body);

        //--------------start new code ------------
        $mailer = Factory::getMailer();
        $mailer->setSender($from);
        $mailer->addRecipient($data['emailto']);
        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->isHTML();
        try{
            if (!$mailer->send())
            {
                $app->enqueueMessage( Text::_('com_planjeagenda_MAILTO_EMAIL_NOT_SENT'), 'error');
                $this->setRedirect($currentUri);
                return false;
            }
        }catch(Exception $e){
            $app->enqueueMessage($e->getMessage(), 'notice');
            $this->setRedirect($currentUri);
            return false;
        }
        $currentUri .= '&layout=sent';
        $this->setRedirect($currentUri);
        //--------------end new code ------------

    }

}
