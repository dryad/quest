<?php
/**
 * work page html
 *
 * @author josh04
 * @package skin_public
 */
class skin_work extends skin_common {

    public function make_work() {
        $make_work .= "
            <h2>Work for tokens</h2>
            Need some extra tokens? A bit short for that hospital visit? Then this is the place for you. In exchange for a bit of work we are more then happy <br /><br />
            <u>The current going rate for work is one energy point for:</u>
            <ul>
            <li>Level One: 50 Tokens</li>
            <li>Level Two: 100 Tokens</li>
            <li>Level Three: 150 Tokens</li>
            <li>Level Four: 200 Tokens</li>
            <li>Level Five: 250 Tokens</li>
            <li>Level Six: 300 Tokens</li>
            <li>Level Seven: 350 Tokens</li>
            <li>Level Eight: 400 Tokens</li>
            <li>Level Nine: 450 Tokens</li>
            <li>Level Ten: 500 Tokens</li>
            </ul>
            <form action='index.php?page=work' method='POST'>
            <input type='submit' name='action' value='Work' />
            </form>
            ";
        return $make_work;
    }

}
?>
