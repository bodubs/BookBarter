<?php

include 'top.php';

$thisURL = DOMAIN . PHP_SELF;

// ****************** initialize variables ******************
$pmkItemId = -1;
$title = '';
$authorFirstName = '';
$authorLastName = '';
$ISBN = '';
$subject = '';
$type = '';

// ****************** initialize error variables ****************** //
$titleError = false;
$authorFirstNameError = false;
$authorLastNameError = false;
$ISBNError = false;
$subjectError = false;
$typeError = false;

// ****************** initialize misc variables ****************** //
$dataEntered = false;
$dataDeleted = false;
$tagDeleted = false;
$tagsDeleted = false;
$mailed = false;
$update = false;

// ****************** initialize arrays ****************** //
$tags = array();
$tagsData = array();
$errorMsg = array();
$data = array();





//************** if edit show what was previously put ******************//
if (isset($_GET['id'])) {
    
    $pmkItemId = (int) htmlentities($_GET["id"], ENT_QUOTES, "UTF-8");
    
    // ****************** create query to display info which will be updated ****************** //
    $query = 'SELECT fldTitle, fldAuthorFirstName, fldAuthorLastName, fldISBNNumber, fldSubject, fldType ';
    $query .= 'FROM tblBooks WHERE pmkItemId = ?';
    
    $data[] = $pmkItemId;
    
    if ($thisDatabaseReader->querySecurityOk($query, 1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $book = $thisDatabaseReader->select($query, $data);
    }
    
    $title = $book[0]['fldTitle'];
    $authorFirstName = $book[0]['fldAuthorFirstName'];
    $authorLastName = $book[0]['fldAuthorLastName'];
    $ISBN = $book[0]['fldISBNNumber'];
    $subject = $book[0]['fldSubject'];
    $type = $book[0]['fldType'];
    
       
    $query = 'DELETE FROM tblBooksTags WHERE pfkItemId = ?';
    
    if ($thisDatabaseWriter->querySecurityOk($query, 1)) {
        $query = $thisDatabaseWriter->sanitizeQuery($query);
        $tagsResults = $thisDatabaseWriter->delete($query, $data);
    }   
}






//************** process for when code is submitted ****************** //

if (isset($_POST['btnSubmit'])) {
    
    // ****************** security ****************** //
    
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ****************** sanitize data ****************** //
    
    $pmkItemId = (int) htmlentities($_POST["hidItemId"], ENT_QUOTES, "UTF-8");
    if ($pmkItemId > 0) {
        $update = true;
    }
    
    $data[] = $_SESSION['user']["pmkUserId"];
    
    $title = htmlentities($_POST["txtTitle"], ENT_QUOTES, "UTF-8");
    $data[] = $title;
    
    $authorFirstName = htmlentities($_POST["txtAuthorFirstName"], ENT_QUOTES, "UTF-8");
    $data[] = $authorFirstName;
    
    $authorLastName = htmlentities($_POST["txtAuthorLastName"], ENT_QUOTES, "UTF-8");
    $data[] = $authorLastName;
    
    $ISBN = htmlentities($_POST["txtISBN"], ENT_QUOTES, "UTF-8");
    $data[] = $ISBN;
    
    $subject = htmlentities($_POST["lstSubject"], ENT_QUOTES, "UTF-8");
    $data[] = $subject;
    
    if (isset($_POST["radType"])) {
        $type = htmlentities($_POST["radType"], ENT_QUOTES, "UTF-8");
        $data[] = $type;
    } else {
        $errorMsg[] = "Please enter the type of book you are uploading.";
        $typeError = true;
    }
    
    // **************** save tags to tags data array ***************** //
    if (isset($_POST["chkhardback"])){
        $chkHardBack = htmlentities($_POST["chkhardback"], ENT_QUOTES, "UTF-8");
        $tagsData[] =$chkHardBack;         
    }
    
    if (isset($_POST["chklooseleaf"])){
        $chkLooseLeaf = htmlentities($_POST["chklooseleaf"], ENT_QUOTES, "UTF-8");
        $tagsData[] =$chkLooseLeaf;         
    }
    
    if (isset($_POST["chkoptional"])){
        $chkOptional = htmlentities($_POST["chkoptional"], ENT_QUOTES, "UTF-8");
        $tagsData[] =$chkOptional;         
    }
    
    if (isset($_POST["chkrequired"])){
        $chkRequired = htmlentities($_POST["chkrequired"], ENT_QUOTES, "UTF-8");
        $tagsData[] =$chkRequired;         
    }
    
    // ****************** validation ***************** //
    if ($title == '') {
        $errorMsg[] = 'Please add a title.';
        $titleError = true;
    }
    
    if ($authorFirstName == '') {
        $errorMsg[] = "Please enter the author's first name";
        $authorFirstNameError = true;
    } elseif (!verifyAlphaNum($authorFirstName)) {
        $errorMsg[] = "The author's first name appears to have extra character.";
        $authorFirstNameError = true;
    }
    
    if ($authorLastName == '') {
        $errorMsg[] = "Please enter the author's last name.";
        $authorLastNameError = true;
    } elseif (!verifyAlphaNum($authorLastName)) {
        $errorMsg[] = "The author's last name appears to have extra character.";
        $authorLastNameError = true;
    }
    
    if ($ISBN == '') {
        $errorMsg[] = "Please enter the ISBN Number.";
        $ISBNError = true;
    }
    
    if ($subject == '') {
        $errorMsg[] = "Please enter the subject the book is used for.";
        $subjectError = true;
    }
    
    // ****************** check to see if form has passed validation ******************  //  
    
    if (!$errorMsg) {
        print "<!-- PROCESSED FORM THAT HAS PASSED VALIDATION -->";
        if (DEBUG) {
            print "<h2>Your Form has been Submitted</h2>";
        }
        
        // ****************** send data to database ****************** //
        try {
            $thisDatabaseWriter->db->beginTransaction();
            
            // ****************** create query for updating or inserting ****************** //
            if ($update) {
                $query = "UPDATE tblBooks SET ";
            } else {
                $query = "INSERT INTO tblBooks SET ";
            }
            $query .= "fnkUserId = ?, ";
            $query .= "fldTitle = ?, ";
            $query .= "fldAuthorFirstName = ?, ";
            $query .= "fldAuthorLastName = ?, ";
            $query .= "fldISBNNumber = ?, ";
            $query .= "fldSubject = ?, ";
            $query .= "fldType = ? ";        
            
            if (DEBUG) {
                $thisDatabaseWriter->TestSecurityQuery($query, 0);
                print_r($data);
            }
            
            if ($update) {
                $query .= "WHERE pmkItemId = ?";
                $data[] = $pmkItemId;
                
                if ($thisDatabaseWriter->querySecurityOk($query, 1)) {
                    $query = $thisDatabaseWriter->sanitizeQuery($query);
                    $results = $thisDatabaseWriter->update($query, $data);
                }
                
            } else {
            
                if ($thisDatabaseWriter->querySecurityOk($query, 0)) {
                    $query = $thisDatabaseWriter->sanitizeQuery($query);
                    $results = $thisDatabaseWriter->insert($query, $data);
                    $pmkItemId = $thisDatabaseWriter->lastInsert();
                }
                
            }
            
            $query = 'INSERT INTO tblBooksTags SET pfkItemId = ?, pfkTag = ?';
            
            foreach($tagsData as $tagData) {
                $insert = array($pmkItemId, $tagData);
                if ($thisDatabaseWriter->querySecurityOk($query, 0)) {
                    $query = $thisDatabaseWriter->sanitizeQuery($query);
                    $tagResults = $thisDatabaseWriter->insert($query, $insert);
                    if (DEBUG) {
                        print "<p>pmk= " . $pmkItemId;
                    }
                } 
            }
            
            // ****************** commit changes ****************** //
            $dataEntered = $thisDatabaseWriter->db->commit();
  
        } catch (PDOException $e) {
            $thisDatabase->db->rollback();
            $errorMsg[] = "There was a problem accepting your data.";
        }
    }
}





// ******************* Process for deleting record ********************* //

if (isset($_POST['btnDelete'])) {
    
    // ****************** security ****************** //
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ****************** sanitize data ****************** //
    $pmkItemId = (int) htmlentities($_POST["hidItemId"], ENT_QUOTES, "UTF-8");  
    
    $title = htmlentities($_POST["txtTitle"], ENT_QUOTES, "UTF-8");   
    
    $authorFirstName = htmlentities($_POST["txtAuthorFirstName"], ENT_QUOTES, "UTF-8");  
    
    $authorLastName = htmlentities($_POST["txtAuthorLastName"], ENT_QUOTES, "UTF-8");    
    
    $ISBN = htmlentities($_POST["txtISBN"], ENT_QUOTES, "UTF-8");   
    
    $subject = htmlentities($_POST["lstSubject"], ENT_QUOTES, "UTF-8");   
    
    if (isset($_POST["radType"])) {
        $type = htmlentities($_POST["radType"], ENT_QUOTES, "UTF-8");
    }
    
    try {
    
        $thisDatabaseWriter->db->beginTransaction();
        
        // ****************** create query for deleting records ****************** //
        $query = 'DELETE FROM tblBooks WHERE pmkItemId = ?';
        
        $data[] = $pmkItemId;
        
        if ($thisDatabaseWriter->querySecurityOk($query, 1)) {
            $query = $thisDatabaseWriter->sanitizeQuery($query);
            $results = $thisDatabaseWriter->delete($query, $data);
        }
        
        $dataDeleted = $thisDatabaseWriter->db->commit();
        
    } catch (PDOException $e) {
        $thisDatabaseWriter->db->rollback();
    }
}







// ****************** check to see if the data has been entered to the database ******************
if ($dataEntered) {
    
    if ($update) { // IF RECORD UPDATED
        
        print "<h3>Updated:</h3>";
        print '<h4>' . $title . '<br>';
        print $authorFirstName . ' ' . $authorLastName . '<br>';
        print $ISBN . '<br>';
        print $subject . '<br>';
        print $type . '</h4>';
        
    } else { // IF RECORD SAVED
        
        print "<h3>Saved:</h3>";
        print '<h4>' . $title . '<br>';
        print $authorFirstName . ' ' . $authorLastName . '<br>';
        print $ISBN . '<br>';
        print $subject . '<br>';
        print $type . '</h4>';
        
    } 
    
} elseif ($dataDeleted) { // IF RECORD DELETED
    
    print '<h3>Deleted:</h3>';
    print '<h4>' . $title . '<br>';
    print $authorFirstName . ' ' . $authorLastName . '<br>';
    print $ISBN . '<br>';
    print $subject . '<br>';
    print $type . '</h4>';
    
} else {
    
    if (!empty($_SESSION)) { // IF USER
    
    
        // ****************** display error messages ******************
        if ($errorMsg) {
            print '<p class="errors">There is a problem with your form.<br>Please fix the following mistakes:</p>';
            print '<ul class="errors">' . PHP_EOL;
        
            foreach ($errorMsg as $err) {
                print '<li>' . $err . '</li>' . PHP_EOL;
            }
        
            print '</ul>' . PHP_EOL;
        }
        
?>



    <!-- ****************** DISPLAY FORM ****************** -->
        
<h2>Upload A Textbook</h2>
<form action="<?php print PHP_SELF; ?>" method="post" id="upload">
    
    <input type="hidden" name="hidItemId" value="
           <?php
           print $pmkItemId;
           ?>
           ">
    
    <fieldset>
        <legend>Book Info:</legend>
        Title:<input type="text" placeholder="Title" maxlength="30" id="txtTitle" name="txtTitle"
            <?php
            if ($titleError) {
                print ' class="mistake"';
            }
            print ' value="' . $title . '"';
            ?>
                    ><br>

        Author First Name:<input type="text" placeholder="First Name" maxlength="20" id="txtAuthorFirstName" name="txtAuthorFirstName"
            <?php
            if ($authorFirstNameError) {
                print ' class="mistake"';
            }
            print ' value="' . $authorFirstName . '"';
            ?>
                                ><br>
        Author Last Name:<input type="text" placeholder="Last Name" maxlength="20" id="txtAuthorLastName" name="txtAuthorLastName"
            <?php
            if ($authorLastNameError) {
                print ' class="mistake"';
            }
            print ' value="' . $authorLastName . '"';
            ?>
                                ><br>
        
        ISBN Number:<input type="text" placeholder="ISBN" maxlength="30" id="txtISBN" name="txtISBN"
            <?php
            if ($ISBNError) {
                print ' class="mistake"';
            }
            print ' value="' . $ISBN . '"';
            ?>
                           ><br>
        
            <?php
    
            $query = 'SELECT pmkSubject FROM tblSubjects';
    
            if ($thisDatabaseReader->querySecurityOk($query, 0)) {
                $query = $thisDatabaseReader->sanitizeQuery($query);
                $schoolSubjects = $thisDatabaseReader->select($query, '');
            }
    
            ?>
        
        <label for="lstSubject">Subject
        <select name="lstSubject">
            <option value=""></option>
            <?php

            foreach ($schoolSubjects as $schoolSubject) {
                print '<option ';
                if ($subject == $schoolSubject['pmkSubject']) {
                    print ' selected="selected" ';
                }
                print 'name="lstSchoolSubject" ';
                print 'value="' . $schoolSubject["pmkSubject"] . '">' . $schoolSubject["pmkSubject"];
                print '</option>';
            }
            
            ?>
            <option value="Other">Other</option>
        </select><br>
    </label>
        
        <input type="radio" id="radType" name="radType" value="Book"
               <?php
            if ($typeError) {
                print ' class="mistake"';
            }
            ?>
               >Book<br>
        
        <input type="radio" id="radType" name="radType" value="eBook"
               <?php
            if ($typeError) {
                print ' class="mistake"';
            }
            ?>
               >eBook<br>
        
        <input type="radio" id="radType" name="radType" value="Other"
               <?php
            if ($typeError) {
                print ' class="mistake"';
            }
            ?>
               >Other<br>
    
    <!-- ****************** check box for book tags ****************** -->   
    <?php
    
    $query = 'SELECT pmkTag, fldDefaultValue FROM tblTags';
    
    if ($thisDatabaseReader->querySecurityOk($query, 0)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $tags = $thisDatabaseReader->select($query, '');
    }


    // ********* display tags ********** //
    if (is_array($tags)) {
        foreach ($tags as $tag) {

            print "\t" . '<label for="chk' . str_replace(" ", "", $tag["pmkTag"]) . '"><input type="checkbox" ';
            print ' id="chk' . str_replace(" ", "", $tag["pmkTag"]) . '" ';
            print ' name="chk' . str_replace(" ", "", $tag["pmkTag"]) . '" ';

            if ($tag["fldDefaultValue"] == 1) {
                print ' checked ';
            }

            print 'value="' . $tag["pmkTag"] . '">' . $tag["pmkTag"];
            print '</label>' . PHP_EOL;
        }
    }
    
    ?>
    
    </fieldset>
    
    <input type="submit" name="btnSubmit" value="Upload" tabindex="900" id="btnSubmit">
    
</form>
    
<?php
    
    if (isset($_GET['id'])) {
        
?>
<form action="<?php print PHP_SELF; ?>" method="post" id="frmDelete">
            
    <input type="hidden" name="hidItemId" value="<?php print $pmkItemId; ?>">
    <input type="hidden" name="txtTitle" value="<?php print $title; ?>">
    <input type="hidden" name="txtAuthorFirstName" value="<?php print $authorFirstName; ?>">
    <input type="hidden" name="txtAuthorLastName" value="<?php print $authorLastName; ?>">
    <input type="hidden" name="txtISBN" value="<?php print $ISBN; ?>">
    <input type="hidden" name="lstSubject" value="<?php print $subject; ?>">
    <input type="hidden" name="radType" value="<?php print $type; ?>">
            
    <input type="submit" name="btnDelete" value="Delete" tabindex="1900" id="btnDelete">
        
</form>
<?php
        
    } // END IF ISSET GET
        
} else { // IF NOT USER
    
    print '<h3 class="single-title">You must log in or create an account to upload a book.</h3>';
    
} // END IF NOT USER
} // IF NO DATA INPUT

// INCLUDE FOOTER
include 'footer.php';

?>