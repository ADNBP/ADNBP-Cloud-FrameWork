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
	
    
    class CloudSQLQueryObject {
        
        var $data = array();
        var $table = '';
        var $selectFields = '';
        var $order = '';
        var $extraWhere = '';
        
        function CloudSQLQueryObject ($data,$table='') {
            $this->data = $data;
            $this->table = $table;
        }
    }
    
	class CloudSQL {
		
        // Base variables
        var $_error='';                                       // Holds the last error
        var $_lastRes=false;                                        // Holds the last result set
        var $_lastQuery='';                                        // Holds the last result set
        var $_lastInsertId='';                                        // Holds the last result set
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
        var $_qObject = array();
        var $_cloudDependences = array();
		var $_cloudReferalFields = array();
		var $_cloudAutoSelectFields = array();
		var $_cloudWhereFields = array();
		var $_cloudFilterWhereFields = array();
		var $_queryFieldTypes = array();
                
        var $_dblink=false;                // Database Connection Link	
        var $_debug=false;
        
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

		function setConf($var,$value) {
			switch ($var) {
				case 'dbServer':$this->_dbserver = $value; break;
				case 'dbUer':$this->_dbuser = $value; break;
				case 'dbPassword':$this->_dbpassword = $value; break;
				case 'dbName':$this->_dbdatabase = $value; break;
				case 'dbSocket':$this->_dbsocket = $value; break;
				case 'dbPort':$this->_dbport = $value; break;
				default:
					$this->setError('Unknown "confVar". Please use: dbServer, dbUer, dbPassword, dbName, dbSocket, dbPort');
					break;
			}
		}
		
		function getConf($var) {
			$ret ='';
			switch ($var) {
				case 'dbServer':$ret = $this->_dbserver; break;
				case 'dbUer':$ret = $this->_dbuser; break;
				case 'dbPassword':$ret = $this->_dbpassword; break;
				case 'dbName':$ret = $this->_dbdatabase; break;
				case 'dbSocket':$ret = $this->_dbsocket; break;
				case 'dbPort':$ret = $this->_dbport; break;
				default:
					$ret = 'Unknown "confVar". Please use: dbServer, dbUer, dbPassword, dbName, dbSocket, dbPort';
					break;
			}
			return($ret);
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
			$this->_dblink = false;
            
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
				
				if($this->_debug) _print($_q);
				
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
            	
            	if($this->_debug) _print($_q);
                if( ($this->_lastRes = $this->_db->query($_q)) ) {
                    $_ok=true;
                    $this->_lastInsertId = $this->_db->insert_id;
                    if(is_object($this->_lastRes)) {
                        $this->_lastRes->close();
                    }
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
                    $this->setError("Number of %s doesn't count match with number of arguments. Query: $q -> ".print_r($params,true));
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
               $q = $fields[0]." $joints '%s'";
               $data[] = $search;
               
               if(is_array($joints)) $last_join = array_shift($joints);
               else $last_join = $joints;
               
               if(is_array($operators)) $last_op = array_shift($operators);
               else $last_op = $operators;
               
               for ($i=1,$tr=count($fields); $i < $tr; $i++) if(strlen($fields[$i])) {
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
			if($this->_debug) _print($err);
			
		    if(strlen($this->_error)) $this->_error.="\n\n";
		    $this->_error.=$err;
            syslog(LOG_ERR, $err);
        }
		function setDB($db) {$this->_dbdatabase = $db;}
        function getQuery() {return( $this->_lastQuery);}
        function getInsertId() {return( $this->_lastInsertId);}
        
        
        /*
         *  OBJECT QUERIES
         */
         
        function initQueryObject($id,$data=array(),$table='') {
            $this->_qObject[$id][data] = $data;
            $this->_qObject[$id][table] = $table;
        }

        function getQueryObject($id) {$this->_qObject[$id];}
        
        function setQueryObjectSelectFields($id,$value) {
            if(is_array($value)) $value = implode(",",$value);
            $this->_qObject[$id][selectFields] = $value;
        }
        
        function setQueryObjectOrder($id,$value) { $this->_qObject[$id][order] = $value; }
        function setQueryObjectTable($id,$value) { $this->_qObject[$id][table] = $value; }
        function setQueryObjectData($id,$value) { $this->_qObject[$id][data] = $value;  }       
         
        function setQueryObjectWhere($id,$q,$v='') {
            $this->_qObject[$id][where] = array();
            $this->addQueryObjectWhere($id,$q,$v);
        }
        
        function addQueryObjectWhere($id,$q,$v='') { $this->_qObject[$id][where][] = $q; }
		
        function addFieldDependence($field,$dependence) { $this->_cloudDependences[$field] .= $dependence; }
        function setFieldDependence($field,$dependence) {
        	unset($this->_cloudDependences[$field]);
			$this->addFieldDependence($field,$dependence);
		}
		function getFieldDependence($field) {
			if(is_string($this->_cloudDependences[$field])) return $this->_cloudDependences[$field];
			else return(false);
		}
		
        function addReferalField($field,$referal) { $this->_cloudReferalFields[$field] .= $referal; }
        function setReferalField($field,$referal) {
        	unset($this->_cloudReferalFields[$field]);
			$this->addReferalField($field,$referal);
		}
		function getReferalField($field) {
				if( isset($this->_cloudReferalFields[$field]) && strlen($this->_cloudReferalFields[$field])) return $this->_cloudReferalFields[$field];
			else return(false);
		}
		
        function setAutoSelectField($field) { $this->_cloudAutoSelectFields[$field]= true; }
        function unsetAutoSelectField($field) { unset($this->_cloudAutoSelectFields[$field]);}
		function isAutoSelectField($field) {
			if( isset($this->_cloudAutoSelectFields[$field]) && $this->_cloudAutoSelectFields[$field]) return true;
			else return(false);
		}

        function addWhereField($field,$where) { $this->_cloudWhereFields[$field] .= $where; }
        function setWhereField($field,$where) {
        	unset($this->_cloudWhereFields[$field]);
			$this->addWhereField($field,$where);
		}
		function getWhereField($field) {
			if(is_string($this->_cloudWhereFields[$field])) return $this->_cloudWhereFields[$field];
			else return(false);
		}		
		
        function addFilterWhereField($field,$where) {
        	 if(!strlen(trim($where))) return;
			 
        	 if(strlen(trim($this->_cloudFilterWhereFields[$field]))) $this->_cloudFilterWhereFields[$field] .= ' AND '; 
        	 $this->_cloudFilterWhereFields[$field] .= $where; 
		}
        function setFilterWhereField($field,$where) {
        	if(strlen(trim($where))) {
	        	unset($this->_clouFilterWhereFields[$field]);
				$this->addFilterWhereField($field,$where);
			}
		}
		function getFilterWhereField($field) {
			if(is_string($this->_cloudFilterWhereFields[$field])) return $this->_cloudFilterWhereFields[$field];
			else return(false);
		}		



		function getSecuredSqlString($ret) {
			$ret = str_ireplace("delete ", '', $ret);			
			$ret = str_ireplace(";", '', $ret);			
			$ret = str_ireplace("insert ", '', $ret);
			$ret = str_ireplace("from  ", '', $ret);
			$ret = str_ireplace("replace  ", '', $ret);
			$ret = str_ireplace("truncate  ", '', $ret);
			$ret = str_ireplace("truncate  ", '', $ret);
			return($ret);
						
		}
 
                    
        function cloudFrameWork($action,$data='',$table='',$order='',$selectFields='*',$page=0) {
        	
			if(!strlen($selectFields)) $selectFields='*';
			if(!is_numeric($page)) $page=0;
			
            // Analyze de possibles params
            if(is_string($data) && is_array($this->_qObject[$data][data])) {
            	
                $id = $data;
                $data = $this->_qObject[$id][data];
                $table = $this->_qObject[$id][table];
                $order = $this->_qObject[$id][order];
                $page = $this->_qObject[$id][page];
                $selectFields = $this->_qObject[$id][selectFields];
				
            }
            
            if(!is_array($data) && !strlen($table)) {
                $this->setError("No fields in \$data in cloudFrameWork function.");
                return false;
            } elseif(is_array($data)) {
                $allFields = array_keys($data);
            } 

            //verify we have a db connection ready
            $_requireConnection = !$this->_dblink;
			if($_requireConnection) $this->connect();
            if($this->error()) return false;
            
            // figuring out the table to work with
            $_where = '';
            if(!strlen($table)) {
                list($table,$foo) = split("_",$allFields[0],2);
                
                if(!strlen($foo) && count($allFields) > 1) {
                	
                    $this->setError("I can not figure out the name of the table to query.");
                    return false;                    
                } else if(strlen($foo)) {
                    $table.="s";
                    $_q = "SELECT count(*) TOT FROM INFORMATION_SCHEMA.TABLES t WHERE t.TABLE_SCHEMA='%s' AND TABLE_NAME = '%s' ";
                    $tmp = $this->getDataFromQuery($_q,$this->_dbdatabase,$table );
                    if($tmp[0][TOT]==0) $table = $allFields[0];
                } 
            } 
			
            $_tableInFirstField = false;
            if( count($allFields) == 1 && $allFields[0] == $table ) {
                $_where = $data[$table];
                $_tableInFirstField =true;
            }
            
            if(strpos($table, "Rel_") !== false) 
               $_relTable = true;
            
            if($action == 'insert' || $action == "replace" || $action == 'insertRecord' || $action == "replaceRecord" || $action == "getRecordsForEdit" ||  $action == "updateRecord" || $action =="getFieldTypes")
                $table ="CF_".$table;

            // Field Types of the table
            if(!isset($this->_queryFieldTypes[$table])) $this->_queryFieldTypes[$table] = $this->getDataFromQuery("SHOW COLUMNS FROM %s",$table );
            if($this->error()) return(false);
            $types = $this->_queryFieldTypes[$table];                     
            
            for($k=0,$tr3=count($types);$k<$tr3;$k++) {
                   $fieldTypes[$types[$k][Field]][type] = $types[$k][Type];
                   $fieldTypes[$types[$k][Field]][isNum] = (preg_match("/(int|numb|deci)/i", $types[$k][Type]));
                   $fieldTypes[$types[$k][Field]][isKey] = ($types[$k][Key]=="PRI");
            }  
            
            // analyze if the Where has _anyfield
            if(strpos($_where,"_anyfield=")!== false) {
                
                list($_foo,$_search) = explode("_anyfield=", $_where,2);
                $_where = "(".$this->getQueryFromSearch("%$_search%", array_keys($fieldTypes),"LIKE","OR").")";
            }
            if($_where == '%') $_where = '1=1'; 
            
            if(strlen($_where)) $tables[$table][selectWhere] = $_where;
              
                               
            $tables[$table][init] = 1;
            
            if(!$_tableInFirstField)
            for($i=-1,$j=0,$tr2=count($allFields);$j<$tr2;$j++) if($allFields[$j] != $table) {
                
                $field = $allFields[$j];
                
                if(!$fieldTypes[$field][type]) {
                    $this->setError("Wrong data array. $field doesn't exist in ".$keys[$i][table]);
                    return(false);
                }
				
                $sep = ((strlen($tables[$table][insertFields]))?",":"");
                $and = ((strlen($tables[$table][selectWhere]))?" AND ":"");

                
                if(strlen($data[$field]) && $data[$field] !='NULL')
                    $tables[$table][updateFields] .= $sep.$field."=".(($fieldTypes[$field][isNum])?"%s":"'%s'");
                else {
                    $data[$field] = 'NULL';
                    $tables[$table][updateFields] .= $sep.$field."=%s";
                }
				
                $tables[$table][insertFields] .= $sep.$field;
                $tables[$table][insertPercents] .= $sep.(($fieldTypes[$field][isNum])?"%s":(($data[$field] == 'NULL')?"%s":"'%s'"));
                
                if($fieldTypes[$field][isKey]) {
                    if(strlen($tables[$table][updateWhereFields])) $tables[$table][updateWhereFields].=',';
                    $tables[$table][updateWhereFields] .= $field."=".(($fieldTypes[$field][isNum])?"%s":"'%s'");
                    $tables[$table][updateWhereValues][] = $data[$field];
                }
                
                if($data[$field] !='%') {
                    if($data[$field]=="_empty_") {
                        $tables[$table][selectWhere] .= $and." ($field IS NULL OR LENGTH($field)=0) ";
                    } else if($data[$field]=="_noempty_") {
                        $tables[$table][selectWhere] .= $and." ($field IS NOT NULL AND LENGTH($field)>0) ";
                    } else {
    					$joint = ' = ';
    					$_selecWhereFieldError = false;
    					if(strpos($data[$field], '%')!==false) $joint = ' LIKE ';
    					else if($fieldTypes[$field][isNum]) {
    						if(!is_numeric(trim($data[$field]))) {
    							$joint=' ';
    						}
    					}
                        
                        if(!$_selecWhereFieldError ) {
                            $tables[$table][selectWhere] .= $and.$field.$joint.(($fieldTypes[$field][isNum])?"%s":"'%s'");
                            $tables[$table][values][] = $data[$field];
                        }
                    }
                }
            }
			

            foreach ($tables as $key => $value) {
                switch ($action) {
                    case 'getFieldTypes':
                        return($fieldTypes);
                        break;
                    case 'getObjectFields':
                        $_infields = "'".implode("','",array_keys($fieldTypes))."'";
                        $_q = "SELECT DirectoryObjectField_Name, DirectoryObjectField_DefaultName FROM DirectoryObjectFields WHERE DirectoryObjectField_Name IN ($_infields)";
                        $_f = $this->getDataFromQuery($_q);
                        if($this->error()) return false;
                        
                        $_ret = array();
                        for ($i=0,$tr=count($_f); $i <  $tr; $i++) { 
                            $_ret[$_f[$i][DirectoryObjectField_Name]] = $_f[$i][DirectoryObjectField_DefaultName];
                        }
                        unset($_f);
                        return($_ret);
                        break;
                    case 'insertRecord':
                    case 'insert':
                    case 'replaceRecord':
                    case 'replace':
						if($action == 'insertRecord' || $action == 'insert') $act = "insert";
						else $act = 'replace';
                        //echo($action." into $key (".$value[insertFields].") values  (".$value[insertPercents].")");
                        return($this->command($act." into $key (".$value[insertFields].") values  (".$value[insertPercents].")",$value[values]));
                        break;

                    case 'getRecords':
                    case 'getDistinctRecords':
                    case 'getRecordsForEdit':
                    case 'getRecordsToExplore':
						
                        if(!strlen($value[selectWhere])) $value[selectWhere] = "1=1";
                        
						if(!strlen($table)) $table = $key;
						if(strlen($order)) $order = " ORDER BY ".$order;
                        if($action == "getRecords") {
                            $_q = "select $selectFields from $table main where ".$value[selectWhere].$order." limit ".$this->_limit;
                           return($this->getDataFromQuery($_q,$value[values]));
                           
                        } else if($action == "getDistinctRecords") {
                            $_q = "select distinct $selectFields from $table main where ".$value[selectWhere].$order." limit ".$this->_limit;
                           return($this->getDataFromQuery($_q,$value[values]));
                           
                        } else {

                           // Eplore types
                           for($k=0,$tr3=count($types);$k<$tr3;$k++) {
                           	   	
                           	   if(preg_match("/(int|numb|deci)/i", $types[$k][Type]))
                                   $_ret[$types[$k][Field]][type] = 'text';
							   else if(preg_match("/(text)/i", $types[$k][Type]))
							       $_ret[$types[$k][Field]][type] = 'textarea';
							   else
							   	   $_ret[$types[$k][Field]][type] = 'text';
                               
                               list($foo,$field,$rels) = explode("_", $types[$k][Field],3);
                               
                               if(($field=="Id" && $rels=="" && !$_relTable) || ($_relTable && $foo=="Id")) 
                                   $_ret[$types[$k][Field]][type] = "key";
                               else if(strlen($rels) || ($_relTable && strlen($field))) {
                               	
							   // Getting Rel data to this field                               	
                                   $_ret[$types[$k][Field]][type] = "rel";
								   
                                   if($_relTable) {
                                       $reltable=$foo."s";
									   $_f= $foo;
                                   } else {
                                       $reltable=$field."s";
                                   	   $_f= $field;
                                   }
								   
								   // Fields dependences and WhereConditions
								   $_fqWhere = '';
								   if(($dependences = $this->getFieldDependence($types[$k][Field])) !== false)  $_fqWhere .=  ' ('.$dependences.')';
								   
								   if(($fieldwheres = $this->getWhereField($types[$k][Field])) !== false) {
								   	if(strlen($_fqWhere)) $_fqWhere .= ' AND ';
								   	$_fqWhere .=  ' ('.$fieldwheres.')';
								   }

								   if(($fieldwheres = $this->getFilterWhereField($types[$k][Field])) !== false) {
								   	if(strlen($_fqWhere)) $_fqWhere .= ' AND ';
								   	$_fqWhere .=  ' ('.$fieldwheres.')';
								   }
								   
								   
								   $_fn = 'R.'.$_f.'_Id Id,'.(($this->getReferalField($types[$k][Field]) !==false )?"CONCAT_WS(' - ',R.".$this->getReferalField($types[$k][Field]).') Name':'R.'.$_f.'_Name Name');
								   if(!strlen($_fqWhere )) $_fqWhere .=  '1=1';
								   $_fq = " SELECT DISTINCT $_fn FROM  $reltable R LEFT JOIN  $table P ON (R.".$_f."_Id = P.".$types[$k][Field].") WHERE $_fqWhere ";
								   
								   
                                   $relData = $this->getDataFromQuery($_fq); 
								   if($this->error()) return false;
                                   $_ret[$types[$k][Field]][relData] =$relData;
								   
                               } else if($this->isAutoSelectField($types[$k][Field])) {
                               	   $_fqWhere = '';
								   if(($dependences = $this->getFieldDependence($types[$k][Field])) !== false)  $_fqWhere .=  ' ('.$dependences.')';
                               	   $_fn = $types[$k][Field].' AS Id,'.$types[$k][Field].' AS Name';
                               	   if(!strlen($_fqWhere )) $_fqWhere .=  '1=1';
                               	   
                               	   $_fq = " SELECT DISTINCT $_fn FROM  $table  WHERE $_fqWhere ";
                                   $relData = $this->getDataFromQuery($_fq); 
								   if($this->error()) return false;
                                   $_ret[$types[$k][Field]][relData] =$relData;
                               	
                               }
	
	                           // add where to Global Query: 
							   if(($fieldwheres = $this->getWhereField($types[$k][Field])) !== false) {
									$value[selectWhere] .= ' AND   ('.$fieldwheres.')';
							   }	
    
	                       } 
						   // Let see how many rows it has
                           $nrows = $this->getDataFromQuery("select count(1) TOT from $table main where ".$value[selectWhere],$value[values]);
						   if($this->error()) return false;
					 	   $_ret[totRows] = $nrows[0][TOT];
						 
						   
                           if($action == "getRecordsForEdit") $this->_limit = 50;  
						   
					 	   $_ret[totPages] = round($nrows[0][TOT]/$this->_limit,0);
					 	   if($_ret[totPages]*$this->_limit < $nrows[0][TOT]) $_ret[totPages]++;
						   
						   if($page >= $_ret[totPages] ) $page = $_ret[totPages]-1 ;
						   if($page < 0 ) $page=0;
						   
						   $_ret[currentPage] = $page;
						   $_ret[totRowsInPage] = ($this->_limit < $_ret[totRows])?$this->_limit:$_ret[totRows];
						   $_ret[offset] = $page * $this->_limit;
						                            
                           $data = $this->getDataFromQuery("select $selectFields from $table main where ".$value[selectWhere].$order." limit ".$_ret[offset].','.$this->_limit,$value[values]);
						   if($this->error()) return false;
                           $_ret[fields] = array_keys($fieldTypes);
                           for($i=0,$tr=count($data);$i<$tr;$i++) 
                              $data[$i][_hash] = $this->getHashFromArray($data[$i]);

                           $_ret[data] = $data;
                           
                           unset($data);
                           return($_ret);
                        }
                        break;
                    case 'updateRecord':
                        $_q = "UPDATE $key SET ".$tables[$table][updateFields]." WHERE ".$tables[$table][updateWhereFields];
						if(!is_array($value[updateWhereValues])) $this->setError("No UPDATE condition in $_q");
						else {
	                        $this->command($_q,array_merge($value[values],$value[updateWhereValues]));
                        }
						if($this->error()) return false;
                        
                        break;
                    default:
                        $this->setError('Unknown action: '.$action);
                        break;
                }
                
            }
			if($_requireConnection) $this->close();
        }

        function getHashFromArray($arr) {
        	if(!isset($arr)) $arr=array();
            return(md5(implode('', $arr)));
        }
	}
}
?>