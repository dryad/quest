<?php
/**
 * 
 * Main quests skin
 *
 * @author grego
 * @package skin_public
 */
class skin_quest extends skin_common {
    
   /**
    * quest selection rows
    * 
    * @param array $quest quest details
    * @return string html
    */
    public function quest_row($quest) {
        return "<div class='quest-select'>
        <a style='float:right;' href='index.php?page=quest&id=".$quest['id']."'>Start this quest!</a>
        <h3>".$quest['title']."</h3>
        Author: ".$quest['author']."<br />
        <p>".$quest['description']."</p></div>";
    }

   /**
    * quest selection page
    * 
    * @param string $quest_html quests you can choose to do
    * @return string html
    */
    public function quest_select($quest_html, $message='') {
        return "<h2>Select a Quest</h2>
        ".($message?"<div class='error'>".$message."</div>":"")."
        <div>You can choose a quest to embark on from below.
        When on a quest, you cannot perform various other actions, such as visit other places.</div>
        ".($quest_html!=""?$quest_html:"<div class='quest-select'>There are no quests installed!</div>");
    }

   /**
    * quest log page
    * 
    * @param object $quest main object
    * @param integer $next_event time until next event
    * @param string $quest_html previous stages
    * @return string html
    */
    public function quest($quest, $next_event, $quest_html) {
        $html = "<h2>".$quest->title."</h2>
        By ".$quest->author."
        <div class='explanation'>".$quest->body."</div>
        ".($next_event<=0?"":"<div id='quest-countdown-container' style='text-align:center;'>Next event in <span id='quest-countdown'>".$next_event."</span> seconds</div>")."
        <script>startQuestCountdown();</script>";
        return $html . $quest_html;
    }

   /**
    * single event
    * 
    * @param string $title event title
    * @param string $body event description
    * @return string html
    */
    public function render_event($title, $body) {
        return "<div style='width:500px;margin:8px auto;padding:4px;border:1px solid #FF0;background-color:#FFC;'>
        <strong>".$title."</strong><br />
	".$body."
        </div>";
    }

   /**
    * an encounter
    * 
    * @param string $enconter details
    * @param string $body main event body
    * @param string $user current username
    * @return string html
    */
    public function encounter($encounter, $body, $user) {
        return "<table><tr><td>".$body.($body&&$encounter['main']?"<hr />":"")."
        " . $encounter['main'] . "<br style=\"clear:both;\" /></td>
        <td style=\"border-left:1px solid #333;padding:4px;width:150px;height:auto;text-align:right;\">
        <div style=\"text-align:left;\"><strong>Enemies:</strong> ".$encounter['enemies']."<br /><strong>Result:</strong> ".($encounter['success']?"Won":"Lost")."</div>
        <br />".$this->gains($encounter['gold'],$encounter['xp'],$encounter['hp'])."</td>
        </tr></table>";
    }

   /**
    * a challenge
    * 
    * @param object $challenge details
    * @param string $body main event body
    * @param string $user current username
    * @return string html
    */
    public function challenge($challenge, $body, $user) {
        return "<table><tr><td>".$body.($body&&$challenge?"<hr />":"")."
        ".$challenge['main'] . "<br style=\"clear:both;\" /></td>
        <td style=\"border-left:1px solid #333;padding:4px;width:150px;height:auto;text-align:right;\">
        <div style=\"text-align:left;\">In a <strong>".$challenge['source']."</strong> check of ".$challenge['value'].", ".$user." got ".$challenge['result'].".</div>
        <br />".$this->gains($challenge['gold'],$challenge['xp'],0)."</td>
        </tr></table>";
    }

   /**
    * a list of enemies
    * 
    * @param array $enemies
    * @return string html
    */
    public function enemy_list($enemies) {
        $count = count($enemies);
        for($i=0;$i<$count;$i++) {
                if($i==$count-1) $ret .= $enemies[$i];
                else if($i==$count-2) $ret .= $enemies[$i] . " and ";
                else $ret .= $enemies[$i] . ", ";
                }
        return $ret;
    }

   /**
    * the rewards of an event
    * 
    * @param string $gold gold received
    * @param string $xp xp earned
    * @return string html
    */
    public function gains($gold=0, $xp=0, $hp=0) {
        return ($gold>0?"+".$gold." gold".($xp>0?"<br />":""):"")."
        ".($xp>0?"+".$xp." xp":"").($hp!=0?"<br />":"")."
        ".($hp==0?"":$hp." hp");
    }

   /**
    * it's over. quit?
    * 
    * @return string html
    */
    public function finish_quest() {
        return "<div style='text-align:center;'><a href='index.php?page=quest'>Finish quest</a></div>";
    }


}
?>