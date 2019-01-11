<?php

namespace Snowdog\DevTest\Model;
use Snowdog\DevTest\Core\Database;

class User
{
    public $user_id;
    public $login;
    public $password_hash;
    public $password_salt;
    public $display_name;
    public $total_visited_pages;
    public $most_recent_visited;
    public $least_recent_visited;

    public function __construct()
    {
        $this->user_id = intval($this->user_id);
        $this->total_visited_pages = self::getUserVisitedPagesTotal($this->user_id);
        $this->most_recent_visited= self::getRecentVisitedPage($this->user_id, "most");
        $this->least_recent_visited = self::getRecentVisitedPage($this->user_id, "least");
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    /**
     * @return string
     */
    public function getPasswordSalt()
    {
        return $this->password_salt;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @return int
     */
    public static function getUserVisitedPagesTotal($user_id)
    {
        $db = new Database();

        $select_query = $db->query("SELECT COUNT(*) AS count FROM `pages` p JOIN `websites` w ON w.website_id = p.website_id WHERE w.user_id = '$user_id'");
        $data = $select_query->fetch();

        $total = $data['count'];
        return $total;
    }

    /**
     * @return string
     */
    public static function getRecentVisitedPage($user_id, $ml_type)
    {

        if ($ml_type === "most") {
            $order = "DESC";
        } else {
            $order = "ASC";
        }

        $db = new Database();

        $select_query = $db->query("SELECT CONCAT(w.hostname, '/', p.url) AS url, w.name FROM `pages` p LEFT JOIN `websites` w ON w.website_id = p.website_id WHERE w.user_id = '$user_id' ORDER BY p.last_visited $order LIMIT 1");
        $data = $select_query->fetch();

        $recent_page_name =  $data['url'] . " (" . $data['name'] . ")";
        return $recent_page_name;
    }

    /**
     * @return int
     */
    public function totalVisitedPages()
    {
        return $this->total_visited_pages;
    }

    /**
     * @return string
     */
    public function mostRecentPage()
    {
        return $this->most_recent_visited;
    }

    /**
     * @return string
     */
    public function leastRecentPage()
    {
        return $this->least_recent_visited;
    }
}
