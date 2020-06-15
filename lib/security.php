<?php
print PHP_EOL . '<!--  BEGIN include security  -->' . PHP_EOL;
//
// Performs security check
//
function securityCheck($myFormURL = "") {
    $debugThis = false;
    
    $status = true;
    
    if ($myFormURL != "") {
        $fromPage = htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');
        
        $fromPage = strtok(preg_replace('#^https?:#', '', $fromPage), '?');
        
        if ($debugThis)
            print '<p>From: ' . $fromPage . ' should match your URL: ' . $myFormURL;
        
        if ($fromPage != $myFormURL) {
            $status = false;
        }
    }
    
    return $status;
}
print PHP_EOL . '<!-- END include security -->' . PHP_EOL;
?>