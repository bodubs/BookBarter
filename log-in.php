<?php

// ******** INCLUDE TOP ********* //
include 'top.php';

$thisURL = DOMAIN . PHP_SELF;

// ****************** initialize variables ****************** //
$email = '';
$password = '';
$newUserEmail = '';
$newUserPassword = '';
$newUserCheckPassword = '';
$firstName = '';
$lastName = '';
$phone = '';

// ****************** initialize error variables ****************** //
$emailError = false;
$passwordError = false;
$newUserEmailError = false;
$newUserPasswordError = false;
$newUserCheckPasswordError = false;
$firstNameError = false;
$lastNameError = false;
$phoneError = false;

// ****************** initialize misc variables and arrays ****************** //
$dataEntered = false;
$records = false;
$createAccountResults = false;
$errorMsg = array();


// ******************* process for when form is submitted ******************** //

if (isset($_POST['btnLogIn'])) {
    
    // ****************** security ******************
    
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ******************* initialize data array ******************** //
    $data = array();
    
    // ****************** sanitize data ****************** //
    
    $email = htmlentities($_POST["txtEmail"], ENT_QUOTES, "UTF-8");
    
    $password = htmlentities($_POST["txtPassword"], ENT_QUOTES, "UTF-8");
    
    // ****************** validate data ******************** //
    if ($email == '') {
        $errorMsg[] = 'Please enter your email.';
        $emailError = true;
    } elseif (!verifyEmail($email)) {
        $errorMsg[] = 'Your email address appears to be incorrect.';
        $emailError = true;
    } else {
        $data[] = $email;
    }
    
    if ($password == '') {
        $errorMsg[] = 'Please enter your password.';
        $passwordError = true;
    } else {
        $data[] = $password;
    }
    
    if (!$errorMsg) { // IF DATA IS VALID
        
        // ******************* create query ****************** //
        $query = "SELECT * FROM tblUsers WHERE ";
        $query .= "fldUserEmail = ? AND ";
        $query .= "fldPassword = ?";

        $records = [];
        // ******************* send query to mysql ******************** //
        if ($thisDatabaseReader->querySecurityOk($query,1,1)) {
            $query = $thisDatabaseReader->sanitizeQuery($query);
            $records = $thisDatabaseReader->select($query, $data);
        }
        
        if ($records) { // IF USER LOG IN SUCCESSFULL        
            // ADD TO $_SESSION SUPER GLOBAL
            $_SESSION['user'] = $records[0];
            
        } else { // NOT A USER
            $errorMsg[] = 'Your email and/or password appear to be incorrect.';
            $emailError = true;
            $passwordError = true;
        }
        
    } // END IF DATA IS VALID
} // END IF BUTTON LOG IN


// ***************** PROCESS FOR CREATING ACCOUNT ****************** //

if (isset($_POST['btnCreateAccount'])) {
    
    // ****************** security ******************
    
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ******************* initialize data array ******************** //
    $data = array();
    
    // ****************** sanitize data ****************** //
    
    $firstName = htmlentities($_POST["txtFirstName"], ENT_QUOTES, "UTF-8");
    
    $lastName = htmlentities($_POST["txtLastName"], ENT_QUOTES, "UTF-8");
    
    $newUserEmail = htmlentities($_POST["txtNewUserEmail"], ENT_QUOTES, "UTF-8");
    
    $newUserPassword = htmlentities($_POST["txtNewUserPassword"], ENT_QUOTES, "UTF-8");
    
    $newUserCheckPassword = htmlentities($_POST["txtNewUserCheckPassword"], ENT_QUOTES, "UTF-8");
    
    $phone = htmlentities($_POST["txtPhone"], ENT_QUOTES, "UTF-8");
    
    
    
    // ****************** validate data ******************** //
    $query = "SELECT * FROM tblUsers WHERE ";
    $query .= "fldUserEmail = ? ";
        
    $checkUserEmail = array($newUserEmail);
        
    // ******************* send query to mysql ******************** //
    if ($thisDatabaseReader->querySecurityOk($query,1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $createAccountResults = $thisDatabaseReader->select($query, $checkUserEmail);
    }
        
    if ($createAccountResults) { // IF ALREADY A USER
        $errorMsg[] = "Sorry, there is already an account with that email.";
        $newUserEmailError = true;
    }
    
    
    if ($firstName == '') {
        $errorMsg[] = 'Please enter your first name.';
        $firstNameError = true;
    } elseif (!verifyAlphaNum($firstName)) {
        $errorMsg[] = "Your first name appears to have extra characters.";
        $firstNameError = true;
    } else {
        $data[] = $firstName;
    }
    
    if ($lastName == '') {
        $errorMsg[] = 'Please enter your last name.';
    } elseif (!verifyAlphaNum($lastName)) {
        $errorMsg[] = "Your first name appears to have extra characters.";
        $lastNameError = true;
    } else {
        $data[] = $lastName;
    }
    
    if ($newUserEmail == '') {
        $errorMsg[] = 'Please enter your email.';
    } elseif (!verifyEmail($newUserEmail)) {
        $errorMsg[] = 'Your email address appears to be incorrect.';
        $newUserEmailError = true;
    } else {
        $data[] = $newUserEmail;
    }
    
    if ($newUserPassword == '') {
        $errorMsg[] = 'Please enter your password.';
        $newUserPassword = true;
    } else {
        $data[] = $newUserPassword;
    }
    
    if ($newUserPassword != $newUserCheckPassword) {
        $errorMsg[] = 'Passwords do not match.';
    }
    
    if ($newUserCheckPassword = '') {
        $errorMsg[] = 'Please Re-Enter your password.';
        $newUserCheckPasswordError = true;
    }
    
    if ($phone == '') {
        $errorMsg[] = 'Please enter your phone number.';
    } else {
        $data[] = $phone;
    }
    
    if (!$errorMsg) { // IF DATA IS VALID
            
        // ****************** send data to database ******************
        try {
            $thisDatabaseWriter->db->beginTransaction();
                
            // ************* CREATE QUERY ************** //
            $query2 = "INSERT INTO tblUsers SET ";
            $query2 .= "fldUserFirstName = ?, ";
            $query2 .= "fldUserLastName = ?, ";
            $query2 .= "fldUserEmail = ?, ";
            $query2 .= "fldPassword = ?, ";
            $query2 .= "fldUserPhone = ?";

            if ($thisDatabaseWriter->querySecurityOk($query2, 0)) {
                $query2 = $thisDatabaseWriter->sanitizeQuery($query2);
                $dataEntered = $thisDatabaseWriter->insert($query2, $data);
                $primaryKey = $thisDatabaseWriter->lastInsert();
            }
                
            // ****************** commit changes ******************
            $dataEntered = $thisDatabaseWriter->db->commit();
            
        } catch (PDOException $e) {
            $thisDatabase->db->rollback();
            $errorMsg[] = "There was a problem accepting your data.";
        }

        
        // ***************** SEND MAIL TO NEW USER ******************* //
        
        $message = "<p>Thank you for joining Book Barter, " . $firstName . "!</p><p>You may now log in and upload books!</p>";
        
        $to = $newUserEmail;
        $cc = '';
        $bcc = '';
        
        $from = 'Book Barter <robert.warren-iii@uvm.edu>';
        
        $subject = 'Thank You for Creating an Account!';
        
        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
        
    } // END IF DATA IS VALID
} // END IF CREATE ACCOUNT BUTTON

if ($records) { // IF LOG IN SUCCESSFULL
    
    print '<h3 class="single-title">Welcome Back!</h3>';
    
} elseif ($dataEntered) { // IF CREATE ACCOUNT SUCCESSFULL
    
    print '<h3 class="single-title">Thank you for joining Book Barter, ' . $firstName . '!</h3><h3 class="single-title">You may now log in and upload books!</h3>';
    
} else { // IF DATA NOT ENTERED
    
    if ($errorMsg) { // DISPLAY ERROR MESSAGES
        print '<p class="errors">There is a problem with your form.<br>Please fix the following mistakes:</p>';
        print '<ul class="errors">' . PHP_EOL;
        
        foreach ($errorMsg as $err) {
            print '<li>' . $err . '</li>' . PHP_EOL;
        }
        
        print '</ul>' . PHP_EOL;
    }
?>

<!-- ***************** DISPLAY FORMS ****************** -->

<!-- ****** LOG IN FORM ***** -->
<h3>Log In</h3>

<form action="<?php print PHP_SELF; ?>" method="post" id="log-in">
    
    <!-- ***************** email text box ****************** -->
    <input type="text" placeholder="Email" maxlength="60" id="txtEmail" name="txtEmail"
        <?php
        print ' value="' . $email . '"';
        ?>
            ><br>
    
    <!-- ***************** password text box ****************** -->
    <input type="password" placeholder="Password" maxlength="60" id="txtPassword" name="txtPassword"
        <?php
        print ' value="' . $password . '"';
        ?>
            ><br>
    
    <!-- ***************** submit button ****************** -->
    <input type="submit" name="btnLogIn" value="Log In" tabindex="900" id="btnLogIn">

</form>

<p id="or">OR</p>

<!-- ******** CREATE ACCOUNT FORM ******* -->
<h3>Create Account</h3>

<form action="<?php print PHP_SELF; ?>" method="post" id="create-account">
    
    <!-- ***************** first name text box ****************** -->
    <input type="text" placeholder="First Name" maxlength="60" id="txtFirstName" name="txtFirstName"
        <?php
        print ' value="' . $firstName . '"';
        ?>
            ><br>
    
    <!-- ***************** last name text box ****************** -->
    <input type="text" placeholder="Last Name" maxlength="60" id="txtLastName" name="txtLastName"
        <?php
        print ' value="' . $lastName . '"';
        ?>
            ><br>
    
    <!-- ***************** email text box ****************** -->
    <input type="text" placeholder="Email" maxlength="60" id="txtNewUserEmail" name="txtNewUserEmail"
        <?php
        print ' value="' . $newUserEmail . '"';
        ?>
            ><br>
    
    <!-- ***************** password text box ****************** -->
    <input type="password" placeholder="Password" maxlength="60" id="txtNewUserPassword" name="txtNewUserPassword"
        <?php
        print ' value="' . $newUserPassword . '"';
        ?>
            ><br>
    
    <!-- ***************** check password text box ****************** -->
    <input type="password" placeholder="Re-Enter Password" maxlength="60" id="txtNewUserCheckPassword" name="txtNewUserCheckPassword"
        <?php
        print ' value="' . $newUserCheckPassword . '"';
        ?>
            ><br>
    
    <!-- ***************** phone text box ****************** -->
    <input type="text" placeholder="XXX-XXX-XXXX" maxlength="60" id="txtPhone" name="txtPhone"
        <?php
        print ' value="' . $phone . '"';
        ?>
            ><br>
    
    <!-- ***************** submit button ****************** -->
    <input type="submit" name="btnCreateAccount" value="Create Account" tabindex="900" id="btnCreateAccount">

</form>

<?php

} // END IF DATA NOT ENTERED

// ******** INCLUDE FOOTER ******* //
include 'footer.php';

?>