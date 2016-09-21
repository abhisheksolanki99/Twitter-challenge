<?php
// Load the library files
require_once('./api/twitteroauth/OAuth.php');
require_once('./api/twitteroauth/twitteroauth.php');
require_once('./api/config.php');

// define the consumer key and secet and callback
define('OAUTH_CALLBACK', 'http://twittertimeline.azurewebsites.net/index.php');
// start the session
session_start();

// 1. to handle logout request
if (isset($_GET['logout'])) {
    //unset the session
    session_unset();
    // redirect to same page to remove url paramters
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
// 2. if user session not enabled get the login url
if (!isset($_SESSION['data']) && !isset($_GET['oauth_token'])) {
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
    $request_token = $connection->getRequestToken(OAUTH_CALLBACK);
    if ($request_token) {
        $token = $request_token['oauth_token'];
        $_SESSION['request_token'] = $token;
        $_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
        $login_url = $connection->getAuthorizeURL($token);
    }
}
// 3. if its a callback url
if (isset($_GET['oauth_token'])) {
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['request_token'], $_SESSION['request_token_secret']);
    $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
    if ($access_token) {
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $params = array('include_entities' => 'false');
        $data = $connection->get('account/verify_credentials', $params);
        if ($data) {
            $_SESSION['data'] = $data;
            $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
        }
    }
}

if (isset($login_url) && !isset($_SESSION['data'])) {
    
} else {
    $data = $_SESSION['data'];
    echo "Name : " . $data->name . "<br>";
    echo "Username : " . $data->screen_name . "<br>";
    echo "Photo : <img src='" . $data->profile_image_url . "'/><br><br>";
    echo "<a href='?logout=true'><button>Logout</button></a>";
}
?>


<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Twitter - Time line Challenge</title>
        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/login.css" rel="stylesheet">
        <script src="js/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">                
            <div class="login-form">
                <h1 class="text-center">Twitter - Time line Challenge</h1>
                <form id="login-form" method="post" class="form-signin" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input name="email" id="email" type="email" class="form-control" placeholder="Email address" autofocus> 
                    <input name="password" id="password" type="password" class="form-control" placeholder="Password"> 
                    <button class="btn btn-block bt-login" type="submit">Sign in</button>
                    <h4 class="text-center login-txt-center">Alternatively, you can log in using:</h4>
                    <a class="btn btn-default twitter" href="<?php echo $login_url; ?>"> <i class="fa fa-twitter modal-icons"></i> Sign In with Twitter </a>
                </form>
            </div>
        </div>
        <!-- /container -->
        <script src="js/jquery.validate.min.js"></script>
        <script src="js/login.js"></script>
    </body>
</html>
