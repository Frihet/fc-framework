<?php


function __autoload($name) {
    util::loadClass($name);
}

class util
{
    
	static $ci_html_title="";
	static $message_str="";
        static $redirect = false;
        static $path = "";
        
        function getPath()
        {
            return self::$path;
        }

	function starts_with($haystack,$needle,$case=true) {
	    if($case){return (strcmp(substr($haystack, 0, strlen($needle)),$needle)===0);}
	    return (strcasecmp(substr($haystack, 0, strlen($needle)),$needle)===0);
	}

	function ends_with($haystack,$needle,$case=true) {
	    if($case){return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);}
	    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
	}

        function array_get($arr, $key, $def)
        {
	    if (isset($arr[$key]))
	        return $arr[$key];
	    return $def;
        }

	function colorToHex($r, $g, $b) {
	    return '#' . str_pad(dechex($r), 2, "0", STR_PAD_LEFT) . str_pad(dechex($g), 2, "0", STR_PAD_LEFT) . str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
	}

	function arrayToSqlIn($col, $arr) {
	    $sql = 'true';
	    $params = array();
	    if ($arr != null && count($arr)) {
		$varnames = array();
		$arr[] = $arr[0];
		$idx = 0;
		foreach ($arr as $item) {
		    $varname = str_replace(".", "_", ":__{$col}_{$idx}");
		    $varnames[] = $varname;
		    $params[$varname] = $item;
		    $idx++;
		}
		$items = implode(', ', $varnames);
		$sql = "{$col} in ({$items})";
	    }
	    return array($sql, $params);
	}

        function loadClass($name) 
        {
            
            $dirs = array();
            if(param('plugin') !== null) {
                $dirs[] = 'plugins/'.param('plugin');
            }
            $dirs[] = ".";

            if(class_exists($name)) {
                return;
            }
            
            foreach(array('controller','view') as $type) {
                if(strcasecmp(substr($name, strlen($name)-strlen($type)),$type )==0) {
                    foreach($dirs as $dir) {
                        $path = $dir . "/{$type}s/{$name}.php";
                        if(file_exists($path)) {
                            include_once($path);
                            return;
                        }
                    }
                }
            }
                        
            if(strcasecmp(substr($name, strlen($name)-strlen("plugin")),"plugin" )==0) {
                $dir_name = substr($name, 0, strlen($name)-strlen("plugin"));
                include_once("plugins/{$dir_name}/index.php");
                return;
            }
            
	}
        
        function array_to_set($arr)
        {
            $res = array();
            foreach($arr as $val) {
                $res[$val] = true;
            }
            return $res;
        }
        
        function date_format($date) 
        {
            if( !$date)
		return "";
            
            return date('Y-m-d', $date) . "&nbsp;" . date('H:i',$date);
        }

        function printGzippedPage() 
        {
            $accepted_encodings= $_SERVER['HTTP_ACCEPT_ENCODING'];
            if( headers_sent() ){
                $encoding = false;
            } else if( strpos($accepted_encodings, 'x-gzip') !== false ) {
                $encoding = 'x-gzip';
            } else if( strpos($accepted_encodings,'gzip') !== false ) {
                $encoding = 'gzip';
            } else{
                $encoding = false;
            }
            
            if( $encoding ) {
                $contents = ob_get_contents();
                ob_end_clean();
                header('Content-Encoding: '.$encoding);
                print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
                $size = strlen($contents);
                $contents = gzcompress($contents, 1);
                $contents = substr($contents, 0, $size);
                print($contents);
                exit();
            } else {
                ob_end_flush();
                exit();
            }
        }
        
        function setTitle($str)
        {
            util::$ci_html_title = $str;
        }
        
        function getTitle()
        {
            return util::$ci_html_title;
        }

        function rmdir($path) {
            $path= rtrim($path, '/').'/';
            $handle = opendir($path);
            for (;false !== ($file = readdir($handle));)
                if($file != "." and $file != ".." ) {
                    $fullpath= $path.$file;
                    if( is_dir($fullpath) ) {
                        rmdir_recurse($fullpath);
                    } else {
                        unlink($fullpath);
                    }
                }
            closedir($handle);
            rmdir($path);
        } 

        function makePager($msg_count, $page_var='page') 
        {
            
            $current_page = param($page_var, 1);
            $item_count = Property::get('pager.itemsPerPage', 20);
            
            $pages = floor(($msg_count-1)/$item_count)+1;
            
            if ($pages > 1) {
                
                if($current_page != '1') {
                    $pager .= "<a href='".makeUrl($page_var, null)."'>&#x226a;</a>&nbsp;&nbsp;";
                    $pager .= "<a href='".makeUrl(array($page_var=>$current_page-1))."'>&lt;</a>&nbsp;&nbsp;";
                }
                else {
                    $pager .= "&#x226a;&nbsp;&nbsp;&lt;&nbsp;&nbsp;";
                }
                
                for( $i=1; $i <= $pages; $i++) {
                    if($i == $current_page) {
                        $pager .= "$i&nbsp;&nbsp;";
                    }
                    else {
                        $pager .= "<a href='".makeUrl(array($page_var=>$i))."'>$i</a>&nbsp;&nbsp;";
                    }
                    
                }
                
                if($current_page != $pages) {
                    $pager .= "<a href='".makeUrl(array($page_var=>$current_page+1))."'>&gt;</a>&nbsp;&nbsp;";
                    $pager .= "<a href='".makeUrl(array($page_var=>$pages))."'>&#x226b;</a>&nbsp;&nbsp;";
                }
                else {
                    $pager .= "&gt;&nbsp;&nbsp;&#x226b;&nbsp;&nbsp;";
                }
            }
            return $pager;
        }

        
        function redirect($page=null) 
        {
            util::$redirect = $page;
            
        }
        
        
        function doRedirect() 
        {
            if(util::$redirect === false) {
                return;
            }
            $page = util::$redirect;
            
            global $start_time;
            if (!$page) {
        $page = "?";
        
            }
            unset($_REQUEST['message_str']);
            
            $stop_time = microtime(true);
            $page .= strchr($page, '?')!==false?'&':'?';
            $page .= "redirect_render_time=" . sprintf("%.4f",$stop_time-$start_time);
            
            if (messageGet()) {
                $page .= "&message_str=" . urlEncode(messageGet()) ;
            }
            
            $page .= "&redirect_query_time=" . sprintf("%.4f",db::$query_time);
            $page .= "&redirect_query_count=" . db::$query_count;
            
            header("Location: $page");
            exit(0);
            
        }

	function calcDayInterval($day1, $day2) 
	{
		$year1 = date('Y', $day1);
		$month1 = date('m', $day1);
		$day1 = date('d', $day1);
		$tm1 = mktime(12, 0, 0, $month1, $day1, $year1);

		$year2 = date('Y', $day2);
		$month2 = date('m', $day2);
		$day2 = date('d', $day2);
		$tm2 = mktime(12, 0, 0, $month2, $day2, $year2);
		
		return round(($tm2-$tm1)/(24*3600));
		
	}

	function formatTime($tm)
	{
		if($tm % 60 == 0) 
		{
			return $tm / 60;
		}
		else if ($tm % 30 == 0) 
		{
                    return number_format((double)$tm/60, 1, ',','');
		}
		else
		{
			return floor($tm / 60). ':'. ($tm%60);
		}
		
	}
	
        function unformatTime($time) 
        {
            if(strstr($time, ':')) {
                $arr = explode(':',$time, 2);
                $minutes = $arr[1] + 60*$arr[0];
            }
            else {
                $time = str_replace(',','.',$time);
                $minutes = 60.0 * $time;
            }
            return $minutes;
        }
        
}

function sprint_r($var)
{
    ob_start();
    print_r($var);
    $res = ob_get_contents();
    ob_end_clean();
    return $res;
}

function stripslashesDeep($value)
{
    $value = is_array($value) ?
                array_map('stripslashesDeep', $value) :
                stripslashes($value);

    return $value;
}

function checkMagicQuotes() 
{
    if (get_magic_quotes_gpc()) {
        
        $_REQUEST = stripslashesDeep($_REQUEST);
        $_GET = stripslashesDeep($_GET);
        $_POST = stripslashesDeep($_POST);
    }
}


function htmlEncode($str,$qt=ENT_QUOTES) 
{
    return htmlEntities($str, $qt, 'UTF-8');
}

function param($name, $default=null) 
{
    if(array_key_exists($name, $_REQUEST)) {
        return $_REQUEST[$name];
    }
    return $default;
}

function error($str, $log=true) 
{
    if ($log)
        logMessage("Error: $str");
    
    $fmt = "<div class='error'>Error: ".htmlEncode($str)."</div>";
    util::$message_str .= $fmt;
}

function message($str, $log=true) 
{
    if(!is_string($str)){
        $str = sprint_r($str);
    }

    if ($log)
        logMessage($str);
    
    $fmt = "<div class='message'>".htmlEncode($str)."</div>";
    util::$message_str .= $fmt;
}

function messageGet()
{
    if (array_key_exists('message_str', $_REQUEST)) {
        return $_REQUEST['message_str'] . util::$message_str;
    }
    return util::$message_str;
}

function makeURLParamList($arr, $prefix = '') {
    $val = array();
    foreach($arr as $key => $value) {
        if ($prefix != '') {
	    $key = "{$prefix}[{$key}]";
        }
        if ($value !== null) {
	    if (is_array($value)) {
	        $val = array_merge($val, makeURLParamList($value, $key));
            } else {
                $val[] = urlEncode($key) . "=" . urlEncode($value);
	    }
        }
    }
    return $val;
}

function makeUrl($v1=null, $v2=null) 
{
    if(is_array($v1)) {
        $res = $v1;
        
    }
    else {
        if($v1===null) {
            $res = array();
        }
        else {
            $res = array($v1=>$v2);
        }
    }

    $strip = false;
    
    if(util::array_get($res, 'controller', null) != null) {
        $strip = true;
    }

    $filter = array( 'message_str'=>true, 'filter_column'=>true, 'filter_column_value'=>true, 'redirect_render_time'=>true, 'redirect_query_time'=>true, 'redirect_query_count'=>true);

    if (!$strip) {
        foreach($_GET as $key => $value) {
            if (array_key_exists($key, $filter)) {
                continue;
            }
            
            if (!array_key_exists($key, $res) ) {
                $res[$key] = $value;
            }
        }
    }
    
    $base = util::getPath();
    
    $controller = util::array_get($res, 'controller', null);
    $plugin = util::array_get($res, 'plugin', null);
    $id = util::array_get($res, 'id', null);
    $date = util::array_get($res, 'date', null);
    $task = util::array_get($res, 'task', null);
    $user = util::array_get($res, 'user', null);
    if(util::$path != "") {

        if ($plugin !== null) {
            $base .= 'plugins/' . urlEncode($plugin) . "/";
            $res['plugin']=null;

        }
        
        if( $controller !== null) {
            if ($id !== null) {
                if ($task !== null) {
                    
                    $res['task']=null;
                    $base .= urlEncode($controller)."/".urlEncode($id). "/".urlEncode($task);
                }
                else {
                    $base .= urlEncode($controller)."/".urlEncode($id);
                }
                
                $res['id']=null;
            }
            else if ($task !== null) {
                $base .= urlEncode($controller)."/".urlEncode($task);
                $res['task']=null;
            } else if ($date !== null) {
                if ($user !== null) {
                    
                    $res['user']=null;
                    $base .= urlEncode($controller)."/".urlEncode($date). "/".urlEncode($user);
                }
                else {
                    $base .= urlEncode($controller)."/".urlEncode($date);
                }
                $res['date']=null;
            } else {
                
                $base .= urlEncode($controller);                
            }
            
            $res['controller']=null;

            
        }
        else if ($date !== null) {
            if ($user !== null) {
                
                $res['user']=null;
                $base .= urlEncode($date). "/".urlEncode($user);
            }
            else {
                $base .= urlEncode($date);
            }
            $res['date']=null;
        }
        
    }
    
    $str = implode("&", makeURLParamList($res));
    
    if (strlen($str)==0) {
        return $base;
    }
    
    return $base . "?" . $str;
}

function makeLink($arr, $txt, $class=null, $mouseover=null, $attribute=array()) 
{
    $mouseover_str = "";
    $onclick_str = "";
    
    if ($mouseover) {
        $class .= " mouseoverowner";
        $mouseover_str = "<div class='onmouseover'>\n$mouseover\n</div>";
    }
        
    $attribute_str = "";
    foreach($attribute as $key => $value) {
        $attribute_str .= htmlEncode($key)."=\"".htmlEncode($value,ENT_COMPAT)."\"";
    }
    

    $class_str = $class?"class='$class'":"";

    if (is_array($arr)) {
        $arr = makeUrl($arr);
    }
    
    
    
    return "<a $class_str href='$arr'  $attribute_str>$mouseover_str" . htmlEncode($txt) . "</a>\n";
}


function makePopup($title, $label, $content, $class= null, $onmouseover=null, $id=null) 
{
    if( $id == null ) {
        global $popup_id;
        $popup_id++;
        $id = "popup_$popup_id";
    }
    
    return makeLink("javascript:popupShow(\"$id\");", $label, $class . " popupbutton", $onmouseover) ."
    <div class='anchor'>
    <div class='popup' id='$id'>
    <div class='popup_title'>
    $title
    <a href='javascript:popupHide(\"$id\")'>x</a>
    </div>
    <div class='popup_content'>
$content
    </div>
    </div>
    </div>
";
     
}

function logMessage()
{
    
}

function coalesce() {
    $args = func_get_args();
    foreach ($args as $arg) {
        if (!empty($arg)) {
            return $arg;
        }
    }
    return $args[0];
}

?>