<?php
// CloudSQL Class v10
if (!defined ("_CloudServiceReporting_CLASS_") ) {
    define ("_CloudServiceReporting_CLASS_", TRUE);
    
    class CloudServiceReporting {
        var $error = false;
        var $errorMsg = array();
        var $data = array();
        var $db = null;
        var $super = null;
        var $queryResults = array();
        
        function CloudServiceReporting(&$db=null) {
            global $adnbp;
            $this->super = &$adnbp;
            $this->super->initCache();
        }
        
        // Excute an DB query
        function _query($id,$q,$data) {
            if($this->error) return false;
            $q = "SELECT ".$q;
            
            // Check cache
            if(!isset($_REQUEST['reload'])) {
                $this->queryResults[$id]['data'] = $this->super->getCache(md5($id.$q.json_encode($data)));
                if(is_array($this->queryResults[$id]['data'])) {
                      return true;
                }
            }
            
            
            
            if($this->db===null) {
                $this -> super -> loadClass("db/CloudSQL");
                $this->db = new CloudSQL();
                $this->db->connect();
                if($this->db->error()) {
                     $this->setError($this->db->getError());
                    return false;
                }
            }
            
            // Query
            $ret = $this->db->getDataFromQuery($q,$data);
            $this->queryResults[$id]['query'] = $this->db->getQuery();
            if(!$this->db->error()) {
                $this->queryResults[$id]['data'] = $ret;
                $this->super->setCache(md5($id.$q.json_encode($data)),$ret);
                unset($ret);
                return true;
            } else {
                $this->queryResults[$id]['data'] = array($this->db->getError());
                return false;
            }
        }
        
        
        function query($id,$q,$data) {
            if($this->error) return false;
            else return  $this->_query($id,$q,$data);
        }
        
        function queryTotals($id,$q,$data=null) {
            if($this->query($id,$q,$data)) {
                if(count($this->queryResults[$id]['data'])) $this->queryResults[$id]['data'] = $this->queryResults[$id]['data'][0];
            } else return false;
        }
        
        function queryEnd() {
            if(is_object($this->db)) $this->db->close();
        }
        function queryData($id,$field='') {
            if(isset($this->queryResults[$id])) {
                if(strlen($field)) return $this->queryResults[$id]['data'][$field];
                else return $this->queryResults[$id]['data'];
            } 
            return false;
        }
        
        function add($type,$info='') {
            //if(is_array($info)) _printe((object)$info);
            switch ($type) {
                case 'row':
                case 'col':
                    if($info=='') $info=12;
                    if($info!='') $this->data[] = array('type'=>$type,'data'=> $info);
                    break;
                default:
                    $this->data[] = array('type'=>$type,'data'=> is_array($info)?(object)$info:json_decode($info));
                    break;
            }
        }
        function output() {
        	global $adnbp;
			$types = array('barcode'=>false);
			$_tables = false;
            $rows='';
            $cols='';
            $container=false;
            $lastColSize=12;
            ob_start();
            echo '<section id="widget-grid" >';
            foreach ($this->data as $key => $data) {
                $type = $data['type'];
                $data = $data['data'];
                if($type=='header') 
                    include __DIR__.'/templates/header.php';
                elseif($type=='table') {
                	$simple = false;
                    include __DIR__.'/templates/table.php';
					$_tables = true;
                }
                elseif($type=='simpleTable') {
                	$simple = true;
                    include __DIR__.'/templates/table.php';
					$_tables = true;
                }
                elseif($type=='tree') {
                    $simple = true;
                    include __DIR__.'/templates/tree.php';
                    $_tables = true;
                }
				elseif($type=='barcode') {
					
					_printe($data->data);
					$barcode = array('element'=>'barcode'.$key);
					
                    include __DIR__.'/templates/barcode.php';
                }
                elseif($type=='btnInfo') {
                    if($list) echo "<li>";
                    include __DIR__.'/templates/btnInfo.php';
                    echo "</li>";
                }
                elseif($type=='container') {
                    if($container) include __DIR__.'/templates/container.php';

                    $container=false;
                    include __DIR__.'/templates/container.php';
                    $container=true;
                }
                elseif($type=='row') {
                    if($container) include __DIR__.'/templates/container.php';
                    $container=false;
                    echo $cols; $cols='';
                    echo $rows."<div class='row'>\n";
                    $rows="\n</div> <!-- row --><br/>";
                    if($data=="center") {
                         echo "<center>";
                        $rows = "</center>".$rows;
                    }
                } elseif($type=='col') {
                    if($container) include __DIR__.'/templates/container.php';
                    $container=false;
                    echo $cols;
                    echo '    <div class="col-md-'.$data.'">';
                    $cols="\n    </div> <!-- col -->";
                    $lastColSize = $data;
                } 
            }
            if($container) include __DIR__.'/templates/container.php';
            echo $list.$cols.$rows;
            echo "</section>";
			//include __DIR__.'/templates/tablejsbottom.php';
            return ob_get_clean();

        }

        function setError($errorMsg) {
            $this -> errorMsg = array();
            $this->addError($errorMsg);
        }
        function addError($errorMsg) {
            $this -> error = true;
            $this -> errorMsg[] = $errorMsg;            
        }

    }
} 