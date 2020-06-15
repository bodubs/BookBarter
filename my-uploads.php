<?php

// Include top
include 'top.php';

// Initialize variables
$records = array();
$i = 0;


// create query
$query = 'SELECT pmkItemId, fnkUserId, fldTitle, fldAuthorFirstName, fldAuthorLastName, fldISBNNumber, fldSubject, fldType ';
$query .= 'FROM tblBooks ';
$query .= 'WHERE fnkUserId = ? ';
$query .= 'ORDER BY pmkItemId DESC';

// ******** GET USER ID THROUGH SESSION SUPER GLOBAL ********* //
$userData = array($_SESSION['user']['pmkUserId']);

if ($thisDatabaseReader->querySecurityOk($query, 1,1)) {
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, $userData); 
}

?>

<!-- ********* DISPLAY MY BOOKS ********** -->
<h2>My Books</h2>

<ul id="books">
    
<?php

if (is_array($records)) { // IF RECORDS IS ARRAY
    foreach ($records as $record) { // DISPLAY EACH RECORD
        
        $i++;
        
        if ($i % 2 != 0) { // ADDS LINE BREAK FOR EVERY TWO RECORDS
            print '<br>';
        }
        print '<li>';
        print '<ul>';
        print '<li><strong>Title:</strong> ' . $record['fldTitle'] . '</li>';
        print '<li><strong>Author:</strong> ' . $record['fldAuthorFirstName'] . ' ' . $record['fldAuthorLastName'] . '</li>';
        print '<li><strong>ISBN:</strong> ' . $record['fldISBNNumber'] . '</li>';
        print '<li><strong>Subject:</strong> ' . $record['fldSubject'] . '</li>';
        print '<li><strong>Type:</strong> ' . $record['fldType'] . '</li>';
        if (!empty($_SESSION)) { // IF IS USER
            if ($_SESSION['user']["pmkUserId"] == $record['fnkUserId']) { // GET USER ID THROUGH SESSION SUPER GLOBAL
                print '<li><a href="upload.php?id=' . $record['pmkItemId'] . '">Edit</a></li>';
            }
        } // END IF USER
        print '</ul>';
        print '</li>';
    }
}
print '</ul>';

?>

<?php

// *********** DISPLAY FOOTER ********** //
include 'footer.php';

?>