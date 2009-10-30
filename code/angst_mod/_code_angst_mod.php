<?php
/**
 * extends code_common, verifies that the user in question has the auth to do this
 *
 * @author josh04
 * @package code_admin
 */
class _code_angst_mod extends code_common {

    public function make_player() {
        parent::make_player();

        if ($this->player->rank != "Admin" && $this->player->rank != "Moderator") { // yeah, I know, poor implementation of permissions
            parent::make_skin();
            $this->error_page($this->lang->access_denied);
        }
    }

    public static function _code_angst_mod_menu(&$menu, $label) {
        if ($menu->player->rank != "Admin" && $menu->player->rank != "Moderator") { // yeah, I know, poor implementation of permissions
            $menu->enabled = false;
        }
        return $label;
    }

}
?>