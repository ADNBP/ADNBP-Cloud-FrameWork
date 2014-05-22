<?php
// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php
    list($foo,$script,$service,$params) = split('/',$this->_url,4);
    switch ($service) {
        case "SABADELL":
            

            
            if(!strlen($params)) {
                
                $_data = $_POST;
                if(strlen($this->getConf("TPV_URL"))) $_data[TPV_URL] = $this->getConf("TPV_URL");
                if(strlen($this->getConf("TPV_Secret"))) $_data[TPV_Secret] = $this->getConf("TPV_Secret");
                if(strlen($this->getConf("Ds_Merchant_Amount"))) $_data[Ds_Merchant_Amount] = $this->getConf("Ds_Merchant_Amount");
                if(strlen($this->getConf("Ds_Merchant_TransactionType"))) $_data[Ds_Merchant_TransactionType] = $this->getConf("Ds_Merchant_TransactionType");
                if(strlen($this->getConf("Ds_Merchant_Currency"))) $_data[Ds_Merchant_Currency] = $this->getConf("Ds_Merchant_Currency");
                if(strlen($this->getConf("Ds_Merchant_MerchantCode"))) $_data[Ds_Merchant_MerchantCode] = $this->getConf("Ds_Merchant_MerchantCode");
                if(strlen($this->getConf("Ds_Merchant_Terminal"))) $_data[Ds_Merchant_Terminal] = $this->getConf("Ds_Merchant_Terminal");
                if(strlen($this->getConf("Ds_Merchant_MerchantURL"))) $_data[Ds_Merchant_MerchantURL] = $this->getConf("Ds_Merchant_MerchantURL");
                if(strlen($this->getConf("Ds_Merchant_UrlOK"))) $_data[Ds_Merchant_UrlOK] = $this->getConf("Ds_Merchant_UrlOK");
                if(strlen($this->getConf("Ds_Merchant_UrlKO"))) $_data[Ds_Merchant_UrlKO] = $this->getConf("Ds_Merchant_UrlKO");
                if(strlen($this->getConf("Ds_Merchant_Amount"))) $_data[Ds_Merchant_Amount] = $this->getConf("Ds_Merchant_Amount");
                if(strlen($this->getConf("Ds_Merchant_Order"))) $_data[Ds_Merchant_Order] = $this->getConf("Ds_Merchant_Order");
                if(strlen($this->getConf("Ds_Merchant_MerchantData"))) $_data[Ds_Merchant_MerchantData] = $this->getConf("Ds_Merchant_MerchantData");
                if(strlen($this->getConf("Ds_Merchant_MerchantName"))) $_data[Ds_Merchant_MerchantName] = $this->getConf("Ds_Merchant_MerchantName");
                //Optional
                if(strlen($this->getConf("Ds_Merchant_ConsumerLanguage"))) $_data[Ds_Merchant_ConsumerLanguage] = $this->getConf("Ds_Merchant_ConsumerLanguage");
                                                
                // Allowing _GET params
                if(!strlen($_data[Ds_Merchant_Amount])) $_data[Ds_Merchant_Amount] = $_GET[Ds_Merchant_Amount];
                if(!strlen($_data[Ds_Merchant_Order])) $_data[Ds_Merchant_Order] = $_GET[Ds_Merchant_Order];
                if(!strlen($_data[Ds_Merchant_ConsumerLanguage])) $_data[Ds_Merchant_ConsumerLanguage] = $_GET[Ds_Merchant_ConsumerLanguage];
    

                // Call to external Service to get an IdTransaction
                $data = array('field1' => 'value', 'field2' => 'value');

                 

                
                 
                if($_GET[initTransaction]=='1') {
                        
                     $result = unserialize($this->getCloudServiceResponse("TPVTransaction/init",$data));
                     if($result[response]!="OK")
                     _print($result);                        
                    
                    $_ready = true;
                    if(!strlen($_data[TPV_URL])) $_ready = false;
                    if(!strlen($_data[TPV_Secret])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_MerchantCode])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_MerchantName])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Titular])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_ProductDescription])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Amount])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_TransactionType])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Currency])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Terminal])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_MerchantURL])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_UrlOK])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_UrlKO])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Amount])) $_ready = false;
                    if(!strlen($_data[Ds_Merchant_Order])) $_ready = false;
    
                    //To add information in the URLOK AND KO
                    if($_ready) {
                        $_data[Ds_Merchant_UrlOK] .= '?Ds_Order='.urlencode($_data[Ds_Merchant_Order]);
                        $_data[Ds_Merchant_UrlKO] .= '?Ds_Order='.urlencode($_data[Ds_Merchant_Order]);
                    }
                    
                    $Ds_Merchant_MerchantSignature = strtoupper(sha1(
                    $_data[Ds_Merchant_Amount]
                   .$_data[Ds_Merchant_Order]
                   .$_data[Ds_Merchant_MerchantCode]
                   .$_data[Ds_Merchant_Currency]
                   .$_data[Ds_Merchant_TransactionType]
                   .$_data[Ds_Merchant_MerchantURL]
                   .$_data[TPV_Secret]
                   ));
                   
                   /*
                   if($_ready) {
                        // Insert Log
                        $_CloudFrameWorkData[TPVLog_DirectoryOrganization_Id] = $this->getConf("setOrganizationId");
                        $_CloudFrameWorkData[TPVLog_TPV_Id] = 1; // Sabadell will have 1
                        $_CloudFrameWorkData[TPVLog_Order] = $_data[Ds_Merchant_Order]; // Sabadell will have 1
                                            
                        $_CloudFrameWorkData[TPVLog_Name] = 'Init Log Sabadell TPV'; // Sabadell will have 1
                        $_CloudFrameWorkData[TPVLog_Date] = date("Y-m-d H:i:s"); // Sabadell will have 1
                        $_CloudFrameWorkData[TPVLog_Info] = print_r($_data,true); // Sabadell will have 1
                        if(!$db->cloudFrameWork("insert",$_CloudFrameWorkData,'TPVLogs')) 
                          $msgerror.= 'DBINSERT_ERROR: '.$db->getError();
                                                                                                    
                        if(strlen($msgerror)) {
                             echo "<pre>$msgerror<pre>";
                             $_ready=false;
                        }           
                   } 
                   */
               }

            } else if($params=='response' || $params=='OK' || $params=='KO') {
                
                if(!is_object($db)){
                    $this->loadClass("db/CloudSQL");
                    $db = new CloudSQL();
                }
                if(!$db->connect()) die('- Error connection DB: '.$db->getError());
                            
                if($params=='OK' || $params=='KO') {
                   
                   if(!strlen($_GET[Ds_Order])) $msgerror.="-Missing Ds_Order\n"; 
                   
                   
                   // What to show in the screen
                   if($params=='OK') $output = 'Transaction OK';
                   else if($params=='KO') $output = 'Transaction FAILED';
                   echo $output;
                   
                      // Insert Log
                    $_CloudFrameWorkData[TPVLog_DirectoryOrganization_Id] = $this->getConf("setOrganizationId");
                    $_CloudFrameWorkData[TPVLog_TPV_Id] = 1; // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Order] = $_GET[Ds_Order]; // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Name] = ((strlen($msgerror) || $params=='KO')?'Error ':'OK ').'Log Sabadell TPV '.$params; // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Date] = date("Y-m-d H:i:s"); // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Info] = $msgerror." -> ".print_r($_GET,true); // Sabadell will have 1
                    if(!$db->cloudFrameWork("insert",$_CloudFrameWorkData,'TPVLogs')) 
                      $msgerror.= 'DBINSERT_ERROR: '.$db->getError();
                                                                                                
                    if(strlen($msgerror)) echo "<pre>$msgerror<pre>";                 
                   
                    
                } if($params=='response') {

                    $msgerror='';
                    if(!strlen($_POST[Ds_Date])) $msgerror.="-Missing Ds_Date\n";
                    if(!strlen($_POST[Ds_Hour])) $msgerror.="-Missing Ds_Hour\n";
                    if(!strlen($_POST[Ds_Amount])) $msgerror.="-Missing Ds_Amount\n";
                    if(!strlen($_POST[Ds_Currency])) $msgerror.="-Missing Ds_Currency\n";
                    if(!strlen($_POST[Ds_Order])) $msgerror.="-Missing Ds_Order\n";
                    if(!strlen($_POST[Ds_MerchantCode])) $msgerror.="-Missing Ds_MerchantCode\n";
                    if(!strlen($_POST[Ds_Terminal])) $msgerror.="-Missing Ds_Terminal\n";
                    if(!strlen($_POST[Ds_Signature])) $msgerror.="-Missing Ds_Signature\n";
                    if(!strlen($_POST[Ds_Response])) $msgerror.="-Missing Ds_Response\n";
                    if(!strlen($_POST[Ds_TransactionType])) $msgerror.="-Missing Ds_TransactionType\n";
                    if(!strlen($_POST[Ds_SecurePayment])) $msgerror.="-Missing Ds_SecurePayment\n";
                    // Optional
                    // if(!strlen($_POST[Ds_MerchantData])) $msgerror.="-Missing Ds_MerchantData\n";
                    if(!strlen($_POST[Ds_Card_Country])) $msgerror.="-Missing Ds_Card_Country\n";
                    if(!strlen($_POST[Ds_AuthorisationCode])) $msgerror.="-Missing Ds_AuthorisationCode\n";
                    if(!strlen($_POST[Ds_ConsumerLanguage])) $msgerror.="-Missing Ds_ConsumerLanguage\n";
                    //Optional
                    //if(!strlen($_POST[Ds_Card_Type])) $msgerror.="-Missing Ds_Card_Type\n";
                    
                    // Insert Log
                    $_CloudFrameWorkData[TPVLog_DirectoryOrganization_Id] = $this->getConf("setOrganizationId");
                    $_CloudFrameWorkData[TPVLog_TPV_Id] = 1; // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Order] = $_POST[Ds_Order]; // Sabadell will have 1
                                        
                    $_CloudFrameWorkData[TPVLog_Name] = ((strlen($msgerror))?'Error ':'OK ').'Log Sabadell TPV response'; // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Date] = date("Y-m-d H:i:s"); // Sabadell will have 1
                    $_CloudFrameWorkData[TPVLog_Info] = $msgerror." -> ".print_r($_POST,true); // Sabadell will have 1
                    if(!$db->cloudFrameWork("insert",$_CloudFrameWorkData,'TPVLogs')) 
                      $msgerror.= 'DBINSERT_ERROR: '.$db->getError();
                                                                                                
                    if(!strlen($msgerror)) echo "OK";
                    else echo "<pre>$msgerror<pre>";
                }

                $db->close();
                exit;
                                
                                
            } else {
                die("unknown action");
            }
            
            break;
        default:
            break;
    }

// Data Arrays
        $Ds_Merchant_ConsumerLanguage['0'] = 'Cliente';
        $Ds_Merchant_ConsumerLanguage['1'] = 'Castellano';
        $Ds_Merchant_ConsumerLanguage['2'] = 'Inglés';
        $Ds_Merchant_ConsumerLanguage['3'] = 'Catalán';
        $Ds_Merchant_ConsumerLanguage['4'] = 'Francés';
        $Ds_Merchant_ConsumerLanguage['5'] = 'Alemán';
        $Ds_Merchant_ConsumerLanguage['6'] = 'Holandés';
        $Ds_Merchant_ConsumerLanguage['7'] = 'Italiano';
        $Ds_Merchant_ConsumerLanguage['8'] = 'Sueco';
        $Ds_Merchant_ConsumerLanguage['9'] = 'Portugués';
        $Ds_Merchant_ConsumerLanguage['10'] = 'Valenciano';
        $Ds_Merchant_ConsumerLanguage['11'] = 'Polaco';
        $Ds_Merchant_ConsumerLanguage['12'] = 'Gallego';
        $Ds_Merchant_ConsumerLanguage['13'] = 'Euskera';

        $Ds_Merchant_TransactionType['0'] = 'Normal';
        $Ds_Merchant_TransactionType['1'] = 'Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)';
        $Ds_Merchant_TransactionType['2'] = 'Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)';
        $Ds_Merchant_TransactionType['9'] = 'Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)';
        $Ds_Merchant_TransactionType['L'] = 'Pago por subscripciones. Pago inicial';
        $Ds_Merchant_TransactionType['M'] = 'Pago por subscripciones. Siguientes cargos';

        $Ds_Merchant_Currency['978'] = 'EUR';
        $Ds_Merchant_Currency['840'] = 'USD';
        $Ds_Merchant_Currency['392'] = 'YEN';


?>