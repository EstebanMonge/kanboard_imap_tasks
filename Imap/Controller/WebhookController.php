<?php

namespace Kanboard\Plugin\Mailgun\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\Imap\EmailHandler;

/**
 * Webhook Controller
 *
 * @package  mailgun
 * @author   Frederic Guillot
 */
class WebhookController extends BaseController
{
    /**
     * Handle Mailgun webhooks
     *
     * @access public
     */
    public function receiver()
    {
        $this->checkWebhookToken();

        $handler = new EmailHandler($this->container);
        $this->response->text($handler->receiveEmail($_POST) ? 'PARSED' : 'IGNORED');
    }
}
