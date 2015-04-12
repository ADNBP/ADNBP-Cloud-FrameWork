<?php

use google\appengine\api\cloud_storage\CloudStorageTools;

$bucket = 'coscms-bucket';

if (!empty($_FILES)) {  
    print_r($_FILES);
}


$options = [ 'gs_bucket_name' => $bucket ];
$upload_url = CloudStorageTools::createUploadUrl('/test.php', $options);
?>
<form action="<?php echo $upload_url?>" enctype="multipart/form-data" method="post" accept-charset="utf-8">
Files to upload: <br>
<input type="file" name="uploaded_files" size="40">
<input type="submit" value="Send">
</form>