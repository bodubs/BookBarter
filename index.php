<?php

// Include top
include 'top.php';

// Initialize variables
$records = array();
$i = 0;

// create query showing most recent 6 records
$query = 'SELECT pmkItemId, fnkUserId, fldTitle, fldAuthorFirstName, fldAuthorLastName, fldISBNNumber, fldSubject FROM tblBooks ';
$query .= 'ORDER BY pmkItemId DESC LIMIT 6';

if ($thisDatabaseReader->querySecurityOk($query, 0,1)) {
    $query = $thisDatabaseReader->sanitizeQuery($query);
    $records = $thisDatabaseReader->select($query, ''); 
}

?>

<h2>Text Books</h2>

<ul id="books">

<?php

if (is_array($records)) { // IF RECORDS IS ARRAY
    foreach ($records as $record) { // DISPLAY EACH RECORD
        
        $i++;
        
        if ($i % 2 != 0) {
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
        } // END IF IS USER
        print '</ul>';
        print '</li>';
    }
}
print '</ul>';

// ********** INCLUDE FOOTER ********** //
include 'footer.php';

?>