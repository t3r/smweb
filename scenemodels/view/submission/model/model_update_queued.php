<?php
$pageTitle = "Models update form";
require 'view/header.php';
?>


<p class="center">Your model named "<?=$updatedReq->getNewModel()->getMetadata()->getFilename()?>" has been successfully queued 
into the FG scenery database model update requests!<br />
Unless it's rejected, it should appear in Terrasync within a few days.<br />
The FG community would like to thank you for your contribution!<br />
Want to submit another model or position?<br /> <a href="http://<?=$_SERVER['SERVER_NAME']?>/submission/">Click here to go back to the submission page.</a></p>

<?php
require 'view/footer.php';