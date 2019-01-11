<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\VarnishManager;

class CreateVarnishAction
{
    /**
     * @var VarnishManager
     */
    private $varnishManager;

    public function __construct(UserManager $userManager, VarnishManager $varnishManager)
    {
        $this->userManager = $userManager;
        $this->varnishManager = $varnishManager;
    }

    public function execute()
    {
        $ip = $_POST['ip'];

        // TODO - add module logic here

        if(!empty($ip)) {
            if (isset($_SESSION['login'])) {
                $logged_user = $this->userManager->getByLogin($_SESSION['login']);

                if ($logged_user) {
                    if ($this->varnishManager->create($logged_user, $ip)) {
                        $_SESSION['flash'] = 'Varnish IP ' . $ip . ' was successfully added';
                    }
                }
            }
        } else {
            $_SESSION['flash'] = 'Error: Varnish IP cannot be empty';
        }

        header('Location: /varnishes');
    }
}
