<?php 
/**
 * Error page
 * To use this page, please instantiate the following strings:
 * - $pageTitle   : page's title
 * - $processText : contains the actual process
 * - $errorText   : contains the error message
 * - $adviseText  : contains advise about what to about to correct the error
 *
**/

require "view/header.php"; ?>


<p class="center">
<?php 
    if(isset($processText)) {
        echo $processText;
    }
?>
</p>

<p class="center warning">
<?php echo $errorText;?>
</p>

<p class="center">
<?php 
    if(isset($adviseText)) {
        echo $adviseText;
    }
?>
</p>

<p class="center">The FlightGear team.</p>


<?php require "view/footer.php"; ?>