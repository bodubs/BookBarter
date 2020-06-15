<!-- ######################     Main Navigation   ########################## -->
<nav>
    <ol>
        <?php
        
        print '<li ';
        if ($PATH_PARTS['filename'] == 'index') {
            print ' class="activePage" ';
        }
        print '><a href="index.php">Home</a></li>';
       
        print '<li ';
        if ($PATH_PARTS['filename'] == 'search') {
            print ' class="activePage" ';
        }
        print '><a href="search.php">Search</a></li>';
        
        print '<li ';
        if ($PATH_PARTS['filename'] == 'upload') {
            print ' class="activePage" ';
        }
        print '><a href="upload.php">Upload</a></li>';
        
        if (!empty($_SESSION)) { // IF THERE IS A USER
            if ($_SESSION['user']["pmkUserId"]) { // GET USER ID THROUGH SESSION SUPER GLOBAL
                print '<li ';
                if ($PATH_PARTS['filename'] == 'my-uploads') {
                    print ' class="activePage" ';
                }
                print '><a href="my-uploads.php">My Uploads</a></li>';
            }
        } // END IF IS USER
        
        if (!empty($_SESSION)) { // IF THERE IS A USER
            if ($_SESSION['user']["pmkUserId"]) { // GET USER ID THROUGH SESSION SUPER GLOBAL
                print '<li ';
                if ($PATH_PARTS['filename'] == 'log-out') {
                    print ' class="activePage" ';
                }
                print '><a href="log-out.php">Log Out</a></li>';
            } 
        } else { // IF NOT A USER
            print '<li ';
            if ($PATH_PARTS['filename'] == 'log-in') {
                print ' class="activePage" ';
            }
            print '><a href="log-in.php">Log In</a></li>';
        } // END IF NOT A USER
      
        print '<li ';
        if ($PATH_PARTS['filename'] == 'tables') {
            print ' class="activePage" ';
        }
        print '><a href="tables.php">Tables</a></li>';
        
        ?>
    </ol>
</nav>
<!-- #################### Ends Main Navigation    ########################## -->

