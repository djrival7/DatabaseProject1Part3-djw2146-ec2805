<?php
include_once 'psl-config.php';

function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
                              $cookieParams["path"],
                              $cookieParams["domain"],
                              $secure,
                              $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session
    session_regenerate_id();    // regenerated the session, delete the old one.
}

function login($email, $password, $mysqli) {
    // Using prepared statements means that SQL injection is not possible.

    $stmt = oci_parse($mysqli, "select email, trim(password) from USERS where email = '" . $email . "'");
    $r = oci_execute($stmt);    

    if ($r) {
        $user = oci_fetch_row($stmt);
        if(!$user) {
            return false;
        }
        $db_password = $user[1];

        if(!strcmp($db_password.trim(), $password.trim())) {
            $user_browser = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
            return true;
        }
        else {
            return false;
        }
    } 
    return false;
}

function login_check($mysqli) {

    // Check if all session variables are set
    if (isset($_SESSION['email'],
              $_SESSION['password'])) {

        $email = $_SESSION['email'];
        $password = $_SESSION['password'];

        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        $stmt = oci_parse($mysqli, "select email, password from USERS where email = '" . $email . "'");
        $r = oci_execute($stmt);

        if ($r) {
            $user = oci_fetch_row($stmt);
            if(!$user) {
                return false;
            }
            $db_password = $password;

            if($db_password == $password) {
                return true;
            }
            else {
                return false;
            }
        } 
        return false;
    }
    return false;
}

function esc_url($url) {

    if ('' == $url) {
        return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;

    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }

    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);

    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}
