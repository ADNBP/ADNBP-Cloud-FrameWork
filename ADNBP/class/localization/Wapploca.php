<?php
// CloudSQL Class v10
if (!defined ("_WAPPLOCA_CLASS_") ) {
define ("_WAPPLOCA_CLASS_", TRUE);

    class Wapploca
    {
        private $super;
        var $orgs=[];
        var $langs=['EN'];
        var $api = 'https://wapploca.org/h/api/wapploca';
        var $apps = [];
        var $error = false;
        var $errorMsg = [];
        var $data = [];
        var $localizePath ='';

        function __construct(ADNBP &$super,$langs=['ES'],&$data=null,$reset=false)
        {
            $this->super = $super;
            if(!strlen($super->getConf('LocalizePath')) || !$this->super->is('dirwritable',$super->getConf('LocalizePath'))) {
                $this->addError('Missing LocalizePath config var or dir is not writable: '.$super->getConf('LocalizePath'));
            } else {
                $this->localizePath = $super->getConf('LocalizePath');
                if(is_array($langs)) $this->langs = $langs;
                if(is_array($data)) $this->addLocBulk($data,$reset);
            }


        }

        function readApps($org=''){
            $apps=[];
            if(!strlen($org)) $org = $this->org;
            $ret =$this->super->getCloudServiceResponseCache($this->api.'/apps/'.$org);
            if(false !== $ret) {
                $ret = json_decode($ret,true);
                if($ret['success']) {
                    $this->apps = $ret['data'];
                } else {
                    $this->super->addError($ret);
                }
            }
        }

        function addLocBulk(&$data,$reset = false) {

            foreach ($this->langs as $lang) {
                $lang = strtoupper($lang);
                foreach ($data as $dic=>$keys) {
                    $this->super->addLog('Adding records: '.count($keys));
                    $this->super->deleteCache('Dic_' . $dic . '_' . $lang);
                    foreach ($keys as $key=>$tag) {
                        // If the dic has not be loaded, create an empty array to reset the files.
                        if($reset)
                            if(!isset($this->data['dics'][$dic][$lang])) $this->data['dics'][$dic][$lang]=[];
                        $this->addLoc($dic, $key, $lang);
                    }
                }
            }
            $this->saveLocalFiles();
        }

        function saveLocalFiles()
        {
            foreach ($this->data['dics'] as $dic => $langs) {
                foreach ($langs as $lang => $items) {
                    $filename = "/{$lang}_" . preg_replace('/[^A-z0-9_]/', '', $dic) . '.json';
                    if (file_put_contents($this->localizePath . $filename, json_encode($items, JSON_PRETTY_PRINT))) {
                        $this->super->addLog('Saved: ' . $filename);
                    } else {
                        $this->super->addLog('Error Saving : ' . $filename);

                    }
                }
            }
        }

        function addLoc($dic,$wapploca_code,$lang) {

            if(!strlen($dic) || !strlen($wapploca_code) )
                $this->addError("addLoc: Missing data $dic - $wapploca_code");
            else {

                list($org,$app,$cat,$code) = explode(";",$wapploca_code,4);
                if(!strlen($org) || !strlen($app) ||!strlen($cat) ||!strlen($code) )
                    $this->addError("addLoc: Wrong code  $wapploca_code");
                else {

                    //1. Detect if we have loaded the dic


                    if(!isset($this->data['dics'][$dic][$lang])) {
                        $filename =  "/{$lang}_" . preg_replace('/[^A-z0-9_]/', '', $dic) . '.json';
                        if (is_file($this->localizePath .$filename)) {
                            $this->super->addLog('Loading: '.$filename);
                            $this->data['dics'][$dic][$lang] = json_decode(file_get_contents($this->localizePath .$filename),true);
                        } else {
                            $this->super->addLog('Not found: '.$filename);
                            $this->data['dics'][$dic][$lang]=[];
                        }
                    }

                    //2. Detect if we have loaded the wapploca cat
                    if(!isset($this->data['wapploca']["$org;$app;$cat"][$lang])) {
                        $this->super->addLog('Connecting with SWAPPLOCA: '."/dics/$org/$app/$cat?export=json&lang=$lang");
                        $ret =$this->super->getCloudServiceResponseCache($this->api."/dics/$org/$app/$cat?export=json&lang=$lang");
                        if(false !== $ret) {
                            $ret = json_decode($ret,true);
                            if(count($ret)) {
                                $this->data['wapploca']["$org;$app;$cat"][$lang] = $ret;
                            } else {
                                $this->data['wapploca']["$org;$app;$cat"][$lang]['error'] = 'none found';
                                $this->super->addError("$org;$app;$cat not found");
                            }
                        } else {
                            die('Error calling service wapploca');
                        }
                    }

                    //3. Start the mapping bt. LOCAL DICS && WAPPLOCA
                    $this->data['dics'][$dic][$lang][$wapploca_code] = (isset($this->data['wapploca']["$org;$app;$cat"][$lang][$wapploca_code]))?$this->data['wapploca']["$org;$app;$cat"][$lang][$wapploca_code]:$wapploca_code;
                }

            }

        }

        function addError($err) {
            $this->error = true;
            $this->errorMsg[] = $err;
            $this->super->addLog($err);
        }
    }
}