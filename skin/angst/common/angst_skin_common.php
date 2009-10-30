<?php
/**
 * skin_common.class.php
 *
 * Contains static skin elements, such as header html. No functions called or processing done.
 * @package skin_common
 * @author josh04
 */

class angst_skin_common extends skin_common {

   /**
    * Error strings, lots of error strings
    *
    * @var lang_error
    */
    public $lang_error;

   /**
    * Title of the page, for the header
    *
    * @var string
    */
    public $title;

    public $javascript;

   /**
    * displays error; needs skinning
    *
    * @param string $error error text
    * @return string html
    */
    public function error_page($error) {
        $error_page = "<h2>Error</h2><div class='error'>".$error."</div>
			<p><a href=\"index.php\">Return home</a> | <a href=\"#\" onclick=\"javascript:history.go(-1);return false;\">Back one page</a></p>
			";
        return $error_page;
    }

   /**
    * Makes the top title
    *
    * @param string $site_name the site name
    * @return string the title
    */
    public function title($site_name) {
        $title = $site_name." - ".$this->title;
        return $title;
    }
    
   /**
    * return the static start of the skin
    *
    * @param string $title page title
    * @param string $site_name name of the site
    * @param string $css name of css file
    * @return string html
    */
    public function start_header($title, $site_name, $skin) {
        
        $start_header = "
                    <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'> 
                    <html xml:lang='en' lang='en' xmlns='http://www.w3.org/1999/xhtml'>
                    <head>
                    <title>".$title."</title>
                    <meta http-equiv='content-type' content='text/html; charset=utf-8' />
                    <link rel='stylesheet' type='text/css' href='./skin/angst/common/" . $skin . "' />
                    <script type='text/javascript' language='JavaScript' src='./skin/angst/common/jquery.js'></script>
<script type='text/javascript' language='JavaScript' src='./skin/angst/common/autoresize.jquery.js'></script>
<script type='text/javascript' language='JavaScript'>
".$this->javascript."</script>
                    <script type='text/javascript' language='JavaScript' src='./skin/angst/common/functions.js'></script>
                    </head>
                    <body>
                    <div id='wrapper'>
                    <h1 id='header-text'><a href='index.php'>".$site_name."</a></h1>
                    ";
        
        return $start_header;
    }

    /**
     * glues the left side to the right. yeah.
     * @param string $page page html
     * @return string html
     */
    public function glue($page) {
        $glue = "
                 <div id='content'>".$page."
                 </div>";
        return $glue;
    }

    /**
     * returns footer html
     * @return string html
     */
    public function footer() {
         $footer .= "
                    <div id='footer'>
                        <div id='footer-text'>
                            <span style='font-size:1.5em;'>Angst</span><br />
                            This version by Josh04 and Grego.
                        </div>
                    </div>
                </div>
                </body>
            </html>";
        return $footer;
    }

   /**
    * for making shiny red boxes
    * (TODO) USE THIS EVERYWHERE
    *
    * @param string $message error
    * @return string html
    */
    public function error_box($message) {
        $error_box = "<div class='error'>".$message."</div>";
        return $error_box;
    }

   /**
    * for making shiny green boxes
    *
    * @param string $message what happened?
    * @return string html
    */
    public function success_box($message) {
        $success_box = "<div class='success'>".$message."</div>";
        return $success_box;
    }

   /**
    * generates a little link to a relevant help topic
    *
    * @param string $id the id of the help topic you're pushing for
    * @return string html
    */
    public function popup_help($id) {
        $popup_help = " <a href='#' class='popup-help-link' onclick='popup_help(${id});return false;'>(?)</a>";
        return $popup_help;
    }

}
?>