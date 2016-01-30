<?php
// CloudSQL Class v10
if (!defined ("_MYSQLI_MODELS_CLASS_") ) {
    require_once __DIR__.'/CloudSQL.php';
    define("_MYSQLI_MODELS_CLASS_", TRUE);

    class CloudModels extends CloudSQL {
        /**
         * Return the CloudFrameWork-io Model from a DB table
         * @param $table
         * @return array where array['model'] is the JSON model if array['table_exists]===true
         */
        function getModelFromTable($table) {
            if($this->tableExists($table)) {
                $tmp['explain'] = $this->getDataFromQuery("SHOW FULL COLUMNS FROM %s", $table);
                $tmp['index'] = $this->getDataFromQuery('SHOW INDEX FROM %s;',array($table));
                $tmp['SQL'] = $this->getDataFromQuery('SHOW CREATE TABLE %s;',array($table))[0];
                $tmp['SCHEMA'] = $this->getDataFromQuery('SELECT * FROM information_schema.TABLES WHERE TABLE_NAME = "%s"',array($table))[0];
                foreach ($tmp['explain'] as $key => $value) {

                    // TYPE OF THE FIELD
                    $tmp['Fields'][$value['Field']]['type'] = $value['Type'];

                    // IS NULLABLE
                    if ($value['Null'] == 'NO')
                        $tmp['Fields'][$value['Field']]['null'] = false;

                    // Let's see if the field is Key, Unique or Index
                    if (strlen($value['Key']))
                        if ($value['Key'] == 'PRI') $tmp['Fields'][$value['Field']]['key'] = true;
                        elseif($value['Key'] == 'MUL') $tmp['Fields'][$value['Field']]['index'] = true;
                        elseif($value['Key'] == 'UNI') $tmp['Fields'][$value['Field']]['unique'] = true;

                    // Default value
                    if(!($value['Null'] == 'NO' && $value['Default']===null))
                        $tmp['Fields'][$value['Field']]['default'] = $value['Default'];

                    if (strlen($value['Extra']))
                        $tmp['Fields'][$value['Field']]['extra'] = $value['Extra'];

                    // Comment field
                    $tmp['Fields'][$value['Field']]['description'] = $value['Comment'];
                }
                return (['table_exists'=>true,'model'=>['table' => $table
                    , 'description' => $tmp['SCHEMA']['TABLE_COMMENT']
                    , 'engine' => $tmp['SCHEMA']['ENGINE']
                    , 'fields' => $tmp['Fields']
                ]
                    , 'Schema' => $tmp['SCHEMA']
                    , 'indexes' => $tmp['index']
                    , 'SQL' => $tmp['SQL']['Create Table']
                    , 'explain' => $tmp['explain']
                    //, 'indexes' => $tmp['index']
                ]);
            } else
                return(['table_exists'=> false]);
        }

        /**
         * Return the SQL CREATION table for mysql based on a CloudFrameWork-io Model
         * @param $model
         * @return bool|mixed|string
         */
        function getSQLTableCreationFromModel($model) {
            $data = array($model['table']);
            $sql = "CREATE TABLE %s (";

            // Fields
            foreach ($model['fields'] as $field=>$fieldAttribs) {
                if($sql != "CREATE TABLE %s (") $sql.=', ';
                $sql .= $this->getSQLFieldCreationFromModelField($field,$fieldAttribs);
            }

            // Keys
            foreach ($model['fields'] as $field=>$fieldAttribs) {
                $sql .= $this->getSQLKeyCreationFromModelField($field,$fieldAttribs);
            }


            if(!strlen($model['engine'])) $model['engine']='InnoDB';
            $sql .= ') ENGINE = ' . $model['engine'];
            // TODO: Control engine;
            // $sql.= ' DEFAULT CHARACTER SET ' . $table['mysql_character_set'];
            return($this->_buildQuery(array($sql,$data)));
        }

        function getSQLFieldCreationFromModelField ($field,$attribs) {
            $sql = $field;
            $sql.= ' '.$attribs['type'];
            if($attribs['key'] || (isset($attribs['null']) && !($attribs['null']))) $sql.=' NOT NULL';
            if($attribs['key'] and strpos(strtolower($attribs['type']),'int')===0)  $sql.=' AUTO_INCREMENT';

            if(!strlen($attribs['default']) && false !== $attribs['null']) $attribs['default']='NULL';
            if(strlen($attribs['default']))
                if(trim(strtoupper($attribs['default']))=='NULL')
                    $sql.=' DEFAULT '.$attribs['default'];
                else
                    $sql.=' DEFAULT \''.$attribs['default'].'\'';

            $sql.= ' COMMENT \''.$attribs['description'].'\'';

            return($sql);
        }

        function getSQLKeyCreationFromModelField ($field,$attribs) {
            $sql='';
            if($attribs['key']) $sql.=', PRIMARY KEY('.$field.')';
            elseif($attribs['unique']) $sql.=', UNIQUE KEY('.$field.')';
            elseif($attribs['index']) $sql.=', KEY '.$field.'('.$field.')';
            return($sql);
        }



        function getSQLTableUpdateFromModelField ($action,$table,$field,$attribs=array())
        {
            $ret='ALTER TABLE '.$table;
            if (strlen($table) && ('update' == strtolower($action) || 'insert' == strtolower($action) ||  'delete' == strtolower($action)))
            {
                if(strtolower($action) == 'delete') {
                    $ret.= ' DROP '.$field;
                } else {
                    $ret .= (strtolower($action) == 'update') ? ' MODIFY ':' ADD ';
                    $ret.= $this->getSQLFieldCreationFromModelField($field,$attribs);
                    // $ret.=$this->getSQLKeyCreationFromModelField($field,$attribs);
                }
            }
            return($ret);
        }

        function checkModels($models,$action='') {

            // Attribs to explore.
            $tmp['attribs'] = array('type','key','index','default','description');


            // We expect a JSON array
            $tmp['models'] = json_decode($models,true);
            if(!is_array($tmp['models'])) $tmp['models'] = array();
            else {
                // If only a model is passed convert it in an array on 1 element.
                if(!is_array($tmp['models'][0])) $tmp['models'] = array($tmp['models']);
            }

            //At least has to be one element with table and fields attribs.
            //TODO: improve Model Structure validation
            if(!isset($tmp['models'][0]['table']) || !is_array($tmp['models'][0]['fields']))
                return array('wrong CloudFrameWork-io JSON Model');;

            // Start the exploring
            foreach ($tmp['models'] as $key=>$model) {

                $tmp['db'] = $this->getModelFromTable($model['table']);
                $tmp['ret'][$model['table']]['table_exists'] = $tmp['db']['table_exists'];

                // If table does not exist, set table creation
                if(!$tmp['ret'][$model['table']]['table_exists']) {
                    $tmp['ret'][$model['table']]['SQL'][] = $this->getSQLTableCreationFromModel($model);
                    continue; // go yo next model
                } else {
                    if($model['description'] != $tmp['db']['model']['description'] ) {

                        $tmp['ret'][$model['table']]['table_description']['model'] = $model['description'];
                        $tmp['ret'][$model['table']]['table_description']['db'] = $tmp['db']['model']['description'];
                        $tmp['ret'][$model['table']]['table_ok'] = false;
                        $tmp['ret'][$model['table']]['fields'] = [];
                        $tmp['ret'][$model['table']]['SQL'][] = "ALTER TABLE ".$model['table']." COMMENT = '".$model['description']."'";

                        // Updating table description in $action=='udate'
                        if($action == 'update') {
                            $this->command("ALTER TABLE %s COMMENT = '%s'",array($model['table'],$model['description']));
                            if (!$this->error()) {
                                $tmp['ret'][$model['table']]['SQL'][] = 'UPDATED OK';
                                $tmp['ret'][$model['table']]['table_description'] = 'OK: ee '.$model['description'];
                                $tmp['ret'][$model['table']]['table_ok'] = true;

                            } else {
                                $tmp['ret'][$model['table']]['SQL'][] = 'UPDATING ERROR: '.$this->getError();
                            }
                        }
                    } else {
                        $tmp['ret'][$model['table']]['table_description'] = 'OK: '.$tmp['db']['model']['description'];
                        $tmp['ret'][$model['table']]['table_ok'] = true;
                    }


                }

                //If table exists.. explore modifications.
                $tmp['nattribs'] = count($tmp['attribs']);

                // Checking fields from model to DB
                foreach ($model['fields'] as $field=>$fieldAttribs) {
                    if(!isset($tmp['db']['model']['fields'][$field])) {
                        $tmp['ret'][$model['table']]['fields'][$field] = 'missing in database';
                        $tmp['ret'][$model['table']]['SQL'][] = $this->getSQLTableUpdateFromModelField('insert',$model['table'],$field,$fieldAttribs);

                    }
                    else for($i=0;$i<$tmp['nattribs'];$i++) {
                        $attrib = $tmp['attribs'][$i];
                        $attrib_model = (isset($fieldAttribs[$attrib])) ? $fieldAttribs[$attrib] : 'none';
                        $attrib_db = (isset($tmp['db']['model']['fields'][$field][$attrib])) ? $tmp['db']['model']['fields'][$field][$attrib] : 'none';

                        // Fill the output with the differences found in attribs
                        if ($attrib_model != $attrib_db) {
                            // SQL to correct the field
                            if(!isset($tmp['ret'][$model['table']]['fields'][$field])) {
                                $tmp['ret'][$model['table']]['fields'][$field] = array();
                                $tmp['ret'][$model['table']]['SQL'][] = $this->getSQLTableUpdateFromModelField('update', $model['table'], $field, $fieldAttribs);
                                if($action == 'update') {
                                    $this->command($this->getSQLTableUpdateFromModelField('update', $model['table'], $field, $fieldAttribs));
                                    if(!$this->error()) $tmp['ret'][$model['table']]['SQL'][] = 'UPDATED OK';
                                    else {
                                        $tmp['ret'][$model['table']]['SQL'][] = 'UPDATING ERROR: '.$this->getError();
                                        $tmp['ret'][$model['table']]['table_ok'] = false;
                                    }
                                } else {
                                    $tmp['ret'][$model['table']]['table_ok'] = false;
                                }

                            }
                            // Description of each attrib with difference.
                            $tmp['ret'][$model['table']]['fields'][$field][$attrib]['model'] =  $attrib_model;
                            $tmp['ret'][$model['table']]['fields'][$field][$attrib]['db'] = $attrib_db;
                        }
                    }
                    if(!isset($tmp['ret'][$model['table']]['fields'][$field]))
                        $tmp['ret'][$model['table']]['fields'][$field] = 'OK: '.$fieldAttribs['description'];
                }

                // Checking fields from DB to model
                if(is_array($tmp['db']['model']['fields']))
                    foreach ($tmp['db']['model']['fields'] as $field=>$fieldAttribs) {
                        if(!isset($model['fields'][$field])) {
                            $tmp['ret'][$model['table']]['fields'][$field] = 'missing in the model';
                            $tmp['ret'][$model['table']]['SQL'][] = $this->getSQLTableUpdateFromModelField('delete',$model['table'],$field);
                            $tmp['ret'][$model['table']]['table_ok'] = false;
                        }
                    }
            }
            return($tmp['ret']);
        }
    }
}