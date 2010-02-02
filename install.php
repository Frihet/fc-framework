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
   Protection against bad includes.
  */
if (!defined("IS_MAIN_PAGE")) {
    return;
}

define('CONFIG_FILE_PATH', 'config.php');

class Checks{
    function configFileExists(){
        $fe = file_exists(CONFIG_FILE_PATH);
        if ($fe) {
            require_once("config.php");
            if(defined('FC_DSN_DEFAULT')) {
                return true;
            }
        }
        return false;
    }
}


class InstallApplication
extends Application
{

    function check_dependencies()
    {
        $res = null;
        
        if(!class_exists("PDO")) {
            $res[] = "<span class='error'>The PDO library is missing. You can install it by writing <code>pecl install PDO</code> at the command line.</span>";
        }

        return $res;
        
    }
    
    function writeMenu($controller)
    {
        return "";
    }

    function getDsn()
    {
        return array('default');
    }
    
    
    function view()
    {
        
        $this->writeHeader("Install","");
        
        $dep_str="";
        $dep = $this->check_dependencies();
        if (count($dep)) {
            $dep_str = "<p>The following problems have been detected with your server setup.: </p><p>" . implode("</p><p>", $dep) ." </p>";
        }
        
        ?>
<div class='content_install'>
<div class='content_install_inner'>
<h2>Install software</h2>

 <?= $dep_str; ?>
<p>
This application has not been installed. Please fill out the following form to install it. All fields are required.
</p>
<form action="" name="dbDetailForm" method="post" onSubmit='return installCheckFields();'>
<input type='hidden' name='action' value='install'/>
<table class='striped'>
	<thead>
		<tr>
			<th colspan="3">Database Details</th>
		</tr>
	</thead>
	<tbody>
        <?php
        foreach ($this->getDsn() as $dsn) {

            $dsn_name = htmlEncode($dsn);
            
            $dsn_value = htmlEncode(param("dsn_$dsn","pgsql:dbname=DATABASE;host=localhost;user=USERNAME;password=PASSWORD"));
            

            echo "
 
<tr>
  <td align='right'><label for='dsn_{$dsn_name}'>$dsn_name DSN</label></td>
  <td align='left'>
    <input type='text' id='dsn_{$dsn_name}' name='dsn_{$dsn_name}'
				size='80' class='required'
				value='$dsn_value'> 
  </td>
  <td>
    <span id='dsn_{$dsn_name}_notification'>".htmlEncode(param("dsn_{$dsn}_error"))."</span>
  </td>
</tr>
";
        }

        // We do not use json_encode here, because we do not want the installer to have dependencies
        echo "
<script>
var InstallData = 
{
    dsn:['" . implode("', '", $this->getDsn()) . "']
};
</script>
";
        

?>

        </tbody>
</table>

<div class='button_list'>
<p>
<button type='button' onclick='installDbCheck();'>Test database...</button>
</p>
<p>
<button>Install!</button>
</p>
</div>

</form>        
<script>
stripe();
</script>
</div>
</div>
<?php
  $this->writeFooter();
        
    }

    function db_check()
    {
        // We do not use json_encode here, because we do not want the installer to have dependencies
        if (!db::init(param('dsn_value',''))) {
            $res =  "<span class='error'>" . htmlEncode(db::getError())."</span>";
        } else {
            $res =  "Database details ok!";
        }
        echo "{dsn:'".addSlashes(param('dsn_name'))."', status:'".addSlashes($res)."'}";
    }

    function install() 
    {

        $ok = true;
        
        $dep = $this->check_dependencies();
        if (count($dep)) {
            $ok = false;
        }
        else {
            foreach ($this->getDsn() as $dsn) {
                
                if (!db::init(param('dsn_'.$dsn,''))) {
                    $ok = false;
                }
            }
        }
        
        if (!$ok) {
            $this->view();
            return;
        }
        
        $this->writeHeader("install","");

        echo "
<div class='content_install'>
<div class='content_install_inner'>
";
      
        db::init(param('dsn_default'));
        foreach( explode(';', file_get_contents('./static/schema.sql')) as $sql) {
            db::query($sql);
        }

        $config = "<?php\n";
        foreach ($this->getDsn() as $dsn) {
            $config .= "define('FC_DSN_".strtoupper($dsn)."', '".addSlashes(param('dsn_'.$dsn))."');\n";
        }
        
        $config .= "?>";
        
        $write_ok = @file_put_contents("./config.php", $config);
        
        if (!$write_ok) {
            $uid = posix_getuid();
            $passwd = posix_getpwuid($uid);
            $username = $passwd['name'];
            
            $script_dir =  dirname($_SERVER['SCRIPT_FILENAME']);

            ?>

            Could not write configuration file. This probably means that the web server 
            does not have write privileges in the install directory. You can either add the 
            correct privileges using a command like <pre>chown -R <?=$username;?> <?= $script_dir ?></pre> and press the «Reload» button, or manually create the file <?= $_SERVER['DOCUMENT_ROOT']; ?>/config.php on the web server with the following contents:

<?php
            
            echo "<pre>";

            echo htmlEncode($config);
            
            echo "</pre>";
            
            $ok = false;
            
        }
        
        if ($ok) {
            echo "<h2>Success!</h2>The installation is complete. Click <a href=''>here</a> to start using the application.";
        }

        echo "
</div>
</div>
";
        
        $this->writeFooter();

        
    }
    

    function main() 
    {
        if (!Checks::configFileExists()) {
            $action = param('action','view');
              
            switch ($action) {
            case 'db_check':
                $this->db_check();
                break;
                
            case 'install':
                $this->install();
                break;
                
            default:
                $this->view();
                break;
                
            }
            exit(0);
        }
    }

}

include "install.php";


?>