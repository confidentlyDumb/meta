<?php

!isset($_SESSION) ? session_start() : null;

$user = $_SERVER['REMOTE_ADDR'];

include "helpers.php";
require_once "content.php";

if (isset($_POST['key']) && strlen($_POST['key']) > 0 && isset($_POST['nick']) && strlen($_POST['nick']) > 0)
{
    ver_sf_id($_POST['key'], $user, $_POST['nick']);
}
elseif (isset($_POST['name']) && strlen($_POST['name']) > 0 && isset($_POST['pass']) && strlen($_POST['pass']) > 0)
{
    ver_host([
        'name'     => $_POST['name'],
        'pass'     => $_POST['pass'],
        'host_key' => isset($_POST['host_key']) ? $_POST['host_key'] : null
    ]);
}
elseif (vrf_client_ip($user, 0) == false && !strpos($_SERVER["REQUEST_URI"], 'intro') && !(isset($_SESSION['player']) || isset($_SESSION['host'])))
{
    header("Location: /intro");
}

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>MetaZodiac</title>
        <base href="/" />
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, minimal-ui">
        <link href="/assets/css/main_fin_v8.css" rel="stylesheet" type="text/css">
        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
    </head>
    <body class="<?php echo strpos($_SERVER['REQUEST_URI'], 'intro') ? 'intro'
                                   : (isset($_SESSION['player'])     ? 'pre_play' : ''); ?>">
        <div id="loader-wrapper">
            <div id="loader">
                <img src="/assets/img/logo.png" alt="">
            </div>
        </div>
        <?php if (!strpos($_SERVER['REQUEST_URI'], 'intro'))
        { ?>
        <header>
            <img src="/assets/img/logo.png" alt="">
            <img src="/assets/img/logo_letters.png" alt="">
        </header>
        <?php }
        include isset($_SESSION['player'])               ? "partials/player.php"
             : (isset($_SESSION['host'])                 ? "partials/host.php"
             : (strpos($_SERVER["REQUEST_URI"], 'intro') ? "partials/intro.php"
                                                         : "partials/login.php")); ?>
        
        <script type="text/javascript" src="/assets/js/jquery.js" type="text/javascript"></script>
        <script type="text/javascript" src="/assets/js/main.js" type="text/javascript"></script>

        <script type="text/javascript">

        <?php if (isset($_SESSION['host']))
        {
            $pre_actions = build_p_actions_arr(); ?>
            window.p_actions = '<?php echo isset($pre_actions) ? json_decode($pre_actions)->actn : ''; ?>'.split(", ");
        <?php } elseif (isset($_SESSION['player']))
        { ?>
            setInterval(() => {
                is_over();
            }, 10000);
        <?php } ?>

        </script>

        <?php if (strpos($_SERVER["REQUEST_URI"], 'intro'))
        { ?>

        <script type="text/javascript">

            function rdr_cntdwn ()
            {
                setInterval(() => {
                    if (document.querySelector(".i_wrap video").currentTime >= 8)
                    {
                        setTimeout(() => {
                            window.location.href = "/";                            
                        }, 10);
                    }
                }, 1000);
            }
            window.addEventListener("load", function ()
            {
                document.querySelector("#skip").addEventListener("click", function ()
                {
                    window.location.href = "/";
                })
                <?php if (is_mobile())
                { ?>
                document.querySelector(".i_wrap video").addEventListener("play", function ()
                {
                    rdr_cntdwn();
                })
                <?php } else { ?>
                    rdr_cntdwn();
                <?php } ?>
            });

        </script>

        <?php } ?>
    </body>
</html>