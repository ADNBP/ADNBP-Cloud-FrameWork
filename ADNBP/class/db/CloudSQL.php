<?php
/*
*  ADNBP Mysql Class
*  Feel free to use a distribute it. 
*/

class CloudSQLError extends Exception { 
    public function __construct() { 
        list( 
            $this->code, 
            $this->message, 
            $this->file, 
            $this->line) = func_get_args(); 
    } 
} 

// CloudSQL Class v10
if (!defined ("_MYSQLI_CLASS_") ) {
    define ("_MYSQLI_CLASS_", TRUE);
	
	class CloudSQL {
		
        // Base variables
        var $_error='';                                       // Holds the last error
        var $_lastRes=false;                                        // Holds the last result set
        var $_lastQuery='';                                        // Holds the last result set
        var $result;                                                // Holds the MySQL query result
        var $records;                                                // Holds the total number of records returned
        var $affected;                                        // Holds the total number of records affected
        var $rawResults;                                // Holds raw 'arrayed' results
        var $arrayedResult;                        // Holds an array of the result
        
        var $_db;
        var $_dbserver;        // MySQL Hostname
        var $_dbuser;        // MySQL Username
        var $_dbpassword;        // MySQL Password
        var $_dbdatabase;        // MySQL Database
        var $_dbsocket;        // MySQL Database
        var $_dbport = '3306';        // MySQL Database
        var $_dbtype = 'mysql';
        var $_limit = 1000;
                
        var $_dblink=false;                // Database Connection Link	
        
        Function CloudSQL ($h='',$u='',$p='',$db='',$port='3306',$socket='') {
            
            global $adnbp;
            
        	if(strlen($h)) {
        	    
        		$this->_dbserver = $h;
        		$this->_dbuser = $u;
        		$this->_dbpassword = $p;
        		$this->_dbdatabase = $db;
                $this->_port = $port;
                $this->_dbsocket = $socket;
                
        	}  else if(strlen( $adnbp->getConf("dbServer"))  || $adnbp->getConf("dbSocket")) {
        	    
                $this->_dbserver = $adnbp->getConf("dbServer");
                $this->_dbuser = $adnbp->getConf("dbUser");
                $this->_dbpassword = $adnbp->getConf("dbPassword");
                $this->_dbdatabase = $adnbp->getConf("dbName");
                $this->_dbsocket = $adnbp->getConf("dbSocket");
                
                if(strlen($adnbp->getConf("dbPort")))
                    $this->_dbport = $adnbp->getConf("dbPort");

            } 
            
            set_error_handler(create_function( 
                '$errno, $errstr, $errfile, $errline', 
                'throw new CloudSQLError($errno, $errstr, $errfile, $errline);' 
            ),E_WARNING); 
            
		}	
		
		function connect($h='',$u='',$p='',$db='',$port="3306",$socket='') {
		    
        	if(strlen($h)) {
        		$this->_dbserver = $h;
        		$this->_user = $u;
        		$this->_dbpassword = $p;
        		$this->_dbdatabase = $db;
                $this->_dbport = $port;
                $this->_dbsocket = $socket;
        	}
            
			if($this->_dblink)  $this->close();
            
			if(strlen($this->_dbserver) || strlen($this->_dbsocket)) {
			    try {
			    if(strlen($this->_dbsocket))
                    $this->_db = new mysqli(null, $this->_dbuser, $this->_dbpassword, $this->_dbdatabase, 0,$this->_dbsocket);
                else 
                    $this->_db = new mysqli($this->_dbserver, $this->_dbuser, $this->_dbpassword, $this->_dbdatabase, $this->_dbport);
				
                    if($this->_db->connect_error)  $this->setError('Connect Error to: '.$this->_dbserver.$this->_dbsocket.' (' . $this->_db->connect_errno . ') '. $mysqli->connect_error);
                    else $this->_dblink = true;
                    
                } catch (Exception $e) {
                    $this->setError('Connect Error to: '.$this->_dbserver.$this->_dbsocket.' (' . $this->_db->connect_errno . ') '. $mysqli->connect_error);
                }
                
			} else {
			    
				$this->setError("No DB server or DB name provided. ");
                
			}
			return($this->_dblink);
		}

		
        // It requires at least query argument
		function getDataFromQuery() {
		    $_q = $this->_buildQuery(func_get_args());
            if($this->error()) {
                return(false);
            } else {
                $ret=array();
                if( ($this->_lastRes = $this->_db->query($_q)) ) {
                    while ($fila = $this->_lastRes->fetch_array( MYSQL_ASSOC)) $ret[] = $fila;
                    if(is_object($this->_lastRes))
                       $this->_lastRes->close();
                    $this->_lastRes = false;
                } else {
                    $this->setError('Query Error [$q]: ' . $this->_db->error);
                }
                return($ret);                
            }
		}

        // It requires at least query argument
        function command() {
            $_q = $this->_buildQuery(func_get_args());
            if($this->error()) {
                return(false);
            } else {
                if( ($this->_lastRes = $this->_db->query($_q)) ) {
                    $_ok=true;
                    if(is_object($this->_lastRes))
                        $this->_lastRes->close();
                    $this->_lastRes = false;
                } else {
                    $_ok = false;
                    $this->setError('Query Error [$q]: ' .  $this->_db->error);
                }
                return($_ok);                
            }
        }
                
        // Scape Query arguments
        function _buildQuery($args) {
        	
			if(!$this->_dblink ) {
				$this->setError("No db connection");
				return false;
			}
            if(!is_array($args)) {
                $this->setError("_buildQuery requires an array");
                return(false);
            }

            
            $qreturn = "";
            
            $q = array_shift($args);
            
            if(!strlen($q)) {
                $this->setError("Function requires at least the query parameter");
                return(false);
            } else {
                $n_percentsS = substr_count($q,'%s');
                if(is_array($args[0]) && count($args)==1) {
                    $params = $args[0];
                    
                } else {
                    if(count($args)==1 && !strlen($args[0])) $params = array();
                    else $params = $args;
                }
                unset($args);
                
                if(count($params) != $n_percentsS) {
                    $this->setError("Number of %s doesn't count match with number of arguments");
                    return(false);
                } else {
                    if($n_percentsS == 0 ) $qreturn = $q;
                    else {
                        for($i=0;$i<$n_percentsS;$i++) $params[$i] = $this->_db->real_escape_string($params[$i]);
                        $qreturn = vsprintf($q, $params);
                    }
                }
            }
            
            $this->_lastQuery = $qreturn;
            return($qreturn);
        }
        
        function getQueryFromSearch ($search,$fields=false,$joints="=",$operators="AND") {
           $ret = '1=1'; 
           if( strlen($search) && $fields !== false )  {
               if(!is_array($fields)) $fields = explode(",", $fields);
               $q = $fields[0]." = '%s'";
               $data[] = $search;
               
               if(is_array($joints)) $last_join = array_shift($joints);
               else $last_join = $joints;
               
               if(is_array($operators)) $last_op = array_shift($operators);
               else $last_op = $operators;
               
               for ($i=1,$tr=count($fields); $i < $tr; $i++) {
                   $q .= " $last_op ".$fields[$i]." $last_join '%s'";
                   $data[] = $search;
                   
                   //looking if they have sent more operators
                   if(is_array($operators)) {
                       $op = array_shift($operators);
                       if(strlen($op)) $last_op = $op;
                   }
                   
                   //looking if they have sent more operators
                   if(is_array($joints)) {
                       $jo= array_shift($joints);
                       if(strlen($jo)) $last_join = $jo;
                   }                 
                   
               }  
               $ret = $this->_buildQuery(array($q,$data));
           }  
           return($ret);         
        }
        
		
		function close() {
			if($this->_dblink )  $this->_db->close();
			$this->_dblink = false;
		}
		
		function error() {return(strlen($this->_error)>0);}
		function getError() {return($this->_error);}
		function setError($err) {
		    if(strlen($this->_error)) $this->_error.="\n\n";
		    $this->_error.=$err;
            syslog(LOG_ERR, $err);
        }
		function setDB($db) {$this->_dbdatabase = $db;}
        function getQuery() {return( $this->_lastQuery);}
        
        function cloudFrameWork($action,$data,$table='',$order='',$selectFields='*') {
			
            if(!is_array($data)) {
                $this->setError("No fields in \$data in cloudFrameWork function.");
                return false;
            } else $allFields = array_keys($data);

            $_requireConnection = !$this->_dblink;
			if($_requireConnection) $this->connect();

            if($this->error()) return false;


            for($i=-1,$j=0,$tr2=count($allFields);$j<$tr2;$j++) {
                $field = $allFields[$j];
                
                // Tables finish en 's' allways
				if(strlen($table)) $tablename = $table;
				else {
	                list($tablename,$foo) = split("_",$field,2);
                    
                    // They are passing a field name
                    $_where = '';
                    if(strlen($foo)) $tablename.="s"; 
                    else {
                        // They are passing a table with a Where condition
                        $_where = $data[$tablename];
                        if($_where == '%') $_where = '1=1';

                    }
                    
                     // I add CF_ prefix to write in tables
                    if($action == 'insert' || $action == "replace" || $action == "getRecordsForEdit" ||  $action == "updateRecord")
	                    $tablename="CF_".$tablename;
				}
                
				
                if(!is_array($tables[$tablename])) {
                    $i++;                  
                    $tables[$tablename] = array();
                    $keys[$i][table] = $tablename;
                    $types = $this->getDataFromQuery("SHOW COLUMNS FROM %s",$keys[$i][table] ); //analyze types                      
                    if($this->error()) return(false);

                    //if($action=="updateRecord")
                    //    echo "<pre> fields to update ".print_r( $types,true)."</pre>";

                    
                    for($k=0,$tr3=count($types);$k<$tr3;$k++) {
                           $fieldTypes[$types[$k][Field]][type] = $types[$k][Type];
                           $fieldTypes[$types[$k][Field]][isNum] = (preg_match("/(int|numb|deci)/i", $types[$k][Type]));
                           $fieldTypes[$types[$k][Field]][isKey] = ($types[$k][Key]=="PRI");
                    }
                    
                    if(strpos($_where,"_anyfield=")!== false) {
                        list($_foo,$_search) = explode("_anyfield=", $_where,2);
                        $_where = $this->getQueryFromSearch("%$_search%", array_keys($fieldTypes),"LIKE","OR");
                    }                      
                }
                
                if(!strlen($_where) && !$fieldTypes[$field][type]) {
                    $this->setError("Wrong data array. $field doesn't exist in Cloud FrameWork.");
                    return(false);
                }
                
				
                $sep = ((strlen($tables[$tablename][insertFields]))?",":"");
                $and = ((strlen($tables[$tablename][selectWhere]))?" AND ":"");

                $tables[$tablename][insertFields] .= $sep.$field;
                $tables[$tablename][insertPercents] .= $sep.(($fieldTypes[$field][isNum])?"%s":"'%s'");
                
                if(strlen($data[$field]) && $data[$field] !='NULL')
                    $tables[$tablename][updateFields] .= $sep.$field."=".(($fieldTypes[$field][isNum])?"%s":"'%s'");
                else {
                    $data[$field] = 'NULL';
                    $tables[$tablename][updateFields] .= $sep.$field."=%s";
                }
                
                if($fieldTypes[$field][isKey]) {
                    if(strlen($tables[$tablename][updateWhereFields])) $tables[$tablename][updateWhereFields].=',';
                    $tables[$tablename][updateWhereFields] .= $field."=".(($fieldTypes[$field][isNum])?"%s":"'%s'");
                    $tables[$tablename][updateWhereValues][] = $data[$field];
                }
                
                if(strlen($_where)) {
                    $tables[$tablename][selectWhere] = $_where;
                } else {
                    $tables[$tablename][selectWhere] .= $and.$field."=".(($fieldTypes[$field][isNum])?"%s":"'%s'");
                    $tables[$tablename][values][] = $data[$field];
                }
            }
			

            foreach ($tables as $key => $value) {
                switch ($action) {
                    case 'insert':
                    case 'replace':
                        //echo($action." into $key (".$value[insertFields].") values  (".$value[insertPercents].")");
                        return($this->command($action." into $key (".$value[insertFields].") values  (".$value[insertPercents].")",$value[values]));
                        break;

                    case 'getRecords':
                    case 'getRecordsForEdit':
                    case 'getRecordsToExplore':
						if(!strlen($table)) $table = $key;
						if(strlen($order)) $order = " ORDER BY ".$order;
                        if($action == "getRecords") {
                            $_q = "select $selectFields from $table where ".$value[selectWhere].$order." limit ".$this->_limit;
                           return($this->getDataFromQuery($_q,$value[values]));
                        } else {
                            
                           if($action == "getRecordsForEdit") $this->_limit = 50;                           
                           $data = $this->getDataFromQuery("select $selectFields from $table where ".$value[selectWhere].$order." limit ".$this->_limit,$value[values]);
                           $_ret[fields] = array_keys($fieldTypes);
                           for($i=0,$tr=count($data);$i<$tr;$i++) 
                              $data[$i][_hash] = $this->getHashFromArray($data[$i]);

                           $_ret[data] = $data;
                           
                           unset($data);
                           // Eplore types
                           for($k=0,$tr3=count($types);$k<$tr3;$k++) {
                               $_ret[$types[$k][Field]][type] = "text";
                               
                               list($foo,$field,$rels) = explode("_", $types[$k][Field],3);
                               if($field=="Id" && $rels=="") 
                                   $_ret[$types[$k][Field]][type] = "key";
                               else if(strlen($rels)) {
                                   $_ret[$types[$k][Field]][type] = "rel";
                                   $reltable=$field."s";
                                   
                                   $relData = $this->cloudFrameWork("getRecords", array($reltable=>'%'),'','',$field.'_Id Id,'.$field.'_Name Name');                                   
                                   $_ret[$types[$k][Field]][relData] =$relData;
                               }
                           }
                           return($_ret);
                        }
                        break;
                    case 'updateRecord':
                        $_q = "UPDATE $key SET ".$tables[$tablename][updateFields]." WHERE ".$tables[$tablename][updateWhereFields];
                        $this->command($_q,array_merge($value[values],$value[updateWhereValues]));
                        
                        break;
                    default:
                        
                        break;
                }
                
            }
			if($_requireConnection) $this->close();
        }

        function getHashFromArray($arr) {
            return(md5(implode('', $arr)));
        }
	}
}
?>