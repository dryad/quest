<?php
require_once("code/angst/code_angst_index.php");
/**
 * code_login.class.php
 *
 * logs players in and out
 * @author josh04
 * @package code_public
 */
class angst_login extends code_angst_index {
    
    public $player_class = "code_player_profile";
    public $override_skin = "angst";

   /**
    * class override. calls parents, sends kids home.
    *
    * @return string html
    */
    public function construct($code_other = "") {
        if ($code_other) {
             parent::construct($code_other);
             return;
        }
        $this->section = "angst";
        $this->initiate("skin_angst_index");
        if ($this->player->is_member) {
            $code_login = $this->log_out();
        } else {
            $code_login = $this->login_switch();
        }

        parent::construct($code_login);
    }

   /**
    * calls code_player function
    *
    */
    public function log_out() {
        $this->player->log_out();
        header("Location: index.php?action=logged_out");
    }

   /**
    * switches between login and register
    *
    * @return string html
    */
    public function login_switch() {
        if ($_GET['action'] == 'register_submit') {
            $register_or_login = $this->register_submit();
        } else if ($_GET['action'] == 'register') {
            $register_or_login = $this->register();
        } else {
            $register_or_login = $this->log_in();
        }

        return $register_or_login;
    }

   /**
    * logs user in
    * (TODO) slighty odd code structure here
    *
    * @return string html
    */
    public function log_in() {
        if ($_POST['username'] == "") {
            $log_in = $this->index_player($this->skin->error_box($this->lang->please_enter_username));
            return $log_in;
        }

        if ($_POST['password'] == "") {
            $log_in = $this->index_player($this->skin->error_box($this->lang->please_enter_password));
            return $log_in;
        }

        $player = new code_player_profile($this->settings);
        $player_exists = $player->log_in($_POST['username'], $_POST['password']);

        if (!$player_exists) {
            $log_in = $this->index_player($this->skin->error_box($this->lang->password_wrong));
            return $log_in;
        }

        if ($player->verified==0) {
            $player->log_out();
            $log_in = $this->skin->player_not_approved();
            return $log_in;
        }

        header("Location: index.php");
        exit;

    }

   /**
    * displays registration screen
    *
    * @return string html
    */
    public function register() {

        if($this->settings['verification_method']==0) {
            $register_submit = $this->index_player($this->skin->error_box($this->lang->registration_disabled));
            return $register_submit;
        }

        $register = $this->skin->register("", "", "");
        return $register;
    }

   /**
    * registers a user.
    *
    * @return string html
    */
    public function register_submit() {

        if($this->settings['verification_method']==0) {
            $register_submit = $this->index_player($this->skin->error_box($this->lang->registration_disabled));
            return $register_submit;
        }

        $username = htmlentities($_POST['username'],ENT_QUOTES,'UTF-8');
        $email = htmlentities($_POST['email'],ENT_QUOTES,'UTF-8');

        $player_query = $this->db->execute("SELECT id FROM players WHERE username=?", array($_POST['username']));

        if ($username == "") {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->username_needed));
            return $register_submit;
        } else if (strlen($_POST['username']) < 3) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->username_not_long_enough));
            return $register_submit;
        } else if (!preg_match("/^[-_a-zA-Z0-9]+$/", $_POST['username'])) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->username_banned_characters));
            return $register_submit;
        } else if ($player_query->recordcount() > 0) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->username_conflict));
            return $register_submit;
        }


        if (!$_POST['password']) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->password_needed));
            return $register_submit;
        } else if ($_POST['password'] != $_POST['password_confirm']) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->passwords_do_not_match));
            return $register_submit;
        } else if (strlen($_POST['password']) < 3) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->password_not_long_enough));
            return $register_submit;
        }

        //Check email
        if (!$_POST['email']) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->email_needed));
            return $register_submit;
        } else if ($_POST['email'] != $_POST['email_confirm']) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->emails_do_not_match));
            return $register_submit;
        } else if (strlen($_POST['email']) < 3) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->email_not_long_enough));
            return $register_submit;
        } else if (!preg_match("/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i", $_POST['email'])) {
            $register_submit = $this->skin->register($username, $email, email_wrong_format);
            return $register_submit;
        } else {
            $email_query = $this->db->execute("select `id` from `players` where `email`=?", array($_POST['email']));
            if ($email_query->recordcount() > 0 && $this->settings['ban_multiple_email']) {
                $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->email_conflict));
                return $register_submit;
            }
        }

        if ($this->settings['register_ip_check']) {
            
            $ip_query = $this->db->execute("SELECT `registered` FROM `players` WHERE `ip`=? ORDER BY `registered` DESC", array($_SERVER['REMOTE_ADDR']));

            if ($account = $ip_query->fetchrow()) {

                if (time() - $account['registered'] < 1800) {
                    $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->multiple_registrations));
                    return $register_submit;
                }
            }
        }
        
        $registered_player = new code_player($this->settings);
        $username = htmlentities($_POST['username'], ENT_QUOTES, "utf-8");
        $email = htmlentities($_POST['email'], ENT_QUOTES, "utf-8");

        $success = $registered_player->create_player($username, $_POST['password'], $email);
        
        if (!$success) {
            $register_submit = $this->skin->register($username, $email, $this->skin->error_box($this->lang->error_registering));
            return $register_submit;
        }
        
        if ( $this->settings['verification_method']==3 ) {
            $to = 1; // must implement bulk mail by membership group
            $from = $success;
            $body = $username . " has just registered an account. You can approve it [url=index.php?section=admin&amp;page=profile_edit&amp;action=approve&amp;id=".$player_id."]here[/url].";
            $subject = "New account";

            require_once("code/public/code_mail.php");

            $mail = new code_mail();
            $mail->mail_send($from, $from, $body, $subject);
        }
        // loooooooooool. I'mma leave this here.

        $body = "<strong>Welcome to CHERUB Quest!</strong>
        <p>CHERUB Quest is a online browser game for fans of
        the CHERUB series by Robert Muchamore, but isn't
        affiliated so don't contact him with problems.
        If you have any problems, for that matter, look at the
        Help Page or use the Ticket System to contact an Admin
        (TeknoTom, JamieHD, Commander of One, Josh0-4 and Grego)</p>

        <p>But above all, have fun!</p>";

        $register_submit = $this->index_player($this->skin->error_box($this->lang->registered));
        return $register_submit;

    }

}
?>