<?php

namespace Snowdog\DevTest\Model;

use Snowdog\DevTest\Core\Database;

class VarnishManager
{

    /**
     * @var Database|\PDO
     */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getAllByUser(User $user)
    {
        // TODO: add logic here
        $userId = $user->getUserId();
        $query = $this->database->prepare('SELECT * FROM varnishes WHERE user_id = :user');
        $query->bindParam(':user', $userId);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_CLASS, Varnish::class);
    }

    public function getWebsites(Varnish $varnish)
    {
        // TODO: add logic here
        $varnishId = $varnish->getVarnishId();
        $query = $this->database->prepare('SELECT * FROM websites INNER JOIN varnish_to_website ON varnish_to_website.website_id = websites.website_id  WHERE varnish_id = :varnish');
        $query->bindParam(':varnish', $varnishId);
        $query->execute();
	      return $query->fetchAll(\PDO::FETCH_CLASS, Website::class);
    }

    public function getByWebsite(Website $website)
    {
        // TODO: add logic here
        $websiteId = $website->getWebsiteId();
        $query = $this->database->prepare('SELECT * FROM varnishes INNER JOIN varnish_to_website ON varnish_to_website.varnish_id = varnishes.varnish_id WHERE website_id = :website');
        $query->bindParam(':website', $websiteId);
        $query->execute();
	      return $query->fetchAll(\PDO::FETCH_CLASS, Varnish::class);
    }

    public function create(User $user, $ip)
    {
        // TODO: add logic here

        $userId = $user->getUserId();
        $statement = $this->database->prepare('INSERT INTO varnishes (ip, user_id) VALUES (:ip, :user)');
        $statement->bindParam(':ip', $ip);
        $statement->bindParam(':user', $userId);
        $statement->execute();
        return $this->database->lastInsertId();
    }

    public function link(Varnish $varnish, Website $website)
    {
        // TODO: add logic here

        array_filter($this->getByWebsite($website),
            function($associatedVarnish) use ($website){
                array_filter($this->getWebsites($associatedVarnish),
                    function($associatedWebsite) use ($associatedVarnish, $website){
                        if($associatedWebsite->website_id === $website->website_id) {
                            $this->unlink($associatedVarnish, $associatedWebsite);
                        }
                    }
                );
            }
        );

        $query = $this->database->prepare('INSERT INTO varnish_to_website (varnish_id, website_id) VALUES (:varnish, :website)');
        $query->bindParam(':varnish',$varnish->varnish_id);
        $query->bindParam(':website',$website->website_id);

        return $query->execute();
    }

    public function unlink(Varnish $varnish, Website $website)
    {
        // TODO: add logic here
        $query = $this->database->prepare('DELETE FROM varnish_to_website WHERE varnish_id = :varnish AND website_id = :website');
        $query->bindParam(':varnish',$varnish->varnish_id);
        $query->bindParam(':website',$website->website_id);
        return $query->execute();
    }

    public function getById($varnishId) {
        $query = $this->database->prepare('SELECT * FROM varnishes WHERE varnish_id = :id');
        $query->bindParam(':id', $varnishId);
        $query->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, Varnish::class);
        $varnish = $query->fetch(\PDO::FETCH_CLASS);
        return $varnish;
    }

    public function getAssociatedVarnish(Website $website)
    {
        foreach($this->getByWebsite($website) as $varnish){
            return $varnish->getIP();
        };

        return null;
    }

}
