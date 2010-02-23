<?php

/**
 Base class for all controllers. 

 A controller should always be usade by first constructing it and then
 calling the run method. This method will dispatch the correct
 controller code by checking what parameters it was sent.
 
 Specifically, run will check the value of the $_REQUEST parameter
 task, and check if a method exists named in the same way, but with a
 'Run' suffix exists. For example, to implement a 'save' task, a
 developer needs to implement a 'saveRun' method.

*/

class Controller
{

    private $extra_content=array();
    private $application;

    function __construct($app) 
    {
        $this->application = $app;
    }
    
    function getApplication()
    {
        return $this->application;
    }

    function preRun()
    {
    }
    
    function postRun()
    {
    }
    
    
    /** Check the task param and try to run the corresponding
     function, if it exists. Gives an error otherwise.
    */
    function run() 
    {
        $task = param('task','view');

        $class_name = get_class($this);
        $this->preRun();
        
        $str = "{$task}Run";
        
        if(method_exists($this, $str)) {
            $this->$str();
        }
        else {
            error("Unknown task: $task");
        }
        $this->postRun();
    }
    
    /**
     Output a correctly formated action menu, given a set of links as input.
    */
    function actionMenu($link_list) 
    {
        if($link_list === null) {
            return;
        }
        
        echo "<div class='action_menu no_print'>\n";
        echo implode("",$this->getContent("action_menu_pre"));

	if( count($link_list)) {
	    echo "<ul>\n";
            echo  "<li><h2>"._("Actions")."</h2></li>\n";
            
            foreach($link_list as $link) {
                
                echo "<li>";
                echo $link;
                echo "</li>\n";
            }
	    echo "</ul>\n";
        }

	$box = $this->actionBox();        
	if ($box !== "")
	    echo "<ul>" . $box . "</ul>";

        echo implode("",$this->getContent("action_menu_post"));
        echo "</div>\n";
					
    }

    /** A function to output the basic page layout given a set of menu
     items and content for the main pane.
    */
    function show($action_menu, $content)
    {
        $this->actionMenu($action_menu);

        echo "<div class='content'>";
        echo "<div class='content_inner'>";

        echo "<h1>" . htmlEncode(util::getTitle()). "</h1>";
	
        echo implode("",$this->getContent("content_pre"));
        
        echo $content;
        
        echo implode("",$this->getContent("content_post"));
	
        echo "<div class='content_post'>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    /** Create a little box with misc information for the botton of
     the action menu. Currently, this box only contains the ten last
     edited CIs.
     */
    function actionBox() 
    {
	    return "";
    }
    
    function isAdmin() 
    {
	    return false;
    }
    
    function isHelp() 
    {
	    return false;
    }

	/** Check if a view with the specified name exists. If no ,try to
	 autoload it. Recheck, and render if it possible, and exit with an
	 error otherwise.
	 */
	function render($view) 
	{
            $view_name = "{$view}View";
            
            if(class_exists($view_name)) {
                $v = new $view_name();
                $v->render($this);
            } else {
                header("Status: 404");
                echo "View $view_name not found!";
                exit(0);
            }
	}

	function addContent($position, $content) 
	{
		$this->extra_content[$position][]= $content;
	}

	function getContent($position)
	{
            return (is_array($this->extra_content) && array_key_exists($position, $this->extra_content))?$this->extra_content[$position]:array();
	}
	
	
}

?>