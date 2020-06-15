<?php

// Include top
include 'top.php';



if (isset($_GET['id'])) { // GET ITEM ID THROUGH GET ARRAY
    
    // ********* SAVE ITEM ID TO VARIABLE ********* //
    $pmkItemId = (int) htmlentities($_GET["id"], ENT_QUOTES, "UTF-8");
    
    //*********** CREATE QUERY ************ //
    $query = 'SELECT pmkItemId, fnkUserId, fldTitle, fldAuthorFirstName, fldAuthorLastName, fldISBNNumber, fldSubject, fldType ';
    $query .= 'FROM tblBooks WHERE pmkItemId = ?';
    
    $data = array($pmkItemId);
    
    if ($thisDatabaseReader->querySecurityOk($query, 1)) {
        $query = $thisDatabaseReader->sanitizeQuery($query);
        $records = $thisDatabaseReader->select($query, $data);
    }
    
    // ************* CREATE TAGS QUERY ************ //
    $query2 = 'SELECT pfkTag FROM tblBooksTags JOIN tblBooks ON pmkItemId = pfkItemId WHERE pmkItemId = ?';
    
    if ($thisDatabaseReader->querySecurityOk($query2, 1)) {
        $query2 = $thisDatabaseReader->sanitizeQuery($query2);
        $tags = $thisDatabaseReader->select($query2, $data);
    }
    
    // ************ CREATE USER QUERY ************** //
    $query3 = 'SELECT fldUserFirstName, fldUserEmail, fldUserPhone FROM tblUsers ';
    $query3 .= 'JOIN tblBooks ON pmkUserId = fnkUserId WHERE pmkItemId = ?';
    
    if ($thisDatabaseReader->querySecurityOk($query3, 1)) {
        $query3 = $thisDatabaseReader->sanitizeQuery($query3);
        $users = $thisDatabaseReader->select($query3, $data);
    }
    
    print '<ul id="books">';
    
    if (is_array($records)) { // IF RECORDS IS ARRAY 
        foreach ($records as $record) { // DISPLAY ALL RECORDS
        
            print '<li>';
            print '<ul>';
            print '<li><strong>Title:</strong> ' . $record['fldTitle'] . '</li>';
            print '<li><strong>Author:</strong> ' . $record['fldAuthorFirstName'] . ' ' . $record['fldAuthorLastName'] . '</li>';
            print '<li><strong>ISBN:</strong> ' . $record['fldISBNNumber'] . '</li>';
            print '<li><strong>Subject:</strong> ' . $record['fldSubject'] . '</li>';
            print '<li><strong>Type:</strong> ' . $record['fldType'] . '</li>';
            print '<li><strong>Tags:</strong> ';
            foreach($tags as $tag) {
                print $tag['pfkTag'] . ', ';
            }
            print '</li>'; 
            print '<li><br></li>';
            foreach ($users as $user) {
                print '<li><strong>Contact:</strong></li>';
                print '<li><strong>Name:</strong> ' . $user['fldUserFirstName'] . '</li>';
                print '<li><strong>Email:</strong> ' . $user['fldUserEmail'] . '</li>';
                print '<li><strong>Phone:</strong> ' . $user['fldUserPhone'] . '</li>';
            }
            if (!empty($_SESSION)) { // IF IS USER
                if ($_SESSION['user']["pmkUserId"] == $record['fnkUserId']) { // IF USER WHO UPLOADED THIS BOOK
                    print '<li><a href="upload.php?id=' . $record['pmkItemId'] . '">Edit</a></li>';
                }
            }
            print '</ul>';
            print '</li>';
        } // END DISPLAY ALL RECORDS
    } // END IF RECORDS IS ARRAY
    print '</ul>';
    
}

// INCLUDE FOOTER
include 'footer.php';

?>