<?php
// Talking back to submitter.
$pageTitle = "Objects update form";
include 'view/header.php';

// Display email if exists
if ($safeEmail != null) {
    echo "<p class=\"center ok\">Email: ".$safeEmail."</p>";
}
?>
<p class="center">Your update request has been successfully queued into the FG scenery update requests!<br />
Unless it's rejected, the object should be updated in Terrasync within a few days.<br />
The FG community would like to thank you for your contribution!<br />
Want to update, delete or submit another position?<br /> <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/submission/">Click here to go back to the submission page.</a></p>

<?php
include 'view/footer.php';
