<?php
/******************************************************************************
 *
 * Copyright © 2010
 *
 * FreeCode Norway AS
 * Nydalsveien 30A, NO-0484 Oslo, Norway
 * Norway
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ******************************************************************************/


  /** 
   This project mostly uses an MVC layout, though it's not very strict
   about it. The file is split up into files as follows:

   model.php: All model code.

   db.php: Database abstraction, provides a set of global functions
   (actually static methods, in order to be more namespace clean) for
   database access.

   controllers/*.php: All controllers. As of today, view code is
   embedded in the controller code. That should be cleaned up.

   views/*.php: Currently empty, view code lives in the controllers.

   index.php: Glue code.

   install.php: Installation check code.

   util.php: Mish util functions.

   form.php: Form related util code.

   */

$start_time = microtime(true);

define('IS_MAIN_PAGE', 1);

require_once("common/util/util.php");
require_once("common/util/db.php");
require_once("common/install.php");

require_once("common/util/plugin.php");
require_once("common/model.php");
require_once("common/util/form.php");
require_once("common/controller.php");
require_once("common/view.php");

/** Main class. Responsible for a bit of scafolding around the page
 proper, like the top menu and the performance data at the
 bottom. Also responsible for locating the appropriate coltroller,
 initializing it and handing of control to it.
 */
class Application
{

    var $scripts = array("common/static/jquery.js",
                         "common/static/common.js",
                         "common/static/date.js");
    
    var $styles = array(array('name'=>'common/static/common.css', 
                              'media'=>"screen,projection"));

    var $use_tiny_mce;
    
    
    function enableDatePicker()
    {
        $this->addScript("common/static/jquery.datePicker.js");
        $this->addStyle ('common/static/datePicker.css');
    }
        
    function enableTinyMce()
    {
        $this->use_tiny_mce = true;
        $this->addScript("common/static/tiny_mce/tiny_mce.js");
    }
        
    /*
     Write http headers, html headers and the top menu
     */
    function writeHeader($title, $controller)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
';
        foreach($this->styles as $s) {
            echo '<link rel="stylesheet" href="'.util::getPath().htmlEncode($s['name']).'" type="text/css" media="'.$s['media'].'" />
';
        }
        foreach($this->scripts as $s) {
            echo '<script type="text/javascript" src="'.util::getPath().htmlEncode($s).'"></script>
';
        }
        
        echo '<title>'.htmlEncode($title).'</title>';
        if ($this->use_tiny_mce) {
            
            echo '
<script type="text/javascript">

tinyMCE.init({
mode : "specific_textareas",
editor_selector : "rich_edit",
theme : "simple"
});
</script>';
        }
        
        echo '
        </head>
        <body>

';
        $this->writeMenu($controller);
        
        $this->writeMessages();
        

    }
    
    function addScript($s)
    {
        $this->scripts[] = $s;
    }
    
    function addStyle($s, $media='screen,projection')
    {
        $this->styles[] = array('name'=>$s, 'media'=>$media);
    }
    
    /**
     Write bottom content.
     */
    function writeFooter() 
    {
        global $start_time;
        $stop_time = microtime(true);
        
        $copyright = "© 2009 Freecode AS";
        $performance = "Page rendered in " . sprintf("%.2f", $stop_time - $start_time + param('redirect_render_time',0.0)) . " seconds. " .(db::$query_count+param('redirect_query_count',0)) . " database queries executed in " . sprintf("%.2f", db::$query_time+param('redirect_query_time',0)) . " seconds.";
        
        echo "<div class='copyright'>\n";
        
        echo makeLink("http://www.freecode.no", $copyright, 'copyright_inner', $performance);
        echo "</div>\n";
        
        echo "<script>stripe();</script>
</body>
</html>
";

    }
    
    /**
     Write out the message list.
	*/
    function writeMessages()
    {
        $msg = messageGet();
        if ($msg != "") {
            echo "<div class='messages'>\n";
            echo "<div class='messages_inner'>\n";
            echo $msg;
            echo "</div>\n";
            echo "</div>\n";
        }
    }


    /**
     Main application runner.
	*/
    function main() 
    {
        ob_start();
        ob_implicit_flush(0);
        
        ob_start();
        
        util::setTitle("");
        $controller = null;
                
        try {
            
            $controller = param('controller',param('action',$this->getDefaultController()));
            
            $controller_str = "{$controller}Controller";
            /**
             Try and see if we have a controller with the specified name
            */
            
            if(class_exists($controller_str)) {
                $controller = new $controller_str($this);
                $controller->run();
            } else {
                header("Status: 404");
                echo "Controller $controller not found!";
                exit(0);
          }
        }
        catch(PDOException $e) {
            echo "PDO Exception";
            echo $e->getMessage();
        }

        util::doRedirect();
                
        $out = ob_get_contents();
        ob_clean();
        $this->writeHeader( $this->getApplicationName() . " - " . htmlEncode(util::getTitle()), $controller);
        echo $out;
        $this->writeFooter();
        ob_end_flush();

        util::printGzippedPage();

    }
    
}

if(!db::init(FC_DSN_DEFAULT)) {
    //FreeCMDB::writeMessages();
    die("The site is down. Could not connect to the database.");
}

db::query("set client_encoding to \"utf8\"");

?>