<?php

namespace Snowdog\DevTest\Migration;

use Snowdog\DevTest\Core\Database;
use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;

class Version2
{
    /**
     * @var Database|\PDO
     */
    private $database;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var PageManager
     */
    private $pageManager;

    public function __construct(
        Database $database,
        UserManager $userManager,
        WebsiteManager $websiteManager,
        PageManager $pageManager
    ) {
        $this->database = $database;
        $this->userManager = $userManager;
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;
    }

    public function __invoke()
    {
        $this->createPageTable();
        $this->addPageData();
        $this->createVarnishesTable()
        $this->createVarnishToWebsiteTable();
    }

    private function createPageTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `pages` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `website_id` int(11) unsigned NOT NULL,
  `last_visited` DATETIME NOT NULL DEFAULT NOW(),
  PRIMARY KEY (`page_id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `page_website_fk` FOREIGN KEY (`website_id`) REFERENCES `websites` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function createVarnishesTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `varnishes` (
  `varnish_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`varnish_id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `varnish_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function createVarnishToWebsiteTable()
    {
      $createQuery = <<<SQL
CREATE TABLE `varnish_to_website` (
`website_id` int(11) unsigned NOT NULL,
`varnish_id` int(11) unsigned NOT NULL,
KEY `website_id` (`website_id`),
KEY `varnish_id` (`varnish_id`),
CONSTRAINT `website_warnish_fk` FOREIGN KEY (`varnish_id`) REFERENCES `varnishes` (`varnish_id`),
CONSTRAINT `varnish_website_fk` FOREIGN KEY (`website_id`) REFERENCES `websites` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function addPageData()
    {
        $testUser = $this->userManager->getByLogin('test');
        foreach($this->websiteManager->getAllByUser($testUser) as $website) {
            $this->pageManager->create($website, 'index.html');
            $this->pageManager->create($website, 'index.en.html');
            $this->pageManager->create($website, 'contact-us.html');
        }

        $exampleUser = $this->userManager->getByLogin('example');
        foreach($this->websiteManager->getAllByUser($exampleUser) as $website) {
            $this->pageManager->create($website, 'index.php');
            $this->pageManager->create($website, 'product.php');
            $this->pageManager->create($website, 'category.php');
        }

        $demoUser = $this->userManager->getByLogin('demo');
        foreach($this->websiteManager->getAllByUser($demoUser) as $website) {
            $this->pageManager->create($website, 'home.jsp');
            $this->pageManager->create($website, 'contact.jsp');
        }
    }
}
