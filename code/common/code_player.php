<?php
/**
 * player.class.php
 *
 * makes the player object
 * @package code_common
 * @author josh04
 */
 
class code_player {
 
    public $db;
    public $is_member = false;
    public $friends = array();
    public $page = "";
    public $settings = array();

   /**
    * might as well _use_ the fun features of our database class. no more =& $this->db.
    *
    * @param array $config db passwords, in case the db isn't set up yet.
    * @param array $settings settings array, for code_player_profile, basically
    */
    public function __construct($settings = array(), $config = array()) {
        $this->db =& code_database_wrapper::get_db($config);
        $this->settings =& $settings;
    }
 
   /**
    * Main player function. Used to generate the player who is playing.
    *
    * (DONE) I think some of the code_login procedure should end up in here instead.
    *
    * @param string $join table to get extra data from
    * @return bool good to go?
    */
    public function make_player($join = "") {
        if ($_COOKIE['user_id']) {
            $id = $_COOKIE['user_id'];
        } else {
            $id = $_SESSION['user_id'];
        }

        if ($join) {
            $player_query = $this->db->execute("SELECT `p`.*, `j`.* FROM `players` AS `p` LEFT JOIN `".$join."` AS `j`
                ON `p`.`id` = `j`.`player_id`
                WHERE `p`.`id`=?", array(intval($id)));
        } else {
            $player_query = $this->db->execute("SELECT * FROM `players` WHERE `id`=?", array(intval($id)));
        }

        $player_db = $player_query->fetchrow();
 
        $check = md5($player_db['id'].$player_db['password'].$player_db['login_rand']);
        
        if ($check == $_COOKIE['cookie_hash'] || $check == $_SESSION['hash']) {
            $this->is_member = true;
            $last_active = time();
             
            $player_db['last_active'] = $last_active;
 
            $this->player_db_to_object($player_db);

            $this->registered_date = date("l, jS F Y", $this->registered);
            $this->registered_days = intval((time() - $this->registered)/84600);

            $this->db->execute("UPDATE `players` SET `last_active`=? WHERE `id`=?", array ($last_active, $this->id));
          
            if ($this->halt_if_suspended()) {
                return false;
            }
        }
        return $this->halt_if_guest($this->page);
    }
 
 
   /**
    * cancels all if player is suspended. don't like it.
    * (TODO) error handling.
    *
    * @return bool suspended?
    */
    protected function halt_if_suspended() {
        if ($this->disabled == "1" && $page_name !="ticket") {
            return true;
        }
        return false;
    }
 
   /**
    * is guest? is this a guest page?
    * hmm, this couldn't possibly go wrong.
    *
    * @param string $page current page
    * @return bool kick them out?
    */
    protected function halt_if_guest($page) {
        $guest_pages = array("login","guesthelp","ranks", "index", "");
        if(!$this->is_member && !in_array($this->page,$guest_pages)){
            return false;
        }
        return true;
    }
 
   /**
    * secret function to make a player object
    *
    * @param array $player_db array from database
    */
    protected function player_db_to_object($player_db) {
        foreach($player_db as $key=>$value) { //Fill out our object.
            $this->$key = $value;
        }

    }
   /**
    * wrapper for get_player_by_*
    * 
    * @param string $identity name or id
    * @return bool succeed/fail
    */
    public function get_player($identity) {

        $ident_check = intval($identity);
        
        if (intval($identity)) {
            return $this->get_player_by_id($identity);
        } else {
            return $this->get_player_by_name($identity);
        }
    }

   /**
    * get user by id
    *
    * @param integer $id player id
    * @return bool succeed/fail
    */
    protected function get_player_by_id($id) {
        $player_query = $this->db->execute("SELECT * FROM `players` WHERE id=?", array(intval($id)));
        if ($player_query->recordcount() == 0) {
            return false;
        }
        $player_db = $player_query->fetchrow();
        $this->player_db_to_object($player_db);
        return true;
    }
 
   /**
    * get user by id
    *
    * @param integer $id player id
    * @return bool succeed/fail
    */
    protected function get_player_by_name($name) {
        $player_query = $this->db->execute("SELECT * FROM players WHERE username=?", array($name));

        if ($player_query->recordcount() == 0) {
            return false;
        }
        $player_db = $player_query->fetchrow();
        $this->player_db_to_object($player_db);
        return true;
    }
 
   /**
    * Commits player changes to the database + levels up
    *
    * Centralised player updating function, to ensure that if you have more than
    * enough XP, you get levelled up by the same set of code each time.
    *
    * @return bool levelled up?
    */
    public function update_player() {
        $levelled_up = false;

        $update_player['rank']          = $this->rank;
        $update_player['email']         = $this->email;
        $update_player['show_email']    = $this->show_email;
        $update_player['skin']          = $this->skin;
        
        $update_player['gold'] = $this->gold;

        //Update victor (the loser)
        $player_query = $this->db->AutoExecute('players', $update_player, 'UPDATE', 'id='.$this->id);
        return false;
    }

   /**
    * logs the current user out. Destroys, session, backdates cookie.
    *
    */
    public function log_out() {
        session_unset();
        session_destroy();
        setcookie("cookie_hash", NULL, mktime() - 36000000);
        setcookie("user_id", NULL, mktime() - 36000000);
    }

   /**
    * It logs the player in!
    *
    * @param string $username what name they?
    * @param string $password what's the secret word?
    * @return bool did they do it right?
    */
    public function log_in($username, $password) {
        $username = htmlentities($username,ENT_QUOTES,'UTF-8');

        $player_exists = $this->get_player_by_name($username);
        
        if (!$player_exists) {
            return false;
        }

        /**
        * Sigh. sha1 fails so bad, and there's no easy way to get rid of it which doesn't suck, esp on an upgraded db.
        */
        if ($this->password == sha1($password) && IS_UPGRADE) {
            $this->login_salt = substr(md5(uniqid(rand(), true)), 0, 5);
            $this->password = md5($password.$this->login_salt);

            $player_update['password'] = $this->password;
            $player_update['login_salt'] = $this->login_salt;

            $player_insert_query = $this->db->AutoExecute('players', $player_update, 'UPDATE', 'id = '.$this->id);

        }

        if ($this->password == md5($password.$this->login_salt)) {
            $login_rand = substr(md5(uniqid(rand(), true)), 0, 5);
            $update_player['login_rand'] = $login_rand;
            $update_player['last_active'] = time();

            $player_query = $this->db->AutoExecute('players', $update_player, 'UPDATE', 'id = '.$this->id);
            $hash = md5($this->id.$this->password.$login_rand);
            $_SESSION['user_id'] = $this->id;
            $_SESSION['hash'] = $hash;
            setcookie("user_id", $this->id, mktime()+2592000);
            setcookie("cookie_hash", $hash, mktime()+2592000);
            
            return true;
        } else {
            return false;
        }
    }
    
   /**
    * fer makin' a player
    *
    * @param string $username what do they want to be called?
    * @param string $password secret word time!
    * @param string $email not sure why we keep this at all
    * @return int player id
    */
    public function create_player($username, $password, $email) {
        $login_salt = substr(md5(uniqid(rand(), true)), 0, 5);

        $player_insert['username'] = $username;
        $player_insert['password'] = md5($password.$login_salt);
        $player_insert['email'] = $email;
        $player_insert['registered'] = time();
        $player_insert['last_active'] = time();
        $player_insert['ip'] = $_SERVER['REMOTE_ADDR'];
        $player_insert['login_salt'] = $login_salt;

        if($this->settings['verification_method']==1) {
            $player_insert['verified'] = 1;
        } else {
            $player_insert['verified'] = 0;
        }

        $player_insert_query = $this->db->AutoExecute('players', $player_insert, 'INSERT');
        
        if ($player_insert_query) {
            $player_id = $this->db->Insert_Id();
        } else {
            $player_id = 0;
        }
        
        if($this->settings['verification_method']==3) {
            $to = 1;
            $body = $username . " has just registered an account. You can approve it [url=?section=admin&page=profile_edit&action=approve&id=".$player_id."]here[/url].";
            $subject = "New account";
            $this->db->execute("INSERT INTO `mail` (`to`,`from`,`subject`,`body`,`time`,`status`) VALUES (?,?,?,?,?,?)",array($to, 1, $subject, $body, time(), 0));
        }

        return $player_id;
    }

   /**
    * Changes the player password
    *
    * @param string $newpassword the new password!
    * @return bool success
    */
    public function update_password($newpassword) {
        $login_salt = substr(md5(uniqid(rand(), true)), 0, 5);

        $update_password['password'] = md5($newpassword.$login_salt);
        $update_password['login_salt'] = $login_salt;

        $password_query = $this->db->AutoExecute('players', $update_password, 'UPDATE', 'id = '.$this->id);
        
        $this->password = $update_password['password'];

        $hash = md5($this->id.$this->password.$this->login_rand);
        $_SESSION['hash'] = $hash;
        setcookie("cookie_hash", $hash, mktime()+2592000);
        
        if ($password_query) {
            return true;
        } else {
            return false;
        }
    }

// A constant reminder that there are always more functions to add ;)
   /**
    * Retrieves the player's friends
    *
    * @return boolean success
    */
    function getfriends(){

        // This is a resource-expensive funciton, so let's put a circuit breaker in
        if(!empty($this->friends)) return true;
 
        $query = $this->db->execute("SELECT `id`, `username`
    FROM `players`
    WHERE (`id` IN (SELECT f.id2
                   FROM friends AS f
                   WHERE (f.id1=? OR f.id2=?) and `accepted`=1)
    OR `id` IN (SELECT f.id1
                FROM friends AS f
                WHERE (f.id1=? OR f.id2=?) and `accepted`=1))
    AND NOT `id`=?",
                array_fill(0, 5, $this->id));

        if ($query->recordcount() == 0) {
            return false;
        } else {
            while($friend = $query->fetchrow()) {
                $this->friends[($friend['id'])] = $friend['username'];
            }
            return true;
        }
    }
 
}
?>