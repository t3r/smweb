<?php
$pageTitle = "Automated Submission Form";

include 'view/header.php';
?>
<p class="center">Now processing request #<?php echo $request->getId();?></p>
<p class="center ok">This query has been successfully processed into the FG scenery database! It should be taken into account in Terrasync within a few days. Thanks for your control!</p><br />
<p class="center ok">Pending entries correctly deleted from the pending request table.</p>
<?php
include 'view/footer.php';
?>