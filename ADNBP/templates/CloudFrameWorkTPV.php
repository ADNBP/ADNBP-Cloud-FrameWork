<?php if(!strlen($service)) {?>
    <div class="jumbotron">
    <h1>TPV Integration</h1>
    <b>The following TPVs, have been tested:</b>
    <ul>
        <li><a href='CloudFrameWorkTPV/SABADELL'>Sabadell May 2014</a>
    </ul>
    </div>
        
<?php } else if($service=='SABADELL') {?>
    <div class="jumbotron">
    <h1>BancSabadell Integration</h1>
    <p>The following implementation follows the requirements that you cand find in 
        <a hreh='https://drive.google.com/file/d/0B5Hz2q4YesjSLVFmZzhDVEFrQlAxakhFNzdIV0xwU01kM1dz/edit?usp=sharing' target_blank>Sabadell DOC in Mat 2014.</a> 
        You can use the ADNBP Payment System Built with <a href='https://www.bancsabadell.com/cs/Satellite/SabAtl/Terminales-punto-de-venta-(TPV)/1191332198568/en/' target=_blank>Sabadell Technology</a>
        
        or you can manage your own Sabadell TPV clicking on the follow links: Development(<a href='https://sis-t.redsys.es:25443/canales/' target='_blank'>https://sis-t.redsys.es:25443/canales/</a>)
        , Production (<a href='https://sis.redsys.es/canales/' target='_blank'>https://sis.redsys.es/canales/</a>)
     </p>
     <p>For Development Testing use the following fake credit card: Credit Card: 4548812049400004 Expire:12/12 CVV2:123 123456 
     </p>
     <p>You can use ADNBP Cloud TPV Services poiting: <pre> $this->setConf("CloudServiceUrl","http://cloud.adnbp.com/CloudFrameWorkService");</pre>
     </p>
     <p>Or you implement your own personal Services in other location. You only need to know how to create it.
        to do that, change the CloudServiceUrl config var to the URL where you are implementing it.
     </p>

     <h2>CloudServiceUrl is: <a href='<?=$this->getCloudServicesURL()?>' target='_blank'><?=$this->getCloudServicesURL()?></a></h2></p>
     </div>
     <div class="jumbotron">
     <div class="row">
          <div class="col-lg-5">
          <h2>I don't have a Sabadell TPV activated</h2>
          <p>Good.. no problem.<br/> We will provide you everything to faciliate sellings and 
              payments processing for your company.
          </p>
          <p>You need to be signed in ADNBP Cloud Services and to have activated Instant Check-Out Services. 
              If yes, just provide only 2 fields:</p>
          <p>
          <form action='#init' method='post'>
          <div class="input-group">
           <input type="text" name='TPVMerchant_Id' class="form-control" placeholder="TPVMerchant_Id">    
           <input type="text" name='TPVMerchant_Secret' class="form-control" placeholder="TPVMerchant_Secret">    
          </div>    
          </form>
          </p>
          <p><a class="btn btn-lg btn-primary" href=''>Start Here</a></p>
          </div>  
          <div class="col-lg-5">
          <h2>I DO have a Sabadell TPV</h2>
          <p>Let's start then.Be sure you have received the following information from your bank:</p>
          <p><ul>
              <li>User/Password to development TPV</li>
              <li>User/Password to Production TPV</li>
              <li>Dev.&Prod. URL to send the payments</li>
              <li>The <b>Personal Secret Word</b> that you will use to hashing verification.</li>
          </ul>
          <p>If you have all this information we can show you the basics to create your own TPV. 
          </p><p> 
              <a class="btn btn-lg btn-primary" href=''>Start Here</a>
          </p>
          </div>             
     </div>
     
     </div>
     <a name='init'></a>
     <div class="jumbotron">
         
     </div>

<table border=1><tr  valign=top><td>
    

     </td><td rowspan=2>
         
    <?php if($_ready) {?>
        <h2>Step 2/3 Confirm to send info Sabadell TPV</h2>
         <form action='<?=(!strlen($_data[TPV_URL]))?'':htmlentities($_data[TPV_URL])?>' method ='post' target='tpv'>
        <input type=hidden name=Ds_Merchant_Terminal value='<?=htmlentities($_data[Ds_Merchant_Terminal])?>'> 
    
         <input type=hidden name=UserName value='<?=htmlentities($_data[UserName])?>'> 
         <input type=hidden name=Ds_Merchant_ProductDescription value='<?=htmlentities($_data[Ds_Merchant_ProductDescription])?>'>
         <input type=hidden name=UserEmail value='<?=htmlentities($_data[UserEmail])?>'>
         <input type=hidden name=Ds_Merchant_ConsumerLanguage value='<?=htmlentities($_data[Ds_Merchant_ConsumerLanguage])?>'>
    
         <input type=hidden name=Ds_Merchant_Amount value='<?=htmlentities($_data[Ds_Merchant_Amount])?>'>
         <input type=hidden name=Ds_Merchant_Currency value='<?=htmlentities($_data[Ds_Merchant_Currency])?>'>
         <input type=hidden name=Ds_Merchant_MerchantCode value='<?=htmlentities($_data[Ds_Merchant_MerchantCode])?>'> 
         <input type=hidden name=Ds_Merchant_MerchantName value='<?=htmlentities($_data[Ds_Merchant_MerchantName])?>'>
         <input type=hidden name=Ds_Merchant_Titular value='<?=htmlentities($_data[Ds_Merchant_Titular])?>'> 
         <input type=hidden name=Ds_Merchant_TransactionType value='<?=htmlentities($_data[Ds_Merchant_TransactionType])?>'> 
         <input type=hidden name=Ds_Merchant_Order value='<?=htmlentities($_data[Ds_Merchant_Order])?>'> 
        
         <input type=hidden name=Ds_Merchant_MerchantSignature value='<?=htmlentities($Ds_Merchant_MerchantSignature)?>'> 
         <input type=hidden name=Ds_Merchant_MerchantURL value='<?=htmlentities($_data[Ds_Merchant_MerchantURL])?>'> 
         <input type=hidden name=Ds_Merchant_UrlOK value='<?=htmlentities($_data[Ds_Merchant_UrlOK])?>'>
         <input type=hidden name=Ds_Merchant_UrlKO value='<?=htmlentities($_data[Ds_Merchant_UrlKO])?>'>
        <input type=hidden name=Ds_Merchant_MerchantData value='<?=htmlentities($_data[Ds_Merchant_MerchantData])?>'> 
    
         
         <h3><?=htmlentities($_data[TPV_URL])?></h3>
         
         <p><b><?=htmlentities($_data[UserName])?> (<?=htmlentities($_data[UserEmail])?>)</b>, using the language of 
         <b><?=$Ds_Merchant_ConsumerLanguage[$_data[Ds_Merchant_ConsumerLanguage]]?></b> wants to by:
         <ul>
             <li><b><?=htmlentities($_data[Ds_Merchant_ProductDescription])?></b> 
             <li>With a total cost of 
                 <b><?=substr(htmlentities($_data[Ds_Merchant_Amount]),0,-2)?>.<?=substr(htmlentities($_data[Ds_Merchant_Amount]),-2)?> (<?=$Ds_Merchant_Currency[$_data[Ds_Merchant_Currency]]?>)</b>
             <li>In a <b><?=$Ds_Merchant_TransactionType[$_data[Ds_Merchant_TransactionType]]?></b> Type of transaction.</li>
             <li>And assigned Order: <b><?=htmlentities($_data[Ds_Merchant_Order])?></b></li>
         </ul>
         </p>
         <p>The Store <b><?=htmlentities($_data[Ds_Merchant_MerchantCode])?></b> is selling products under the brand: 
             <b><?=htmlentities($_data[Ds_Merchant_MerchantName])?></b> for the Client: <b><?=htmlentities($_data[Ds_Merchant_Titular])?></b>
         </p>
         
         <p>
          <b>- Type of Terminal: <?=htmlentities($_data[Ds_Merchant_Terminal])?></b>
          <br><b>- Callback:</b> <?=htmlentities($_data[Ds_Merchant_MerchantURL])?>
          <br><b>- OK Page:</b> <?=htmlentities($_data[Ds_Merchant_UrlOK])?>
          <br><b>- KO Page:</b> <?=htmlentities($_data[Ds_Merchant_UrlKO])?>
            
         </p>
         <p>This purchase is signed with '<b><?=htmlentities($Ds_Merchant_MerchantSignature)?></b>' secret based code 
            and <?=(strlen($_data[Ds_Merchant_MerchantData]))?'<b>'.htmlentities($_data[Ds_Merchant_MerchantData]).'</b>':'no extra '?> MerchantData
         </p>
       <input type=submit value='Go to Step 3/3'>  
            
             
         </form>
     <?php } ?>         
         
    <h1 align="center">TPV Virtual</h1>
    <iframe id=tpv name='tpv' width="100%" height="400">
        
    </iframe>        
    <b>SOPORTE TECNICO<br/>
        De lunes a domingo de 8 h a 22 h<br/>
        Teléfono: 902 365 650 (opc. 2)<br/>
        Correo electrónico: <br/>
        tpvvirtual@bancsabadell.com<br/>
        <br/> 
        incidencias sobre comunicaciones, inestabilidad del  sistema y similares <br/>
        teléfono 902 198 747, en activo las 24 horas
     </b>   
     
     </td>           
  </tr><tr><td>  
       
    </form>
        <?=$msgError?>
    </pre>
     <h2>Step 1/3 Design your order.</h2>
    <form action="?initTransaction=1" method ='post'>
    <table>
        
        
    <tr><td colspan=2><h3>About the Client</h3></td></tr>    
    <tr><td>UserName</td>
        <td>
 <?php if(strlen($this->getAuthUserData("name"))) echo htmlentities($_data[UserName]); else {?>
    <input type=text name=UserName value='<?=(!strlen($_data[UserName]))?'Your User Name':htmlentities($_data[UserName])?>' (TPVSales Code)
<?php } ?>
        </td></tr>
    <tr><td>UserEmail</td>
        <td>
 <?php if(strlen($this->getAuthUserData("email"))) echo htmlentities($_data[UserEmail]); else {?>
    <input type=text name=UserEmail value='<?=(!strlen($_data[UserEmail]))?'your@email.com':htmlentities($_data[UserEmail])?>' (TPVSales Code)
<?php } ?>
        </td></tr>
 <tr><td>Ds_Merchant_ConsumerLanguage</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_ConsumerLanguage"))) echo 'Provided'; else {?>
            <select name='Ds_Merchant_ConsumerLanguage'>
            <?php foreach ($Ds_Merchant_ConsumerLanguage as $key => $value) {?>
                  <option  value='<?=$key?>' <?=($_data[Ds_Merchant_ConsumerLanguage]==$key)?'selected':''?>><?=$key?> = <?=$value?></option>
                
            <?php } ?>
        </select>
<?php } ?>
    </td></tr>
       
    <tr><td colspan=2><h3>About the Product/Order</h3></td></tr>
    
<?php if(is_array($TPV_products) && !strlen($_data[Ds_Merchant_ProductDescription])) { ?>
    
    <tr><td>CRMProduct</td>
        <td>    
            <select name='TPV_product'>
            <?php foreach ($TPV_products as $key => $value) {?>
                  <option  value='<?=$key?>' <?=($_POST[TPV_product]==$key)?'selected':''?>><?=$value[CRMProduct_Name]?> - <?=$value[CRMProduct_Price]?> <?=$value[CRMProduct_Currency]?></option>
            <?php } ?>        
            </select>
            <br>Units:  <input size='3' type=text name=TPV_productUnits value='<?=(strlen($_POST[TPV_productUnits]))?htmlentities($_POST[TPV_productUnits]):1?>'>
      </td></tr>   
    
<?php } else { ?>    
    
    <tr><td>Ds_Merchant_ProductDescription</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_ProductDescription")))  echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_ProductDescription value='<?=(!strlen($_data[Ds_Merchant_ProductDescription]))?'Product to Buy':htmlentities($_data[Ds_Merchant_ProductDescription])?>' (it will be shown in the Page)
<?php } ?>
    </td></tr>                
    <tr><td>Ds_Merchant_Amount</td>
        <td>
 <?php  if(strlen($this->getConf("Ds_Merchant_Amount"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_Amount value='<?=(!strlen($_data[Ds_Merchant_Amount]))?'100':htmlentities($_data[Ds_Merchant_Amount])?>'> equivale a 1,00 (las dos ultimas posiciones son decimales)
<?php } ?>
    </td></tr>
<?php }  ?>
     <tr><td>Ds_Merchant_TransactionType</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_TransactionType"))) echo 'Provided'; else {?>
    <select name='Ds_Merchant_TransactionType'>
            <?php foreach ($Ds_Merchant_TransactionType as $key => $value) {?>
                  <option  value='<?=$key?>' <?=($_data[Ds_Merchant_TransactionType]==$key)?'selected':''?>><?=$key?> = <?=$value?></option>
            <?php } ?>        
        
        </select>
<?php } ?>
    </td></tr>
    <tr><td>Ds_Merchant_Currency</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_Currency")))  echo 'Provided'; else {?>
            <select name='Ds_Merchant_Currency'>
            <?php foreach ($Ds_Merchant_Currency as $key => $value) {?>
                  <option  value='<?=$key?>' <?=($_data[Ds_Merchant_Currency]==$key)?'selected':''?>><?=$key?> = <?=$value?></option>
            <?php } ?>                   

        </select>
<?php } ?>
    </td></tr>
    <tr><td><?php if(!strlen($_data[Ds_Merchant_Order])) echo 'TPV_Order_Sufix'; else echo 'Ds_Merchant_Order';?></td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_Order"))) echo 'Provided'; else {?>
    <?php if(strlen($_data[Ds_Merchant_Order])) {?>
    <input type=hidden name=Ds_Merchant_Order   value='<?=htmlentities($_data[Ds_Merchant_Order])?>'> <?=htmlentities($_data[Ds_Merchant_Order])?>
    <?php } else {?>
    <input type=text name=TPV_Order_Sufix  size=4 maxlength=4 value='<?=(!strlen($_data[TPV_Order_Sufix]))?'TEST':htmlentities($_data[TPV_Order_Sufix])?>'> max 4 chars (A-z)
    <?php } ?>


<?php } ?>
    </td></tr>    
    
    
    
    <tr><td colspan=2><h3>About the BancSabadell TPV</h3></td></tr>  
    <tr><td>TPV_URL</td>
        <td>
 <?php  if(strlen($this->getConf("TPV_URL"))) echo 'Provided'; else {?>
       
        <select name='TPV_URL'>
        <option value='https://sis-t.redsys.es:25443/sis/realizarPago' <?=($_data[TPV_URL]=='https://sis-t.redsys.es:25443/sis/realizarPago')?'selected':''?>>Mandar a desarrollo: https://sis-t.redsys.es:25443/sis/realizarPago</option>
        <option value='https://sis.redsys.es/sis/realizarPago' <?=($_data[TPV_URL]=='https://sis.redsys.es/sis/realizarPago')?'selected':''?>>Mandar a producción: https://sis.redsys.es/sis/realizarPago</option>
        </select>
<?php } ?>
        
        </td></tr>
    <tr><td>Ds_Merchant_MerchantCode</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_MerchantCode"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantCode value='<?=(!strlen($_data[Ds_Merchant_MerchantCode]))?'327559290':htmlentities($_data[Ds_Merchant_MerchantCode])?>' (El código de tu comercio)>
<?php } ?>
    </td></tr>
    <tr><td>Ds_Merchant_MerchantName</td>
        <td>
<?php  if(strlen($this->getConf("Ds_Merchant_MerchantName"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantName value='<?=(!strlen($_data[Ds_Merchant_MerchantName]))?'Your Merchant Name':htmlentities($_data[Ds_Merchant_MerchantName])?>' (TPVSales Code)
<?php } ?>
    </td></tr>   
    <tr><td>Ds_Merchant_Titular</td>
        <td>
<?php if(strlen($this->getConf("Ds_Merchant_Titular"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_Titular value='<?=(!strlen($_data[Ds_Merchant_Titular]))?'Transaction Title':htmlentities($_data[Ds_Merchant_Titular])?>' (it will be shown in the Page)
<?php } ?>
    </td></tr>
           


    <tr><td>Ds_Merchant_Terminal</td>
        <td> 
<?php if(strlen($this->getConf("Ds_Merchant_Terminal"))) echo 'Provided'; else {?>
            <select name='Ds_Merchant_Terminal'>
        <option  value='1' <?=($_data[Ds_Merchant_Terminal]=='1')?'selected':''?>>1 = Terminal en Euros</option>
        <option  value=''>Para otros terminales solicitar al banco</option>
        </select>
<?php } ?>
    </td></tr>
    
    <tr><td>Ds_Merchant_MerchantURL</td>
        <td>
<?php  if(strlen($this->getConf("Ds_Merchant_MerchantURL"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantURL value='<?=(!strlen($_data[Ds_Merchant_MerchantURL]))?'http://cloud.adnbp.com/CloudFrameWorkTPV/SABADELL/response':htmlentities($_data[Ds_Merchant_MerchantURL])?>'>
<?php } ?>
    </td></tr>
    <tr><td>Ds_Merchant_UrlOK</td>
        <td>
<?php  if(strlen($this->getConf("Ds_Merchant_UrlOK"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantURL value='<?=(!strlen($_data[Ds_Merchant_UrlOK]))?'http://cloud.adnbp.com/CloudFrameWorkTPV/SABADELL/OK':htmlentities($_data[Ds_Merchant_UrlOK])?>'>
<?php } ?>
    </td></tr>  
    <tr><td>Ds_Merchant_UrlKO</td>
        <td>
<?php  if(strlen($this->getConf("Ds_Merchant_UrlKO"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_UrlKO value='<?=(!strlen($_data[Ds_Merchant_UrlKO]))?'http://cloud.adnbp.com/CloudFrameWorkTPV/SABADELL/KO':htmlentities($_data[Ds_Merchant_UrlKO])?>'>
<?php } ?>
    </td></tr>      
    <tr><td>TPV_Secret</td>
        <td>
<?php if(strlen($this->getConf("TPV_Secret"))) echo 'Provided'; else {?>
    <input type=password name=secret  value='<?=(!strlen($_data[TPV_Secret]))?'':htmlentities($_data[TPV_Secret])?>'> (are different in dev & prod )
<?php } ?>
   
    </td></tr>
    <tr><td>Ds_Merchant_MerchantData</td>
        <td>
 <?php if(strlen($this->getConf("Ds_Merchant_MerchantData"))) echo 'Provided'; else {?>
    <input type=text name=Ds_Merchant_MerchantData value='<?=(!strlen($_data[Ds_Merchant_MerchantData]))?'':htmlentities($_data[Ds_Merchant_MerchantData])?>'> Libre Texto devuelto por el TPV
<?php } ?>
    </td></tr>
  </table>
  <input type=submit value='Go to Step 2/3'>  
  </form>
</td>
</tr></table>     
 <?php } ?>
