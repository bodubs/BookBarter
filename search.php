<?php

include 'top.php';

$thisURL = DOMAIN . PHP_SELF;

// ******************* initialize variables ******************** //
$universityName = '';
$title = '';
$authorFirstName = '';
$authorLastName = '';
$ISBN = '';
$subject = '';
$type = '';

// ******************* initialize error flags ******************** //
$titleError = false;
$authorFirstNameError = false;
$authorLastNameError = false;
$ISBNError = false;
$errorMsg = array();


// ******************* process for when form is submitted ******************** //

if (isset($_POST['btnSubmit'])) {
    
    // ****************** security ******************
    
    if (!securityCheck($thisURL)) {
        $msg = "<p>Security breach detected and reported.</p>";
        die($msg);
    }
    
    // ******************* initialize data array ******************** //
    $data = array();
    
    // ****************** sanitize data ****************** //
    
    $title = htmlentities($_POST["txtTitle"], ENT_QUOTES, "UTF-8");
    
    $authorFirstName = htmlentities($_POST["txtAuthorFirstName"], ENT_QUOTES, "UTF-8");
    
    $authorLastName = htmlentities($_POST["txtAuthorLastName"], ENT_QUOTES, "UTF-8");
    
    $ISBN = htmlentities($_POST["txtISBN"], ENT_QUOTES, "UTF-8");
    
    $subject = htmlentities($_POST["lstSubject"], ENT_QUOTES, "UTF-8"); 
    
    if (isset($_POST["radType"])) {
        $type = htmlentities($_POST["radType"], ENT_QUOTES, "UTF-8");       
    }
    
    // ****************** validate data ******************** //
    if ($title == '' AND $authorFirstName == '' AND $authorLastName == '' AND $ISBN == '' AND $subject == '') { // IF NO DATA ENTERED
        
        print '<p>Search too broad. Please enter some information.</p>';
        
        ?>

        <!-- ***************** display form ****************** -->
        <h2>Search</h2>

        <form action="<?php print PHP_SELF; ?>" method="post" id="search">
    
            <!-- ***************** title text box ****************** -->
            <input type="text" placeholder="Title" maxlength="60" id="txtTitle" name="txtTitle"
                <?php
                if ($titleError) {
                    print ' class="mistake"';
                }
                print ' value="' . $title . '"';
                ?>
                    ><br>
        
            <!-- ***************** author first text box ****************** -->
            <input type="text" placeholder="First Name" maxlength="20" id="txtAuthorFirstName" name="txtAuthorFirstName"
                <?php
                if ($authorFirstNameError) {
                    print ' class="mistake"';
                }
                print ' value="' . $authorFirstName . '"';
                ?>
                    ><br>
        
            <!-- ***************** author last name text box ****************** -->
            <input type="text" placeholder="Last Name" maxlength="20" id="txtAuthorLastName" name="txtAuthorLastName"
                <?php
                if ($authorLastNameError) {
                    print ' class="mistake"';
                }
                print ' value="' . $authorLastName . '"';
                ?>
                    ><br>
        
            <!-- ***************** isbn text box ****************** -->
            <input type="text" placeholder="ISBN" maxlength="30" id="txtISBN" name="txtISBN"
                <?php
                if ($ISBNError) {
                    print ' class="mistake"';
                }
                print ' value="' . $ISBN . '"';
                ?>
                    ><br>
       
            <!-- ***************** subject list box ****************** -->
            <?php
    
            // ************ query for selecting subjects *****************//
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
    
            <!-- ***************** type radio buttons ****************** -->    
            <input type="radio" name="radType" value="Book"/>Book<br>
            <input type="radio" name="radType" value="eBook"/>eBook<br>
            <input type="radio" name="radType" value="Other"/>Other<br>
        
            <!-- ***************** submit button ****************** -->
            <input type="submit" name="btnSubmit" value="Search" tabindex="900" id="btnSubmit">
    
        </form>

<?php
        
    } else { // IF DATA ENTERED
        // ****************** create query ****************** //
        $query = "SELECT * FROM tblBooks WHERE ";
        
        // ******************* initialize conditions variable ******************** //
        $CONDITIONS = 0;
    
        // ******************* validate and add to data array and query  ******************** //
        if ($title != '') {
            $query .= "fldTitle LIKE ? ";
            $data[] = "%$title%";
        }
    
        if ($authorFirstName != '') {
            if ($title != '') {
                $query .= "AND ";
                $CONDITIONS++;
            }
            $query .= "fldAuthorFirstName LIKE ? ";
            $data[] = "%$authorFirstName%";
            
        }
    
        if ($authorLastName != '') {
            if ($title != '' OR $authorFirstName != '') {
                $query .= "AND ";
                $CONDITIONS++;
            }
            $query .= "fldAuthorLastName LIKE ? ";
            $data[] = "%$authorLastName%";
        }
    
        if ($ISBN != '') {
            if ($title != '' OR $authorFirstName != '' OR $authorLastName != '') {
                $query .= "AND ";
                $CONDITIONS++;
            }
            $query .= "fldISBNNumber LIKE ? ";
            $data[] = "%$ISBN%";
        }
    
        if ($subject != '') {
            if ($title != '' OR $authorFirstName != '' OR $authorLastName != '' OR $ISBN != '') {
                $query .= "AND ";
                $CONDITIONS++;
            }
            $query .= "fldSubject LIKE ? ";
            $data[] = "%$subject%";
        }
        
        if (isset($_POST["radType"])) {
            if ($title != '' OR $authorFirstName != '' OR $authorLastName != '' OR $ISBN != '' OR $subject != '') {
                $query .= "AND ";
                $CONDITIONS++;
            }
            $query .= "fldType LIKE ? ";
            $data[] = "%$type%";
        }
    
        // ******************* send query to mysql ******************** //
        if ($thisDatabaseReader->querySecurityOk($query,1,$CONDITIONS)) {
            $query = $thisDatabaseReader->sanitizeQuery($query);
            $records = $thisDatabaseReader->select($query, $data);
        }
    
        // ******************* check for no results ******************** //
        if (!$records) {
            print '<p id="failed-search">Sorry, no results found.</p>';
        } else {
            
            print "<h2>Search Results:</h2>";
    
            // ******************* display search results ******************** //
            print '<ul id="books">';
            
            $i = 0;
    
            if (is_array($records)) { // IF RECORDS IS ARRAY
                foreach ($records as $record) { // DISPLAY EACH RECORD
        
                    $i++;
        
                    if ($i % 2 != 0) { // ADD LINE BREAK IF DIVISIBLE BY 2
                        print '<br>';
                    }
                    print '<li>';
                    print '<ul>';
                    print '<li><strong>Title:</strong> ' . $record['fldTitle'] . '</li>';
                    print '<li><strong>Author:</strong> ' . $record['fldAuthorFirstName'] . ' ' . $record['fldAuthorLastName'] . '</li>';
                    print '<li><strong>ISBN:</strong> ' . $record['fldISBNNumber'] . '</li>';
                    print '<li><strong>Subject:</strong> ' . $record['fldSubject'] . '</li>';
                    print '<li><a href="view-book.php?id=' . $record['pmkItemId'] . '">View</a></li>';
                    if (!empty($_SESSION)) { // IF IS USER
                        if ($_SESSION['user']["pmkUserId"] == $record['fnkUserId']) { // GET USER ID THROUGH SESSION SUPER GLOBAL
                            print '<li><a href="upload.php?id=' . $record['pmkItemId'] . '">Edit</a></li>';
                        }
                    }
                    print '</ul>';
                    print '</li>';
                }
            }
            print '</ul>';
        
        }
    
    }
    
} else { // IF NO DATA IN INPUT YET

?>

<!-- ***************** display form ****************** -->
<h2>Search</h2>

<form action="<?php print PHP_SELF; ?>" method="post" id="search">
    
    <!-- ***************** title text box ****************** -->
    <input type="text" placeholder="Title" maxlength="60" id="txtTitle" name="txtTitle"
        <?php
        if ($titleError) {
            print ' class="mistake"';
        }
        print ' value="' . $title . '"';
        ?>
            ><br>
        
    <!-- ***************** author first text box ****************** -->
    <input type="text" placeholder="First Name" maxlength="20" id="txtAuthorFirstName" name="txtAuthorFirstName"
        <?php
        if ($authorFirstNameError) {
            print ' class="mistake"';
        }
        print ' value="' . $authorFirstName . '"';
        ?>
            ><br>
        
    <!-- ***************** author last name text box ****************** -->
    <input type="text" placeholder="Last Name" maxlength="20" id="txtAuthorLastName" name="txtAuthorLastName"
        <?php
        if ($authorLastNameError) {
            print ' class="mistake"';
        }
        print ' value="' . $authorLastName . '"';
        ?>
            ><br>
        
    <!-- ***************** isbn text box ****************** -->
    <input type="text" placeholder="ISBN" maxlength="30" id="txtISBN" name="txtISBN"
        <?php
        if ($ISBNError) {
            print ' class="mistake"';
        }
        print ' value="' . $ISBN . '"';
        ?>
            ><br>
       
    <!-- ***************** subject list box ****************** -->
    <?php
    
    // ************ query for selecting subjects *****************//
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
    
    <!-- ***************** type radio buttons ****************** -->    
    <input type="radio" name="radType" value="Book"/>Book<br>
    <input type="radio" name="radType" value="eBook"/>eBook<br>
    <input type="radio" name="radType" value="Other"/>Other<br>
        
    <!-- ***************** submit button ****************** -->
    <input type="submit" name="btnSubmit" value="Search" tabindex="900" id="btnSubmit">
    
</form>

<?php

} // END IF NO DATA IN INPUT

// *********** INCLUDE FOOTER *********** //
include 'footer.php';

?>
