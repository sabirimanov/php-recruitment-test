<?php

interface Old_Legacy_CacheWarmer_Resolver_Interface
{
    public function getIp($hostname);
}

class Old_Legacy_CacheWarmer_Resolver_Method implements Old_Legacy_CacheWarmer_Resolver_Interface
{
    public function getIp($hostname)
    {
        return gethostbyname($hostname);
    }
}

class Old_Legacy_CacheWarmer_Actor
{
    private $callable;

    public function setActor($callable) {
        $this->callable = $callable;
    }
    
    public function act($hostname, $ip, $url)
    {
        call_user_func($this->callable, $hostname, $ip, $url);
    }

    public function updateLastVisited($hostname)
    {
	        $db = new Snowdog\DevTest\Core\Database();

	        $select_query = $db->query("SELECT `website_id` FROM `websites` WHERE `hostname` = '$hostname'");
	        $data = $select_query->fetch();
	        $website_id = $data['website_id'];

	        $update_query = $db->prepare("UPDATE `pages` SET `last_visited` = NOW() WHERE `website_id` = '$website_id'");

	        try {
	            $update_query->execute();
	        } catch(Exception $e){
	            die($e->getMessage());
	        }
    }
}

class Old_Legacy_CacheWarmer_Warmer
{
    /** @var Old_Legacy_CacheWarmer_Actor */
    private $actor;
    /** @var Old_Legacy_CacheWarmer_Resolver_Interface */
    private $resolver;
    /** @var string */
    private $hostname;

    /**
     * @param Old_Legacy_CacheWarmer_Actor $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @param Old_Legacy_CacheWarmer_Resolver_Interface $resolver
     */
    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
    }

    public function warm($url) {
        $ip = $this->resolver->getIp($this->hostname);
        sleep(1); // this emulates visit to http://$hostname/$url via $ip
        $this->actor->act($this->hostname, $ip, $url);
        $this->actor->updateLastVisited($this->hostname);
    }

}
