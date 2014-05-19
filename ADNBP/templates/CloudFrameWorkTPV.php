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
    <p> To manage you Sabadell TPV you can click on the follow links:
        <ul>Development: <a href='https://sis-t.redsys.es:25443/canales/' target='_blank'>https://sis-t.redsys.es:25443/canales/</a></ul>    
        <ul>Development: <a href='https://sis.redsys.es/canales/' target='_blank'>https://sis.redsys.es/canales/</a></ul>    
        
    </p>
    
  <?php if(strlen($_POST[type])) {?>
    <h2>Step 2/3 Confirm to send info Sabadell TPV</h2>
     <form action='<?=(!strlen($_POST[type]))?'':htmlentities($_POST[type])?>' method ='post' target='tpv'>
    <table>
    <tr><td colspan=2>Send order to: <?=htmlentities($_POST[type])?></td></tr>
    <tr><td>Ds_Merchant_Amount</td>
        <td>
    <input type=hidden name=Ds_Merchant_Amount value='<?=htmlentities($_POST[Ds_Merchant_Amount])?>'> <?=htmlentities($_POST[Ds_Merchant_Amount])?>
    </td></tr>
    <tr><td>Ds_Merchant_TransactionType: </td>
        <td>
    <input type=hidden name=Ds_Merchant_TransactionType value='<?=htmlentities($_POST[Ds_Merchant_TransactionType])?>'> <?=htmlentities($_POST[Ds_Merchant_TransactionType])?>
    </td></tr><tr><td>Ds_Merchant_Currency: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Currency value='<?=htmlentities($_POST[Ds_Merchant_Currency])?>'> <?=htmlentities($_POST[Ds_Merchant_Currency])?>
    </td></tr><tr><td>Ds_Merchant_Order: </td>
        <td>
    <input type=hidden name=Ds_Merchant_Order value='<?=htmlentities($_POST[Ds_Merchant_Order])?>'> <?=htmlentities($_POST[Ds_Merchant_Order])?>
    </td></tr><tr><td>Ds_Merchant_MerchantCode: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantCode value='<?=htmlentities($_POST[Ds_Merchant_MerchantCode])?>'> <?=htmlentities($_POST[Ds_Merchant_MerchantCode])?>
            
    </td></tr><tr><td>Ds_Merchant_Terminal: </td>
        <td> 
    <input type=hidden name=Ds_Merchant_Terminal value='<?=htmlentities($_POST[Ds_Merchant_Terminal])?>'> <?=htmlentities($_POST[Ds_Merchant_Terminal])?>
    </td></tr><tr><td>Ds_Merchant_MerchantURL: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantURL value='<?=htmlentities($_POST[Ds_Merchant_MerchantURL])?>'> <?=htmlentities($_POST[Ds_Merchant_MerchantURL])?>
    </td></tr><tr><td>Ds_Merchant_MerchantSignature: </td>
        <td>
    <input type=hidden name=Ds_Merchant_MerchantSignature value='<?=htmlentities($Ds_Merchant_MerchantSignature)?>'> <?=htmlentities($Ds_Merchant_MerchantSignature)?>
     [strtoupper(sha1(
                $_POST[Ds_Merchant_Amount]
               .$_POST[Ds_Merchant_Order]
               .$_POST[Ds_Merchant_MerchantCode]
               .$_POST[Ds_Merchant_Currency]
               .$_POST[Ds_Merchant_TransactionType]
               .$_POST[Ds_Merchant_MerchantURL]
               .$_POST[secret]
               ));]
    </td></tr>
  </table>         
   <input type=submit value='Go to Step 3/3'> it will target a _blank page.  
        
         
     </form>
 <?php } ?>     
    
    <h2>Step 1/3 Design your order.</h2>
    <form  method ='post'>
    <table>
    <tr><td colspan=2><select name='type'>
        <option value='https://sis-t.redsys.es:25443/sis/realizarPago' <?=($_POST[type]=='https://sis-t.redsys.es:25443/sis/realizarPago')?'selected':''?>>Mandar a desarrollo: https://sis-t.redsys.es:25443/sis/realizarPago</option>
        <option value='https://sis.redsys.es/sis/realizarPago' <?=($_POST[type]=='https://sis.redsys.es/sis/realizarPago')?'selected':''?>>Mandar a producción: https://sis.redsys.es/sis/realizarPago</option>
        
        </select></td></tr>
    <tr><td>Ds_Merchant_Amount</td>
        <td>
    <input type=text name=Ds_Merchant_Amount value='<?=(!strlen($_POST[Ds_Merchant_Amount]))?'100':htmlentities($_POST[Ds_Merchant_Amount])?>'> equivale a 1,00 (las dos ultimas posiciones son decimales)
    </td></tr>
    <tr><td>Ds_Merchant_TransactionType</td>
        <td>
    <select name='Ds_Merchant_TransactionType'>
        <option value='0' <?=($_POST[Ds_Merchant_TransactionType]=='0')?'selected':''?>>0 = Normal</option>
        <option value='1' <?=($_POST[Ds_Merchant_TransactionType]=='1')?'selected':''?>>1 = Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='2' <?=($_POST[Ds_Merchant_TransactionType]=='2')?'selected':''?>>2 = Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='9' <?=($_POST[Ds_Merchant_TransactionType]=='9')?'selected':''?>>9 = Conformación de Preautorización (sólo Hoteles, Agencia y Alquiler vehículos)</option>
        <option value='L' <?=($_POST[Ds_Merchant_TransactionType]=='L')?'selected':''?>>L = Pago por subscripciones. Pago inicial</option>
        <option value='M' <?=($_POST[Ds_Merchant_TransactionType]=='M')?'selected':''?>>M = Pago por subscripciones. Siguientes cargos</option>
        
        </select>
    </td></tr><tr><td>Ds_Merchant_Currency</td>
        <td>
            <select name='Ds_Merchant_Currency'>
        <option  value='978' <?=($_POST[Ds_Merchant_Currency]=='978')?'selected':''?>>978 = EUR</option>
        <option  value='840' <?=($_POST[Ds_Merchant_Currency]=='840')?'selected':''?>>840 = USD</option>
        <option  value='392' <?=($_POST[Ds_Merchant_Currency]=='392')?'selected':''?>>392 = YEN</option>
        </select>
    </td></tr><tr><td>Ds_Merchant_Order</td>
        <td>
    <input type=text name=Ds_Merchant_Order  value='<?=(!strlen($_POST[Ds_Merchant_Order]))?'0001PRUEBA':htmlentities($_POST[Ds_Merchant_Order])?>'> (Max. 10 num/letras, siendo los 4 primeros números)
    </td></tr><tr><td>Ds_Merchant_MerchantCode</td>
        <td>
    <input type=text name=Ds_Merchant_MerchantCode value='<?=(!strlen($_POST[Ds_Merchant_MerchantCode]))?'327559290':htmlentities($_POST[Ds_Merchant_MerchantCode])?>' (El código de tu comercio)>
    </td></tr><tr><td>Ds_Merchant_Terminal</td>
        <td> <select name='Ds_Merchant_Terminal'>
        <option  value='1' <?=($_POST[Ds_Merchant_Terminal]=='1')?'selected':''?>>1 = Terminal en Euros</option>
        <option  value=''>Para otros terminales solicitar al banco</option>
        </select>
    </td></tr><tr><td>Ds_Merchant_MerchantURL</td>
        <td>
    <input type=text name=Ds_Merchant_MerchantURL value='<?=(!strlen($_POST[Ds_Merchant_MerchantURL]))?'http://www.adnbp.com/CloudFrameWorkTPV/SABADELL/response':htmlentities($_POST[Ds_Merchant_MerchantURL])?>'>
    </td></tr><tr><td>Your Merchant secret Word </td>
        <td>
    <input type=password name=secret  value='<?=(!strlen($_POST[secret]))?'':htmlentities($_POST[secret])?>'> (are different in dev & prod )
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
