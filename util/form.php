<?php
  /**
   Helper functions for making forms.
   */

class form
{
    static $iframe_id=0;

    function makeSelectList($arr, $val_field, $desc_field)
    {
	$res=array();
	foreach($arr as $it) 
	{
	    $res[$it->$val_field] = $it->$desc_field;
	}
	return $res;
    }
    

    /**
     Create a select box with the specified values.
     */
    function makeSelect($name, $values, $selected=null, $id=null, $attributes=array()) {
        
        $id_str = $id?'id="'.htmlEncode($id).'"':'';
        $multiple_str = "";
        if( is_array($selected)) {
            $multiple_str = "multiple";
            $name = "{$name}[]";
        }
                
        $select='<select '.$multiple_str.' name="'.htmlEncode($name).'" '.$id_str. " ";

        foreach($attributes as $key => $value) {
            $val = htmlEncode($value);
            $select .= "$key='$val' ";
        }
        $select .= ">";
                
        if ($values!=null) {
            
            foreach($values as $num => $val) {
                if(is_object($val)) {
                    $id = $val->getId();
                    $name = $val->getDescription();
                } else {
                    $id = $num;
                    $name = $val;
                }
                
                $selected_str = "";
                
                if( is_array($selected)) {
                    if (in_array($id, $selected)) {
                        $selected_str = 'selected';
                    }
                } else {
                    if ($id == $selected) {
                        $selected_str = 'selected';
                    }
                }
                
                $select .= "<option $selected_str value='".htmlEncode($id)."'>". htmlEncode($name)."</option>\n";
            }
        }
        
        $select .= "</select>";
        return $select;
        
    }

    function makeButton($content, $name, $value, $type='submit', $id=null, $class=null) 
    {
            $id_str = $id?'id="'.htmlEncode($id).'"':'';
            $class_str = $class?'class="'.htmlEncode($class).'"':'';
            return "<button $id_str $class_str name='".htmlEncode($name)."' value='".htmlEncode($value)."' type='".htmlEncode($type)."'>".$content."</button>";		
    }
    

    function makeText($name, $value, $id=null, $class=null, $attributes = array()) 
    {
        $attr = '';
        foreach($attributes as $key => $val) {
            $val = htmlEncode($val);
            $attr .= "$key='$val' ";
        }

        $id_str = $id?'id="'.htmlEncode($id).'"':'';
        $class_str = $class?'class="'.htmlEncode($class).'"':'';
        return "<input type='text' $id_str $class_str size='16' name='".htmlEncode($name)."' value='".htmlEncode($value)."' {$attr} />\n";		
    }
    
    function makeColorSelector($name, $value, $id=null, $class=null) 
    {
        $id_str = $id?'id="'.htmlEncode($id).'"':'';
	if ($class == null) $class = "";
        $class_str = 'class="colorpickerinput '.htmlEncode($class).'"';
        return "<input type='text' $id_str $class_str size='16' name='".htmlEncode($name)."' value='".htmlEncode($value)."' style='background-color: ".htmlEncode($value).";' />\n";
    }
    
    function makePassword($name, $value, $id=null, $class=null) 
    {
        $id_str = $id?'id="'.htmlEncode($id).'"':'';
        $class_str = $class?'class="'.htmlEncode($class).'"':'';
        return "<input type='password' $id_str $class_str size='16' name='".htmlEncode($name)."' value='".htmlEncode($value)."'/>\n";		
    }

    function makeCheckbox($name, $value, $description, $id=null, $return_value = 'f', $attributes=array()) 
    {
        if($id === null) {
            $id = $name;
        }
	$checked = '';
        if($value===true || $value=='1' || $value=='t') {
            $checked = 'checked="yes"';
        }
        
        $attr = '';
        foreach($attributes as $key => $value) {
            $val = htmlEncode($value);
            $attr .= "$key='$val' ";
        }

        return "<input class='checkbox' type='hidden' name='".htmlEncode($name)."' value='f'><input type='checkbox' name='".htmlEncode($name)."' id='".htmlEncode($id)."' value='t' $checked $attr /><label for='".htmlEncode($id)."'>".htmlEncode($description)."</label>";
    }
    
    function makeListCheckbox($name, $value, $checked, $description, $id=null) 
    {
        if($id === null) {
            $id = $name;
        }

	$id = htmlEncode($id);
	$name = htmlEncode($name . "[]");
	$description = htmlEncode($description);
	$value = htmlEncode($value);

	$checked_html = "";
        if($checked) {
            $checked_html = "checked='yes'";
        }
	$description_html = "";
	if ($description) {
     	     $description_html = "<label for='{$id}'>{$description}</label>";
	}
        
        return "<input type='checkbox' name='{$name}' id='{$id}' value='{$value}' {$checked_html} />{$description_html}";
    }
    
    function makeFile($name, $id=null)
    {
        $id_str = $id?'id="'.htmlEncode($id).'"':'';
        return "<input type='file' name='".htmlEncode($name)."' $id_str>";
    }
    

    function makeForm($content, $hidden=array(),$method='post', $file_upload=false)
    {
        $enc = $file_upload?"enctype='multipart/form-data'":"";
        
        $path = "";
        if(util::$path != "") {
            $path = util::$path;
        }
        

        $form = "<form accept-charset='utf-8' method='$method' action='$path' $enc>\n";
        foreach($hidden as $name => $value) {
	    if (is_array($value)) {
	        foreach ($value as $part) {
		    $form .= "<input type='hidden' name='".htmlEncode($name)."[]' value='".htmlEncode($part)."'>\n";
		}
	    } else {
	        $form .= "<input type='hidden' name='".htmlEncode($name)."' value='".htmlEncode($value)."'>\n";
            }
        }
        
        $form .= $content;
        $form .= "</form>\n";
        return $form;
    }

}

?>