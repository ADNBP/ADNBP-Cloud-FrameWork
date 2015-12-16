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
        var $filters = array();
        
        function __construct(&$db=null) {
            global $adnbp;
            $this->super = &$adnbp;
            $this->super->initCache();
        }

        function filter($command,$var,$info=null) {
            if(is_array($info) && isset($info['type']) &&  ($command == 'set' || $command=='add')) {



                // lets work on the diferent filters.
                $_filterCorrect = true;
                switch($info['type']) {
                    case "select":
                        if(!isset($info['data'])) $_filterCorrect = false;
                        else{
                            // Value for field based in filter or default
                            if(isset($_REQUEST['filter_'.$var])) $info['value'] = $_REQUEST['filter_'.$var];
                            else $info['value'] = (isset($info['default']))?$info['default']:'';

                            if(strlen($info['value']) && !isset($info['data'][$info['value']])) $info['value'] ='';

                            // Required value
                            if($info['required'] && !strlen($info['value'])) $info['value'] = array_keys($info['data'])[0];
                        }
                        break;
                }

                if($_filterCorrect) {
                    if ($command == 'set') $this->filters = array($var => $info);
                    elseif ($command == 'add') $this->filters[$var] = $info;
                }
            }
            elseif($command == 'get') return (isset($this->filters[$var]))?$this->filters[$var]:null;
            elseif($command == 'getvalue') return (isset($this->filters[$var]))?$this->filters[$var]['value']:null;
            elseif($command == 'getdata') return (isset($this->filters[$var]))?$this->filters[$var]['data']:null;
            elseif($command == 'delete') { if(isset($this->filters[$var])) unset($this->filters[$var]);}
            else return null;
        }

        // Excute an DB query
        function query($id,$q,$data=null) {
            if($this->error) return false;
            $q = "SELECT ".$q;
            
            // Check cache
            if(!isset($_REQUEST['reload'])) {
                $this->queryResults[$id]['data'] = $this->super->getCache('Reporting_'.$id.'_'.md5($id.$q.json_encode($data)));
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
                $this->super->setCache('Reporting_'.$id.'_'.md5($id.$q.json_encode($data)),$ret);
                unset($ret);
                return true;
            } else {
                $this->addError($this->db->getError());
                return false;
            }
        }
        //Close the db connection of a query.
        function queryEnd() {
            if(is_object($this->db)) $this->db->close();
        }

        /**
         * @param $id   id of of data
         * @param $cond  condition to reduce
         * @return array|null
         */
        function getSubData($id,$cond) {
            $ret = null;
            if (isset($this->queryResults[$id]['data'])) {
                if(!is_array($cond)) $ret = &$this->queryResults[$id]['data'];
                else {
                    $ret=array();
                    //_printe($this->queryResults[$id]['data']);
                    foreach ($this->queryResults[$id]['data'] as $i=>$row) {
                        // Only include match elements
                        $inc = true;
                        foreach ($cond as $key => $fieldCond) {

                            if (!is_array($fieldCond)) {
                                $fieldCond = array(  '=', $fieldCond );
                            }
                            switch ($fieldCond[0]) {
                                case '=':
                                    if (!isset($row[$key]) || $row[$key] != $fieldCond[1]) $inc = false;
                                    break;
                                case '!=':
                                    if (!isset($row[$key]) || trim($row[$key]) == trim($fieldCond[1])) $inc = false;
                                    break;
                            }
                        }
                        if($inc) $ret[] = $row;
                    }
                }
            }
            return $ret;
        }

        /**
         * @param $id
         * @param string $fields
         * @param string $op
         * @param null $cond
         * @return array|bool|int|string
         */
        function queryData($id,$fields='*',$op='raw',$cond=null)
        {
            if (!isset($this->queryResults[$id]['data'])) return false;
            if(!is_string($fields) || trim($fields)=='') $fields='*';
            $data = $this->getSubData($id,$cond);
            if ($fields=='*' && $op=='raw') return $data;
            else {
                $ret = '';
                if($fields=='*') $fields = array_keys($data[0]);
                else $fields = explode(',', $fields);

                switch ($op) {
                    case'raw':
                        $ret = array();
                        for ($i = 0, $tr = count($data); $i < $tr; $i++) {
                            foreach ($fields as $ind => $key) { $key = trim($key);
                                $ret[$i][$key] = $data[$i][$key];
                            }
                        }
                        break;
                    case'sum':
                        $ret = 0;
                        for ($i = 0, $tr = count($data); $i < $tr; $i++) {
                            foreach ($fields as $ind => $key) { $key=trim($key);
                                if(isset($data[$i][$key]))
                                $ret += $data[$i][$key];
                            }
                        }
                        break;
                    case'count':
                        $ret=0;
                        if(!is_array($cond))
                            return(count($data));
                        else for ($ret=0,$i=0,$tr=count($data);$i<$tr;$i++) {
                            $ret++;
                        }
                        return $ret;

                        break;
                }
                return($ret);
            }
            return false;
        }
        function queryDataFields($id) {
            $ret=array();
            if (is_array($this->queryResults[$id]['data'][0])) $ret = array_keys($this->queryResults[$id]['data'][0]);
            return($ret);
        }
        function queryDataRows($id) {
            $ret=0;
            if (is_array($this->queryResults[$id]['data'])) $ret = count($this->queryResults[$id]['data']);
            return($ret);
        }
        function queryDataExplore($id,$field,$row=null,$col=null,$cond=null){
            if (!isset($this->queryResults[$id]['data']) || (!is_array($field) && !strlen($field)) ) return false;
            $data = $this->getSubData($id,$cond);

            if(!is_array($field)) $field = array($field,'distinct');
            if(!is_array($row)) $row = array((strlen($row))?$row:'_row_');
            if(!is_array($col)) $col = array((strlen($col))?$col:'_col_');

            $rowFieldContent = $row[0];
            $colFieldContent = $col[0];

            $ret = array();
            $retRows = array();
            $retCols = array();
            $retFields = array();
            $distinctFields = array();

            // Preparing data in the first Loop
            for ($i = 0, $tr = count($data); $i < $tr; $i++) {
                // ROW/COL for the field
                if($row[0]!='_row_')
                    $rowFieldContent = (isset($data[$i][$row[0]]) && strlen($data[$i][$row[0]]))?$data[$i][$row[0]]:'_empty_';

                if($col[0]!='_col_')
                    $colFieldContent = (isset($data[$i][$col[0]]) && strlen($data[$i][$col[0]]))?$data[$i][$col[0]]:'_empty_';

                $value = (isset($data[$i][$field[0]]) && strlen($data[$i][$field[0]]))?$data[$i][$field[0]]:'_empty_';

                switch ($field[1]) {
                    case 'distinct':
                        if(!isset($distinctFields[$value])) {
                            $ret[$rowFieldContent][$colFieldContent][] = $value;
                            $distinctFields[$value] += 1;
                            $retRows[$rowFieldContent][]= $value;
                            $retCols[$colFieldContent][]= $value;
                        }
                        break;
                    case 'count':
                        $ret[$rowFieldContent][$colFieldContent] += 1;
                        $retRows[$rowFieldContent]+= 1;
                        $retCols[$colFieldContent]+= 1;
                        $retFields['count_' .$field[0]]+=1;

                        break;
                    default:
                        $ret[$rowFieldContent][$colFieldContent] += $value;
                        $retRows[$rowFieldContent]+= $value;
                        $retCols[$colFieldContent]+= $value;
                        break;
                }
            }

            // Transform return data based in the info colected.
            // Potential order
            if($col[0]!='_col_' && isset($col[1]) && stripos($col[1],'order ')!==false)
                if(stripos($col[1],' asc')!==false) ksort($retCols);
                else if(stripos($col[1],' desc')!==false) krsort($retCols);

            if($row[0]!='_row_' && isset($row[1]) && stripos($row[1],'order ')!==false)
                if(stripos($row[1],' asc')!==false) ksort($retRows);
                else if(stripos($col[1],' desc')!==false) krsort($retRows);

            // Values Data
            $retGroup = array();
            $retGroup[1] = ($row[0]!='_row_')?array_keys($retRows):'';
            $retGroup[2] = ($col[0]!='_col_')?array_keys($retCols):'';
            $i=0;
            foreach ($retRows as $row=>$rowValue) {
                foreach ($retCols as $col=>$colValue) {
                    if($row != '_row_') {
                        if($col !='_col_') {
                            $retGroup[0][$i][] = $ret[$row][$col];
                        } else {
                            $retGroup[0][] = $ret[$row][$col];
                        }
                    } elseif($col !='_col_') {
                        $retGroup[0][] = $ret[$row][$col];
                    } else {
                        $retGroup[0] = $ret[$row][$col];
                    }
                }
                $i++;
            }
            return($retGroup);

        }

        /**
         * @param $id
         * @param $fields
         * @param $rows
         * @param $cols
         * @param $cond
         * @return array or false in error case.
         */
        function queryDataGroup($id,$fields,$rows,$cols,$cond=null)
        {
            if (!isset($this->queryResults[$id]['data'])) return false;
            $data = $this->getSubData($id,$cond);


            if(!is_array($fields)) $fields = explode(",",trim($fields));
            if(!is_array($rows)) $rows = explode(",",trim($rows));
            if(!is_array($cols)) $cols = explode(",",trim($cols));

            if(!strlen($rows[0])) $rows = array('_row_');
            if(!strlen($cols[0])) $cols = array('_col_');

            $ret = array();
            $retRows = array();
            $retCols = array();
            $retFields = array();
            // Preparing data in the first Loop
            for ($i = 0, $tr = count($data); $i < $tr; $i++) {
                $row = '';
                foreach ($rows as $ind => $key) {
                    $key = trim($key);
                    $rowFieldContent = (isset($data[$i][$key]))?$data[$i][$key]:$key;
                    $row.= ($row)?'_'.$rowFieldContent:$rowFieldContent;
                    foreach ($fields as $ind2 => $field) {
                        if(!is_array($field)) $field = array(trim($field),'sum');
                        $col ='';
                        foreach ($cols as $ind3 => $key3) {
                            $colFieldContent = (isset($data[$i][$key3]))?$data[$i][$key3]:$key3;
                            $col.= ($col)?'_'.$colFieldContent:$colFieldContent;
                            switch ($field[1]) {
                                case 'count':
                                    $ret[$row][$col]['count_' . $field[0]] += 1;
                                    $retRows[$row]+= 1;
                                    $retCols[$col]+= 1;
                                    $retFields['count_' .$field[0]]+=1;

                                    break;
                                default:
                                    $ret[$row][$col][$field[0]] += $data[$i][$field[0]];
                                    $retRows[$row]+= $data[$i][$field[0]];
                                    $retCols[$col]+= $data[$i][$field[0]];
                                    $retFields[$field[0]]+= $data[$i][$field[0]];
                                    break;
                            }
                        }
                    }

                }
            }

            // Building array to allow its representation
            $retGroup = array();
            $colSpan = count($retFields);
            foreach ($retRows as $row=>$rowValue) {
                $currentRow = array();

                // Header of the report
                if(!count($retGroup)) {
                    if($row !='_row_') $currentRow[] = '';
                    foreach ($retCols as $col=>$colValue) if($col != '_col_'){
                        $currentRow[] = array('value'=>$col,'colspan'=>$colSpan,'align'=>'center','bold'=>true);
                    }
                    if(count($currentRow)>1)  {
                        $retGroup[] = $currentRow;
                    }
                    $currentRow = array();
                }

                // Row Content
                if($row !='_row_') $currentRow[] = array('value'=>$row,'bold'=>true);;
                foreach ($retCols as $col=>$colValue) {
                    $i=0;
                    foreach ($retFields as $field=>$fieldValue ) {
                        $currentRow[] = array('value'=>$ret[$row][$col][$field],'align'=>'right','currency'=> (is_array($fields[$i]))?$fields[$i][2]:'') ;
                        $i++;
                    }




                }

                $retGroup[] = $currentRow;
            }
            return($retGroup);
        }

        /**
         * @param $type
         * @param string $info
         */
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

        /**
         * return the HTML with the info printed.
         */
        function output() {
        	global $adnbp;
            $controlVars = (object)array('dygraph'=>false,'tables'=>false,'reportNumber'=>0);

			$types = array('barcode'=>false);
			$_tables = false;
            $rows='';
            $cols='';
            $container=false;
            $lastColSize=12;
            ob_start();
            echo '<section id="widget-grid" >';
            foreach ($this->data as $key => $data) {
                $controlVars->reportNumber++;
                $type = $data['type'];
                $data = $data['data'];
                if(isset($data->columns) && is_string($data->columns)) $data->columns = explode(',',$data->columns);

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
                elseif($type=='dygraph') {
                    if(is_array($data->rows)) {
                        if(!isset($data->rows[1]) || !is_array($data->rows[1])) $data->rows[1][]='Info';
                        if(!isset($data->rows[2]) ||!is_array($data->rows[2])) $data->rows[2][]=date('Ymd');
                        if(is_array($data->rows[0]) && !is_array($data->rows[1][0]) && !is_array($data->rows[2][0])) {
                            if(!is_array($data->rows[0][0])) $data->rows[0] = array($data->rows[0]);
                            include __DIR__ . '/templates/dygraph.php';

                        } else $this->addError('Wrong dygraph data');
                    }

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

        /**
         * @param $errorMsg error message to set.
         */
        function setError($errorMsg) {
            $this -> errorMsg = array();
            $this->addError($errorMsg);
        }

        /**
         * @param $errorMsg error messageto add.
         */
        function addError($errorMsg) {
            $this -> error = true;
            $this -> errorMsg[] = $errorMsg;            
        }

    }
} 