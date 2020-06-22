<?php

namespace Kanboard\Plugin\Imap;

use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Mailgun Plugin
 *
 * @package  mailgun
 * @author   Frederic Guillot
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:config:integrations', 'imap:integration');
        $this->hook->on('template:layout:js', array('template' => 'plugins/Imap/Asset/integration.js'));
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginDescription()
    {
        return 'Imap Email Integration';
    }

    public function getPluginAuthor()
    {
        return 'Esteban Monge';
    }

    public function getPluginVersion()
    {
        return '0.0.2';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/EstebanMonge';
    }
}
