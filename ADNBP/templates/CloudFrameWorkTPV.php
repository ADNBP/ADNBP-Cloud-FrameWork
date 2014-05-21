<?php if(!strlen($service)) {?>
    
    <h1>TPV Integration</h1>
    <b>The following TPVs, have been tested:</b>
    <ul>
        <li><a href='CloudFrameWorkTPV/SABADELL'>Sabadell May 2014</a>
    </ul>
        
<?php } else if($service=='SABADELL') {?>
<table border=1><tr><td>
    
  
    <h1>BancSabadell Integration</h1>
    <p>The following example is taken from Sabadell DOC in Mat 2014.</p>
    <p> To manage you Sabadell TPV you can click on the follow links:</p>
        Development: 
        <ul>
        <li><a href='https://sis-t.redsys.es:25443/canales/' target='_blank'>https://sis-t.redsys.es:25443/canales/</a>   
        <li>Test Credit Card: 4548812049400004 Expire:12/12 CVV2:123 123456</a>
            
        </ul>    
        Production:
        <ul>
         <li><a href='https://sis.redsys.es/canales/' target='_blank'>https://sis.redsys.es/canales/</a>
         </ul> 
         
     <b>SOPORTE TECNICO<br/>
        De lunes a domingo de 8 h a 22 h<br/>
        Teléfono: 902 365 650 (opc. 2)<br/>
        Correo electrónico: <br/>
        tpvvirtual@bancsabadell.com<br/>
        <br/> 
        incidencias sobre comunicaciones, inestabilidad del  sistema y similares <br/>
        teléfono 902 198 747, en activo las 24 horas
     </b>   
                
    
  <?php if($_ready) {?>
    <h2>Step 2/3 Confirm to send info Sabadell TPV</h2>
     <form action='<?=(!strlen($_data[TPV_URL]))?'':htmlentities($_data[TPV_URL])?>' method ='post' target='tpv'>
    <table>
    <tr><td colspan=2>Send order to: <?=htmlentities($_data[type])?></td></tr>
    <tr><td>Ds_Merchant_MerchantCode: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantCode value='<?=htmlentities($_data[Ds_Merchant_MerchantCode])?>'> <?=htmlentities($_data[Ds_Merchant_MerchantCode])?>
            
    </td></tr>
    <tr><td>Ds_Merchant_MerchantName: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantName value='<?=htmlentities($_data[Ds_Merchant_MerchantName])?>'> <?=htmlentities($_data[Ds_Merchant_MerchantName])?>
    </td></tr>    
    <tr><td>Ds_Merchant_Titular: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Titular value='<?=htmlentities($_data[Ds_Merchant_Titular])?>'> <?=htmlentities($_data[Ds_Merchant_Titular])?>
    </td></tr>
    <tr><td>Ds_Merchant_ProductDescription: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Titular value='<?=htmlentities($_data[Ds_Merchant_ProductDescription])?>'> <?=htmlentities($_data[Ds_Merchant_ProductDescription])?>
    </td></tr>    <tr><td>Ds_Merchant_ProductDescription</td>
        <td>
    <input type=hidden name=Ds_Merchant_Amount value='<?=htmlentities($_data[Ds_Merchant_Amount])?>'> <?=htmlentities($_data[Ds_Merchant_Amount])?>
    </td></tr>

    <tr><td>Ds_Merchant_TransactionType: </td>
        <td>
    <input type=hidden name=Ds_Merchant_TransactionType value='<?=htmlentities($_data[Ds_Merchant_TransactionType])?>'> <?=htmlentities($_data[Ds_Merchant_TransactionType])?>
    </td></tr><tr><td>Ds_Merchant_Currency: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Currency value='<?=htmlentities($_data[Ds_Merchant_Currency])?>'> <?=htmlentities($_data[Ds_Merchant_Currency])?>
    </td></tr><tr><td>Ds_Merchant_Order: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Order value='<?=htmlentities($_data[Ds_Merchant_Order])?>'> <?=htmlentities($_data[Ds_Merchant_Order])?>
    </td></tr>

    <tr><td>Ds_Merchant_Terminal: </td>
        <td> 
    <input type=hidden name=Ds_Merchant_Terminal value='<?=htmlentities($_data[Ds_Merchant_Terminal])?>'> <?=htmlentities($_data[Ds_Merchant_Terminal])?>
    </td></tr>
    <tr><td>Ds_Merchant_MerchantURL: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantURL value='<?=htmlentities($_data[Ds_Merchant_MerchantURL])?>'> <?=htmlentities($_data[Ds_Merchant_MerchantURL])?>
    </td></tr>
    <tr><td>Ds_Merchant_UrlOK: </td>
        <td>
    <input type=hidden name=Ds_Merchant_UrlOK value='<?=htmlentities($_data[Ds_Merchant_UrlOK])?>'> <?=htmlentities($_data[Ds_Merchant_UrlOK])?>
    </td></tr>
    <tr><td>Ds_Merchant_UrlKO: </td>
        <td>
    <input type=hidden name=Ds_Merchant_UrlKO value='<?=htmlentities($_data[Ds_Merchant_UrlKO])?>'> <?=htmlentities($_data[Ds_Merchant_UrlKO])?>
    </td></tr>
    <tr><td>Ds_Merchant_MerchantData</td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantData value='<?=htmlentities($_data[Ds_Merchant_MerchantData])?>'> <?=htmlentities($_data[Ds_Merchant_MerchantData])?>
    </td></tr>    
    
    <tr><td>Ds_Merchant_MerchantSignature: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantSignature value='<?=htmlentities($Ds_Merchant_MerchantSignature)?>'> <?=htmlentities($Ds_Merchant_MerchantSignature)?>
     [strtoupper(sha1(
                $_data[Ds_Merchant_Amount]
               .$_data[Ds_Merchant_Order]
               .$_data[Ds_Merchant_MerchantCode]
               .$_data[Ds_Merchant_Currency]
               .$_data[Ds_Merchant_TransactionType]
               .$_data[Ds_Merchant_MerchantURL]
               .$_data[secret]
               ));]
    </td></tr>
    
  </table>         
   <input type=submit value='Go to Step 3/3'> it will target a _blank page.  
        
         
     </form>
 <?php } ?>     
    
    <h2>Step 1/3 Design your order.</h2>
    <form  method ='post'>
    <table>
    <tr><td>TPV_URL</td>
        <td>
 <?php if(strlen($_data[TPV_URL])) echo 'Provided'; else {?>
       
        <select name='TPV_URL'>
        <option value='https://sis-t.redsys.es:25443/sis/realizarPago' <?=($_data[TPV_URL]=='https://sis-t.redsys.es:25443/sis/realizarPago')?'selected':''?>>Mandar a desarrollo: https://sis-t.redsys.es:25443/sis/realizarPago</option>
        <option value='https://sis.redsys.es/sis/realizarPago' <?=($_data[TPV_URL]=='https://sis.redsys.es/sis/realizarPago')?'selected':''?>>Mandar a producción: https://sis.redsys.es/sis/realizarPago</option>
        </select>
<?php } ?>
        
        </td></tr>
    <tr><td>Ds_Merchant_MerchantCode</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_MerchantCode])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantCode value='<?=(!strlen($_data[Ds_Merchant_MerchantCode]))?'327559290':htmlentities($_data[Ds_Merchant_MerchantCode])?>' (El código de tu comercio)>
<?php } ?>
    </td></tr>
    <tr><td>Ds_Merchant_MerchantName</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_MerchantName])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantName value='<?=(!strlen($_data[Ds_Merchant_MerchantName]))?'Your Merchant Name':htmlentities($_data[Ds_Merchant_MerchantName])?>' (TPVSales Code)
<?php } ?>
    </td></tr>   
    <tr><td>Ds_Merchant_Titular</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_Titular])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_Titular value='<?=(!strlen($_data[Ds_Merchant_Titular]))?'Transaction Title':htmlentities($_data[Ds_Merchant_Titular])?>' (it will be shown in the Page)
<?php } ?>
    </td></tr>   
    <tr><td>Ds_Merchant_ProductDescription</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_ProductDescription])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_ProductDescription value='<?=(!strlen($_data[Ds_Merchant_ProductDescription]))?'Product to Buy':htmlentities($_data[Ds_Merchant_ProductDescription])?>' (it will be shown in the Page)
<?php } ?>
    </td></tr>                
    <tr><td>Ds_Merchant_Amount</td>
        <td>
 <?php if(strlen($_data[Ds_Merchant_Amount])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_Amount value='<?=(!strlen($_data[Ds_Merchant_Amount]))?'100':htmlentities($_data[Ds_Merchant_Amount])?>'> equivale a 1,00 (las dos ultimas posiciones son decimales)
<?php } ?>
    </td></tr>

     <tr><td>Ds_Merchant_TransactionType</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_TransactionType])) echo 'Provided'; else {?>
      else {?>
    <select name='Ds_Merchant_TransactionType'>
        <option value='0' <?=($_data[Ds_Merchant_TransactionType]=='0')?'selected':''?>>0 = Normal</option>
        <option value='1' <?=($_data[Ds_Merchant_TransactionType]=='1')?'selected':''?>>1 = Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='2' <?=($_data[Ds_Merchant_TransactionType]=='2')?'selected':''?>>2 = Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='9' <?=($_data[Ds_Merchant_TransactionType]=='9')?'selected':''?>>9 = Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='L' <?=($_data[Ds_Merchant_TransactionType]=='L')?'selected':''?>>L = Pago por subscripciones. Pago inicial</option>
        <option value='M' <?=($_data[Ds_Merchant_TransactionType]=='M')?'selected':''?>>M = Pago por subscripciones. Siguientes cargos</option>
        
        </select>
<?php } ?>
    </td></tr><tr><td>Ds_Merchant_Currency</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_Currency])) echo 'Provided'; else {?>
            <select name='Ds_Merchant_Currency'>
        <option  value='978' <?=($_data[Ds_Merchant_Currency]=='978')?'selected':''?>>978 = EUR</option>
        <option  value='840' <?=($_data[Ds_Merchant_Currency]=='840')?'selected':''?>>840 = USD</option>
        <option  value='392' <?=($_data[Ds_Merchant_Currency]=='392')?'selected':''?>>392 = YEN</option>
        </select>
<?php } ?>
    </td></tr><tr><td>Ds_Merchant_Order</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_Order])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_Order  value='<?=(!strlen($_data[Ds_Merchant_Order]))?'0001PRUEBA':htmlentities($_data[Ds_Merchant_Order])?>'> (Max. 10 num/letras, siendo los 4 primeros números)
<?php } ?>
    </td></tr>

    <tr><td>Ds_Merchant_Terminal</td>
        <td> 
<?php if(strlen($_data[Ds_Merchant_Terminal])) echo 'Provided'; else {?>
            <select name='Ds_Merchant_Terminal'>
        <option  value='1' <?=($_data[Ds_Merchant_Terminal]=='1')?'selected':''?>>1 = Terminal en Euros</option>
        <option  value=''>Para otros terminales solicitar al banco</option>
        </select>
<?php } ?>
    </td></tr>
    
    <tr><td>Ds_Merchant_MerchantURL</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_MerchantURL])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantURL value='<?=(!strlen($_data[Ds_Merchant_MerchantURL]))?'http://www.adnbp.com/CloudFrameWorkTPV/SABADELL/response':htmlentities($_data[Ds_Merchant_MerchantURL])?>'>
<?php } ?>
    </td></tr>
    <tr><td>Ds_Merchant_UrlOK</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_UrlOK])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantURL value='<?=(!strlen($_data[Ds_Merchant_UrlOK]))?'http://www.adnbp.com/CloudFrameWorkTPV/SABADELL/OK':htmlentities($_data[Ds_Merchant_UrlOK])?>'>
<?php } ?>
    </td></tr>  
    <tr><td>Ds_Merchant_UrlKO</td>
        <td>
<?php if(strlen($_data[Ds_Merchant_UrlKO])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_UrlKO value='<?=(!strlen($_data[Ds_Merchant_UrlKO]))?'http://www.adnbp.com/CloudFrameWorkTPV/SABADELL/KO':htmlentities($_data[Ds_Merchant_UrlKO])?>'>
<?php } ?>
    </td></tr>      
    <tr><td>TPV_Secret</td>
        <td>
<?php if(strlen($_data[TPV_Secret])) echo 'Provided'; else {?>
    <input type=password name=secret  value='<?=(!strlen($_data[TPV_Secret]))?'':htmlentities($_data[TPV_Secret])?>'> (are different in dev & prod )
<?php } ?>
   
    </td></tr>
    <tr><td>Ds_Merchant_MerchantData</td>
        <td>
 <?php if(strlen($_data[Ds_Merchant_MerchantData])) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantData value='<?=(!strlen($_data[Ds_Merchant_MerchantData]))?'':htmlentities($_data[Ds_Merchant_MerchantData])?>'> Libre Texto devuelto por el TPV
<?php } ?>
    </td></tr>
  </table>
  <input type=submit value='Go to Step 2/3'>  
  </form>
</td>
<td valign="top" width="50%">
    <h1 align="center">TPV Virtual</h1>
    <iframe id=tpv name='tpv' width="100%" height="400">
        
    </iframe>
    
</td>
</tr></table>     
 <?php } ?>
