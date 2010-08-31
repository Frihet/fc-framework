<?php

class dbItem
{

    function __construct($param=null) 
    {
        
        if($param === null) {
            return;
        }
        
        if ((int)$param == $param) {
            $this->load($param);
        }
        else if (is_array($param)) {
            $this->initFromArray($param);
        }
    }

    /**
     * Returns an array of all public properties of this object
     * type. By convention, this is exactly the same as the list of
     * fields in the database, and also the same thing as all fields
     * whose name does not begin with an underscore.
     */
    function getPublicProperties() {
        static $cache = null;
        if (is_null( $cache )) {
            $cache = array();
            foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
                if (substr( $key, 0, 1 ) != '_') {
                    $cache[] = $key;
                }
            }
        }
        return $cache;
    }

    function initFromArray($arr)
    {
        $count = 0;
        if ($arr) {
            foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
                if (array_key_exists($key, $arr)) {
                    $this->$key = $arr[$key];
                    $count ++;
                }
            }
        }
        
        return $count;
        
    }
    
    function find($col_name, $col_value, $class_name, $table_name) 
    {
        $res = new $class_name();
        $data = db::fetchRow("select * from $table_name where $col_name=:value",
                             array(':value'=>$col_value));
                
        if (!$data) {
            return null;
        }
        $res->initFromArray($data);
        
        return $res;
    }

    function hasSoftDelete()
    {
        return false;
    }
    

    function findAll($class_name=null, $table_name=null) 
    {
        $where = "";
        
        if(isset($this)){
            $table_name = $this->table;
            $class_name = get_class($this);

            if($this->hasSoftDelete()) {
                $where = "where deleted=false";
            }
        }
        
        $res=array();
                        
        foreach(db::fetchList("select * from $table_name $where") as $row) {
            $res[] = new $class_name($row);
        }
        return $res;
    }

    function save($key='id') 
    {
        return $this->saveInternal($key);
    }
    
    function saveInternal($key='id') 
    {
        $prop = $this->getPublicProperties();
        $param_name=array();
        $param=array();
        $idx = 1;
        
        if ($key !== null && $this->$key !== null) {
            
            foreach($prop as $p) {
                if ($p != $key) {
                    if($this->$p === null) {
                        $param_name[] = "$p = null";
                    } else if($this->$p === false) {
                        $param_name[] = "$p = false";
                    } else if($this->$p === true) {
                        $param_name[] = "$p = true";
                    } else {
                        $nam = ":prop" . $idx;
                        $param_name[] = "$p = $nam";
                        $param[$nam] = $this->$p;
                        $idx++;
                    }
                }
                
            }
            
            $query = "update ".$this->table." set ".implode(', ', $param_name). " where $key = :key";
            $param[':key'] = $this->$key;
            return db::query($query, $param);
        }
        else {
            $param_def = array();
            
            foreach($prop as $p) {
                if($key !== null && $p == $key) {
                    continue;
                }
                if($this->$p === null) {
                    $param_def[] = $p;
                    $param_name[] = "null";
		} else if($this->$p === false) {
                    $param_def[] = $p;
                    $param_name[] = "false";
		} else if($this->$p === true) {
                    $param_def[] = $p;
                    $param_name[] = "true";
                } else {
                    $param_def[] = $p;
                    $nam = ":prop" . $idx;
                    $param_name[] = $nam;
                    $idx++;
                    $param[$nam] = $this->$p;
                }
            }
            
            $query = "insert into ".$this->table." (" . implode(', ', $param_def) . ") values (" . implode(', ', $param_name).")";
            $res = db::query($query, $param);
            if($res)
                $this->$key = db::lastInsertId($this->table . "_" . $key . "_seq");
            return !!$res;
        }
    }

    function removeInternal($key='id') 
    {
        return db::query("delete from ".$this->table." where $key = :key", array(':key'=>$this->$key));    }

    function load($key_value, $key='id') 
    {        
        $this->initFromArray(db::fetchRow("
select *
from ".$this->table."
where $key = :value", array(":value"=>$key_value)));

    }
    
            
}

?>