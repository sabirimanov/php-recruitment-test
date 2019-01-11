<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\Varnish;
use Snowdog\DevTest\Model\VarnishManager;
use Snowdog\DevTest\Model\WebsiteManager;

class CreateVarnishLinkAction
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var VarnishManager
     */
    private $varnishManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    public function __construct(UserManager $userManager, VarnishManager $varnishManager, WebsiteManager $websiteManager)
    {
        $this->userManager = $userManager;
        $this->varnishManager = $varnishManager;
        $this->websiteManager = $websiteManager;
    }

    public function execute()
    {
        // TODO: add module logic here

	        if (!isset($_SESSION['login'])) {
	             header('Location: /login');
	             exit;
	        }
          
	        $is_checked = $_POST['isChecked'];
	        $varnish_id = $_POST['varnish'];
	        $website_id = $_POST['website'];

	        $this->message = '';
	        if(isset($is_checked) && !empty($varnish_id) && !empty($website_id)) {
	            if (isset($_SESSION['login'])) {
	                $logged_user = $this->userManager->getByLogin($_SESSION['login']);
	                if ($logged_user) {
	                    $website = $this->websiteManager->getById($website_id);
    	                $varnish = $this->varnishManager->getById($varnish_id);
	                    if ($is_checked) {
	                        if($this->varnishManager->link($varnish, $website)) {
	                            $this->message = 'Varnish was successfully added to website';
	                        }
	                    } else {
	                        if($this->varnishManager->unlink($varnish, $website)) {
	                            $this->message = 'Varnish was successfully removed from website';
	                        }
	                    }
	                }
	            }
	        } else {
	            $this->message = 'Error. Please, check data and try again';
	        }

	        echo $this->message;
    }
}
