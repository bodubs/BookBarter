<?php

// ******** INCLUDE TOP ******** //
include 'top.php';

$thisURL = DOMAIN . PHP_SELF;

// INITIALIZE logged out variable
$loggedOut = false;

// ********* PROCESS TO LOG OUT ********* //
if (isset($_POST['btnLogOut'])) { // IF LOG OUT BUTTON CLICKED
    
    // ****************** security ******************
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ******** END SESSION SUPER GLOBAL ******** //
    session_destroy();
    
    // SET LOGGEDOUT TO TRUE
    $loggedOut = true;
    
} // END LOG OUT PROCESS

if ($loggedOut) { // IF LOG OUT SUCCESS
    print '<h3 class="single-title">You have logged out.</h3>';
} else {

?>

<h3>Are you sure you want to log out?</h3>

<!-- ********* DISPLAY LOG OUT FORM ******** -->
<form action="<?php print PHP_SELF; ?>" method="post" id="log-out"> 
    <!-- ***************** log out button ****************** -->
    <input type="submit" name="btnLogOut" value="Log Out" tabindex="900" id="btnLogOut">
</form>

<?php

} // END IF NOT LOGGED OUT
// ******** INCLUDE FOOTER ******** //
include 'footer.php';

?>