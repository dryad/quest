<?php
/**
 * member list code
 *
 * @author josh04
 * @package code_public
 */
class code_members extends code_common {

   /**
    * class override. calls parents, sends kids home.
    *
    * @return string html
    */
    public function construct() {
        $this->initiate("skin_members");

        $code_members = $this->members_list();

        parent::construct($code_members);
    }

   /**
    * constructs list of members. 
    *
    * @return string html
    */
    public function members_list() {
        $limit = ($_GET['limit']) ? intval($_GET['limit']) : 30; //Use user-selected limit of players to list
        $begin = ($_GET['begin']) ? abs(intval($_GET['begin'])) : $this->player->id - intval($limit / 2); //List players with the current player in the middle of the list

        $total_players = $this->db->getone("SELECT count(ID) AS `count` FROM `players`");

        $begin = ($begin >= $total_players) ? abs($total_players - $limit) : $begin; //Can't list players don't don't exist yet either

        $begin = ($begin < 0) ? 0 : $begin;

        $lastpage = (($total_players - $limit) < 0) ? 0 : $total_players - $limit; //Get the starting point if the user has browsed to the last page

        $previous = $begin - $limit;
        $next = $begin + $limit;


        $memberlist = $this->db->execute("SELECT `id`, `username`, `level`
                                    FROM `players` ORDER BY `level` DESC
                                    LIMIT ?,?",
                                    array($begin, $limit));

        while($member = $memberlist->fetchrow()) {
            $members_list .= $this->skin->member_row($member);
        }

        $members_list = $this->skin->members_list($begin, $next, $previous, $limit, $members_list);

        return $members_list;

    }

}
?>
