<?php
/**
 * Interface for adding/removing/moving menu items.
 *
 * @author josh04
 * @package code_public
 */
class code_menu_admin extends code_common {

   /**
    * class override. calls parents, sends kids home.
    *
    * @return string html
    */
    public function construct() {
        $this->initiate("skin_menu_admin");

        $code_menu_admin = $this->menu_admin_switch();

        parent::construct($code_menu_admin);
    }

    private function menu_admin_switch() {

        if ($_GET['action'] == 'add') {
            $menu_admin_switch = $this->add();
            return $menu_admin_switch;
        }

        if ($_GET['action'] == 'modify') {
            $menu_admin_switch = $this->modify();
            return $menu_admin_switch;
        }

        $menu_admin_switch = $this->show_menu();
        return $menu_admin_switch;
    }

    public function show_menu($message = "") {
        $menu_post = array();
        if ($_GET['action'] == 'add') {
            $menu_post['label'] = htmlentities($_POST['label'], ENT_COMPAT, 'utf-8');
            $menu_post['section'] = htmlentities($_POST['section'], ENT_COMPAT, 'utf-8');
            $menu_post['page'] = htmlentities($_POST['page'], ENT_COMPAT, 'utf-8');
            $menu_post['extra'] = htmlentities($_POST['extra'], ENT_COMPAT, 'utf-8');
            $menu_post['category'] = htmlentities($_POST['category'], ENT_COMPAT, 'utf-8');

            if ($_POST['function']) {
                $menu_post['function'] = "checked='checked'";
            }

            if ($_POST['enabled']) {
                $menu_post['enabled'] = "checked='checked'";
            }

            if (!$menu_post['label'] && !$menu_post['section'] && !$menu_post['page']) {
                $menu_post['enabled'] = "checked='checked'";
            }
        }

        $menu_query = $this->db->execute("SELECT * FROM `menu` ORDER BY `order` ASC");

        while($menu_entry = $menu_query->fetchrow()) {

        if ($menu_entry['function']) {
                $menu_entry['function'] = "checked='checked'";
            } else {
                $menu_entry['function'] = '';
            }

        if ($menu_entry['enabled']) {
                $menu_entry['enabled'] = "checked='checked'";
            } else {
                $menu_entry['enabled'] = '';
            }

            $menu_categories[$menu_entry['category']] .= $this->skin->make_menu_entry($menu_entry);
        }

        foreach($menu_categories as $category_name => $category_html) {
            $menu_html .= $this->skin->menu_category($category_name, $category_html);
        }

        $show_menu = $this->skin->menu_wrap($menu_html, $menu_post, $message);

        return $show_menu;
    }

    public function modify() {
        $id = intval($_POST['id']);

        if (!$id) {
            $modify = $this->show_menu($this->skin->lang_error->no_menu_entry_selected);
            return $modify;
        }

         if (!$_POST['label']) {
            $modify = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_label));
            return $modify;
        }

        if (!$_POST['section']) {
            $modify = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_section));
            return $modify;
        }

        if (!$_POST['page']) {
            $modify = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_page));
            return $modify;
        }

        if ($_POST['function']) {
            $function = 1;
        }

        if ($_POST['enabled']) {
            $enabled = 1;
        }

        require_once("code/common/code_menu.php");
        $code_menu = new code_menu($this->db, $this->player, $this->section, $this->page, $this->pages);
        $code_menu->modify_menu_entry($id, $_POST['label'], $_POST['category'], $_POST['section'], $_POST['page'], $_POST['extra'], $function, $enabled);
        $modify = $this->show_menu($this->skin->success_box($this->skin->lang_error->menu_entry_modified));
        return $modify;
    }

    private function add() {
        if (!$_POST['label']) {
            $add = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_label));
            return $add;
        }

        if (!$_POST['section']) {
            $add = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_section));
            return $add;
        }

        if (!$_POST['page']) {
            $add = $this->show_menu($this->skin->error_box($this->skin->lang_error->no_page));
            return $add;
        }

        if ($_POST['function']) {
            $function = 1;
        }
        
        if ($_POST['enabled']) {
            $enabled = 1;
        }

        require_once("code/common/code_menu.php");
        $code_menu = new code_menu($this->db, $this->player, $this->section, $this->page, $this->pages);
        $code_menu->add_menu_entry($_POST['label'], $_POST['category'], $_POST['section'], $_POST['page'], $_POST['extra'], $function, $enabled);
        $add = $this->show_menu($this->skin->success_box($this->skin->lang_error->menu_entry_added));
        return $add;
    }
}
?>