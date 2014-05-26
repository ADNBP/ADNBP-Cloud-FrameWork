<?php
// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php
    list($foo,$script,$service,$params) = split('/',$this->_url,4);
    
    // Read CRM Products if there is a conf Val.
    if(strlen($service) && !strlen($_POST[Ds_Merchant_ProductDescription]) && strlen($this->getConf("TPV_CloudServiceProducts"))) {
        $result = unserialize($this->getCloudServiceResponse($this->getConf("TPV_CloudServiceProducts")));
        if(is_array($result[data])) {
            $TPV_products = $result[data];
            unset($result);
        }
    }    
    
    switch ($service) {
        case "SABADELL":
            $this->setConf("pageCode","TPVSabadell");
            
            if(!strlen($params)) {
                
                $_data = $_POST;
                if(strlen($this->getAuthUserData("name"))) $_data[UserName] = $this->getAuthUserData("name");
                if(strlen($this->getAuthUserData("email"))) $_data[UserEmail] = $this->getAuthUserData("email");
                if(strlen($this->getAuthUserData("id"))) $_data[UserId] = $this->getAuthUserData("id");
                
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

                if(is_array($TPV_products) && strlen($_POST[TPV_product]) && strlen($_POST[TPV_productUnits])
                    && !strlen($_data[Ds_Merchant_Amount]) && is_numeric($_POST[TPV_productUnits]) 
                ) {
                    $_data[CRMProduct_Id] = $TPV_products[$_POST[TPV_product]][CRMProduct_Id    ];
                    $_data[Ds_Merchant_Amount] = $TPV_products[$_POST[TPV_product]][CRMProduct_Price] * $_POST[TPV_productUnits]*100;
                    $_data[Ds_Merchant_ProductDescription] = $_POST[TPV_productUnits].' x '.$TPV_products[$_POST[TPV_product]][CRMProduct_Name].' - '.$TPV_products[$_POST[TPV_product]][CRMProduct_Price].' '.$TPV_products[$_POST[TPV_product]][CRMProduct_Currency] ;
                }


                if($_GET[initTransaction]=='1' 
                   && strlen($_data[Ds_Merchant_Amount])
                   && strlen($_data[Ds_Merchant_MerchantCode])
                   && strlen($_data[Ds_Merchant_Currency])
                   && strlen($_data[Ds_Merchant_TransactionType])
                   && strlen($_data[Ds_Merchant_MerchantURL])
                   && strlen($_data[TPV_Secret])
                ) {
                    
                   // We have to Get an Order Name.
                   
                   if(!strlen($_data[Ds_Merchant_Order])) {
                     $result = unserialize($this->getCloudServiceResponse("TPVTransaction/init",$_data));
                     if($result[response]=="OK") {
                         $_data[Ds_Merchant_Order] =   $result[TPVTransaction_Order];
                           
                     }
                   } 
                     // _print($result);                       

                    $Ds_Merchant_MerchantSignature = strtoupper(sha1(
                    $_data[Ds_Merchant_Amount]
                   .$_data[Ds_Merchant_Order]
                   .$_data[Ds_Merchant_MerchantCode]
                   .$_data[Ds_Merchant_Currency]
                   .$_data[Ds_Merchant_TransactionType]
                   .$_data[Ds_Merchant_MerchantURL]
                   .$_data[TPV_Secret]
                   ));
                   $_data[Ds_Merchant_MerchantSignature] =   $Ds_Merchant_MerchantSignature; 
                   
                     
                    
                    $_ready = true;
                    if(!strlen($_data[TPV_URL])) { $_ready = false; $msgError.='Missing TPV_URL';}
                    if(!strlen($_data[TPV_Secret])) { $_ready = false; $msgError.='Missing TPV_Secret';}
                    if(!strlen($_data[Ds_Merchant_MerchantCode])) { $_ready = false; $msgError.='Missing Ds_Merchant_MerchantCode';}
                    if(!strlen($_data[Ds_Merchant_MerchantName])) { $_ready = false; $msgError.='Missing Ds_Merchant_MerchantName';}
                    if(!strlen($_data[Ds_Merchant_Titular])) { $_ready = false; $msgError.='Missing Ds_Merchant_Titular';}
                    if(!strlen($_data[Ds_Merchant_ProductDescription])) { $_ready = false; $msgError.='Missing Ds_Merchant_ProductDescription';}
                    if(!strlen($_data[Ds_Merchant_Amount])) { $_ready = false; $msgError.='Missing Ds_Merchant_Amount';}
                    if(!strlen($_data[Ds_Merchant_TransactionType])) { $_ready = false; $msgError.='Missing Ds_Merchant_TransactionType';}
                    if(!strlen($_data[Ds_Merchant_Currency])) { $_ready = false; $msgError.='Missing Ds_Merchant_Currency';}
                    if(!strlen($_data[Ds_Merchant_Terminal])) { $_ready = false; $msgError.='Missing Ds_Merchant_Terminal';}
                    if(!strlen($_data[Ds_Merchant_MerchantURL])) { $_ready = false; $msgError.='Missing Ds_Merchant_MerchantURL';}
                    if(!strlen($_data[Ds_Merchant_UrlOK])) { $_ready = false; $msgError.='Missing Ds_Merchant_UrlOK';}
                    if(!strlen($_data[Ds_Merchant_UrlKO])) { $_ready = false; $msgError.='Missing Ds_Merchant_UrlKO';}
                    if(!strlen($_data[Ds_Merchant_Amount])) { $_ready = false; $msgError.='Missing Ds_Merchant_Amount';}
                    if(!strlen($_data[Ds_Merchant_Order])) { $_ready = false; $msgError.='Missing Ds_Merchant_Order';}
    
                    //To add information in the URLOK AND KO
                    if($_ready) {
                        $_data[Ds_Merchant_UrlOK] .= '?Ds_Order='.urlencode($_data[Ds_Merchant_Order]);
                        $_data[Ds_Merchant_UrlKO] .= '?Ds_Order='.urlencode($_data[Ds_Merchant_Order]);
                    }
                    

                   
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

            } else if($params=='OK' || $params=='KO') {
                
               // What to show in the screen
               if($params=='OK') $output = 'Transaction OK';
               else if($params=='KO') $output = 'Transaction FAILED';
               echo $output;
                   
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