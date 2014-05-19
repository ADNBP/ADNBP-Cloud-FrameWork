<?php
// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php
    list($foo,$script,$service,$params) = split('/',$this->_url,4);
    switch ($service) {
        case "SABADELL":
            if($params=='response') {
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
                if(!strlen($_POST[Ds_MerchantData])) $msgerror.="-Missing Ds_MerchantData\n";
                if(!strlen($_POST[Ds_Card_Country])) $msgerror.="-Missing Ds_Card_Country\n";
                if(!strlen($_POST[Ds_AuthorisationCode])) $msgerror.="-Missing Ds_AuthorisationCode\n";
                if(!strlen($_POST[Ds_ConsumerLanguage])) $msgerror.="-Missing Ds_ConsumerLanguage\n";
                if(!strlen($_POST[Ds_Card_Type])) $msgerror.="-Missing Ds_Card_Type\n";
                
                if(!is_object($db)){
                    $this->loadClass("db/CloudSQL");
                    $db = new CloudSQL();
                }  
                if($db->connect()) {
                        // Insert Log
                        $_CloudFrameWorkData[TPVLog_DirectoryOrganization_Id] = $this->getConf("setOrganizationId");
                        $_CloudFrameWorkData[TPVLog_TPV_Id] = 1; // Sabadell will have 1
                        
                        $_CloudFrameWorkData[TPVLog_Name] = ((strlen($msgerror))?'Error ':'OK ').'Log Sabadell TPV response'; // Sabadell will have 1
                        $_CloudFrameWorkData[TPVLog_Date] = date("Y-m-d H:i:s"); // Sabadell will have 1
                        $_CloudFrameWorkData[TPVLog_Info] = $msgerror." -> ".serialize($_POST); // Sabadell will have 1
                        if(!$db->cloudFrameWork("insert",$_CloudFrameWorkData,'TPVLogs')) 
                          $msgerror.= 'DBINSERT_ERROR: '.$db->getError();
                                                                                            
                  $db->close(); 
                } else {
                  $msgerror.= '- Error connection DB: '.$db->getError();
                }
                
                if(!strlen($msgerror)) echo "OK";
                else echo "<pre>$msgerror<pre>";
                
                exit;
                                
                                
            } else {
            //include_once 'file';
            $Ds_Merchant_MerchantSignature = strtoupper(sha1(
                $_POST[Ds_Merchant_Amount]
               .$_POST[Ds_Merchant_Order]
               .$_POST[Ds_Merchant_MerchantCode]
               .$_POST[Ds_Merchant_Currency]
               .$_POST[Ds_Merchant_TransactionType]
               .$_POST[Ds_Merchant_MerchantURL]
               .$_POST[secret]
               ));
            }
            break;
        default:

            break;
    }
?>