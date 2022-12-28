<?php
!isset($_SESSION) ? session_start() : null;

require "env.php";
require "content.php";

$session_active = isset($_SESSION['id']) && isset($_SESSION['player']);

if (isset($_POST))
{
    if (isset($_POST['s_type']) && in_array($_POST['s_type'], ['meta', 'astro']) && $session_active)
    {
        $s_type  = filter_var($_POST['s_type'], FILTER_SANITIZE_STRING);
        $this_id = $s_type == 'meta' ? rand(0, 21) : ( $s_type == 'astro' ? rand(0, 11) : null);
        $w_type  = $s_type == 'meta' ? 'Метазнак'  : ( $s_type == 'astro' ? 'Дом' : null);

        sf_write($_SESSION['id'], $w_type . ": " . ($this_id + 1), $_SESSION['player']);
        eval('echo json_encode(["card" => $this_id + 1, "desc" => $' . $s_type . '_cards' . '[' . $this_id . ']]);');
    }
    elseif (isset($_POST['s_type']) && in_array($_POST['s_type'], ['meta', 'astro']) && !$session_active)
    {
      session_fail();
    }

    if (isset($_POST['rolled']) && $session_active && (!isset($_SESSION['start']) || !$_SESSION['start']))
    {
        $rolled = rand(1, 6);

        sf_write($_SESSION['id'], "Игральный кубик: " . strval($rolled), $_SESSION['player']);
        $_SESSION['start'] = $rolled == 5;

        echo $rolled;
    }
    elseif (isset($_POST['rolled']) && !$session_active)
    {
      session_fail();
    }

    if (isset($_POST['session_toggle']))
    {
        isset($_SESSION["host"]) ? gen_sf_id($_SESSION["host"], $_SESSION["id"]) : null;
        die(json_encode(["Message" => "success"]));
    }

    if (isset($_POST['check_actions']))
    {
        echo build_p_actions_arr();
    }

    if (isset($_POST['isover']) && $session_active)
    {
        isset($_SESSION['id']) ? ($currentSf  = "sessions/"  . $_SESSION['id'] . ".txt") : die(json_encode(array(
                                                                                                "message" => "Success",
                                                                                                "data"    => "End")
                                                                                            ));

        if (checkOver($currentSf))
        {
          header('HTTP/1.1 200 Well, that does it.');
          header('Content-Type: application/json; charset=UTF-8');
          die(json_encode(array("message" => "Success", "data" => "End")));
        }
    }
    elseif (isset($_POST['isover']) && !$session_active)
    {
      session_fail();
    }
}

function session_fail()
{
    header('HTTP/1.1 403 What do you think you are doing?');
    header('Content-Type: application/json; charset=UTF-8');

    die(json_encode(array("message" => "Fail")));
}

function checkOver($currentSf)
{
    if ((!(file_exists($currentSf) || file_exists("./{$currentSf}") || file_exists("../{$currentSf}"))
          || !isset($_SESSION['player']) || sf_last($_SESSION['id'])))
          {
            
        session_destroy();
        return true;
    }

    return false;
}

function is_mobile()
{
    return preg_match("/(android|iPhone|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|" . "mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function vrf_client_ip($ip, $action)
{
    $uip  = filter_var($ip, FILTER_SANITIZE_STRING);
    $conn = new mysqli(DB_SERV, DB_USER, DB_PASS, DB_DB);

    $conn->connect_error ? die("Database error :(") : null;

    if ($action == 0)
    {
        $result = $conn->query("SELECT * FROM `ips` WHERE `uip` = '$uip'"); 

        if ($result->num_rows > 0)
        {
            $conn->close();
            return true;
        }
    }
    elseif ($action == 1)
    {
        $result = $conn->query("INSERT INTO `ips` (`uip`) VALUES ('$uip')");
    }

    $conn->close();
    return false;
}

function ver_sf_id($pre_id, $uip, $nick)
{
    $s_id = filter_var($pre_id, FILTER_SANITIZE_STRING);
    $conn = new mysqli(DB_SERV, DB_USER, DB_PASS, DB_DB);

    $conn->connect_error ? die("Database error :(") : null;

    $result = $conn->query("SELECT * FROM `hosts` WHERE `c_session` = '$s_id'"); 

    if ($result->num_rows > 0)
    {
        $_SESSION['id']     = $s_id;
        $_SESSION['player'] = filter_var(str_replace('–', '', $nick), FILTER_SANITIZE_STRING);
        $ssnipw             = $conn->query("INSERT INTO `session_ips` (`uip`, `host`) VALUES ('$uip', '$s_id')");
        
        sf_write($s_id, 'Игрок подключился.', $_SESSION['player']);
    }

    $conn->close();
}

function pre_sf_id()
{
  $this_id = str_replace(["'", "\\", "/"], "", base64_encode(random_bytes(8)));

  file_exists("../sessions/{$this_id}.txt") || file_exists("./sessions/{$this_id}.txt") || file_exists("sessions/{$this_id}.txt") ? pre_sf_id() : null;

  return $this_id;
}

function gen_sf_id($h_name, $c_session)
{
    $this_s_id = pre_sf_id();
    
    $sipsnull  = [];
    $conn      = new mysqli(DB_SERV, DB_USER, DB_PASS, DB_DB);

    $conn->connect_error ? die("Database error :(") : null;

    try {
        $result = $conn->query("SELECT * FROM `session_ips` WHERE `host` = '$c_session'");

        if ($result->num_rows > 0)
        {
            while ($row = $result->fetch_assoc())
            {
                array_push($sipsnull, $row);
            }
        }
        
        foreach ($sipsnull as $sip)
        {
            $this_sip = $sip['uip'];
            $result   = $conn->query("DELETE FROM `ips` WHERE `uip` = '$this_sip'");
        }
    
        $result = $conn->query("DELETE FROM `session_ips` WHERE `host` = '$c_session'");
        $result = $conn->query("UPDATE `hosts` SET `c_session` = '$this_s_id' WHERE `h_name` = '$h_name'");
  
    }
    catch (Exception $e)
    {
        $result = $conn->query("DELETE * FROM `ips`;");
        $result = $conn->query("DELETE * FROM `session_ips`;");
    }

    $conn->close();

    sf_write($c_session, "end.", $h_name);

    $currentSf = "sessions/{$c_session}.txt";

    try {
        unlink($currentSf);
    }
    catch (Exception $e)
    {
        try {
            unlink("./{$currentSf}");
        }
        catch (Exception $e)
        {
            unlink("../{$currentSf}");
        }
    }

    $pre_sf = "sessions/{$this_s_id}.txt";
  
    try {
        fopen($pre_sf, 'x');
    }
    catch (Exception $e)
    {
        try {
            fopen("./{$pre_sf}", 'x');
        }
        catch (Exception $e)
        {
            fopen("../{$pre_sf}", 'x');
        }
    }

    session_destroy();

    return $this_s_id;
}

function get_sf_id($h_name)
{
    $conn = new mysqli(DB_SERV, DB_USER, DB_PASS, DB_DB);

    $conn->connect_error ? die("Database error :(") : null;
    $result = $conn->query("SELECT `c_session` FROM `hosts` WHERE `h_name` = '$h_name'");

    $conn->close();

    return $result->num_rows > 0 ? $result->fetch_assoc()['c_session'] : null;
}

function sf_write($this_id, $data, $player)
{
    if (file_exists("sessions/{$this_id}.txt") || file_exists("./sessions/{$this_id}.txt") || file_exists("../sessions/{$this_id}.txt"))
    {
        try
        {
            file_put_contents("sessions/{$this_id}.txt", "{$player} – {$data}\n", FILE_APPEND);
        }
        catch (Exception $e)
        {
            try
            {
                file_put_contents("./sessions/{$this_id}.txt", "{$player} – {$data}\n", FILE_APPEND);
            }
            catch (Exception $e)
            {
                file_put_contents("../sessions/{$this_id}.txt", "{$player} – {$data}\n", FILE_APPEND);
            }
        }
    }
}

function sf_contents($host)
{
    return file_exists("./sessions/".get_sf_id($host).".txt") ? explode(PHP_EOL, file_get_contents("./sessions/".get_sf_id($host).".txt")) : [];
}

function sf_last($s_id)
{  
  $sfile = explode(PHP_EOL, file_get_contents("./sessions/{$s_id}.txt"));

  return strpos($sfile[count($sfile) - 2], "end.");
}

function ver_host($host)
{
    $h_name = filter_var($host['name'], FILTER_SANITIZE_STRING);
    $h_pass = filter_var($host['pass'], FILTER_SANITIZE_STRING);
    $h_key  = isset($host['host_key']) ? filter_var($host['host_key'], FILTER_SANITIZE_STRING) : null;

    $conn   = new mysqli(DB_SERV, DB_USER, DB_PASS, DB_DB);

    $conn->connect_error ? die("Database error :(") : null;
    
    $result = $conn->query("SELECT * FROM `hosts` WHERE `h_name` = '$h_name'"); 
    
    if ($result->num_rows > 0)
    {
        $retrived = mysqli_fetch_assoc($result);

        if (password_verify($h_pass, $retrived['h_pass']))
        {
            if (isset($h_key) && sf_exists("sessions/{$h_key}.txt"))
            {
                $update = $conn->query("UPDATE `hosts` SET `c_session` = '$h_key' WHERE `h_name` = '$h_name'");
                $result = $conn->query("SELECT * FROM `hosts` WHERE `h_name` = '$h_name'");
            }
    
            $retrived = mysqli_fetch_assoc($result);
    
            $_SESSION['host'] = $h_name;
            $_SESSION['id']   = $retrived['c_session'];
    
            $pre_sf = "sessions/{$_SESSION['id']}.txt";
    
            if (!sf_exists($pre_sf))
            {
                try
                {
                    fopen($pre_sf, 'x');
                }
                catch (Exception $e)
                {
                    try
                    {
                        fopen("./{$pre_sf}", 'x');
                    }
                    catch (Exception $e)
                    {
                        fopen("../{$pre_sf}", 'x');
                    }
                }
            }
        }
    }

    $conn->close();
}

function build_p_actions_arr()
{
    include "content.php";

    $p_actions = isset($_SESSION['host']) ? sf_contents($_SESSION['host']) : [];

    if (count($p_actions))
    {
        $p_actions_arr = '';
        $p_last        = $p_actions[count($p_actions)-1];

        foreach ($p_actions as $action)
        {
          $p_actions_arr .= strlen($action) > 0 ? $action . ($action != $p_last ? ', ' : '"') : null;
        }

        $p_last = count($p_actions) > 1 ? $p_actions[count($p_actions)-2] : $p_last;

        if (strpos($p_last, 'Игральный кубик:') === false && strpos($p_last, 'Игрок подключился.') === false)
        {
            $deck = strpos($p_last, 'Дом') ? $astro_cards : (strpos($p_last, 'Метазнак') ? $meta_cards : null);
            $desc = isset($deck)           ? $deck[intval(str_replace('Дом: ', '', str_replace('Метазнак: ', '', substr($p_last, strpos($p_last, ' –') + 4, strlen($p_last))))) - 1] : null;
        }

        return json_encode(['actn' => $p_actions_arr, 'desc' => isset($desc) ? $desc : null]);
    }

    return null;
}

function deck_build($d_type, $show = false)
{
    $d_size = $d_type == 'astro' ? 12 : 22;

    for ($i = 0; $i < $d_size; $i++)
    { ?>
    
    <div class="wrap wrap--1 <?php echo ($show ? '' : 'c_hidden ') . $d_type; ?>">
        <div class="container container--1 <?php echo $d_type; ?>"></div>
    </div> <?php

    }
}

function dice_build()
{
    for ($i = 1; $i < 7; $i++)
    { ?>

    <li class="die-item" data-side="<?php echo $i; ?>">
        <?php for ($j = 0; $j < $i; $j++)
        { ?>
        <span class="dot"></span>
        <?php } ?>
    </li> <?php
    
    }
}

function build_p_body()
{
    $sf_actions = sf_contents($_SESSION['host']);

    if (isset($sf_actions))
    {
        foreach ($sf_actions as $action)
        { ?>
            <p><?php echo $action; ?></p>
            <?php if ($action == 'Игральный кубик: 5')
            {?>
            <p><span>Выпало 5!</span> Игра началась.</p>
            <?php } ?><?php
        }
    }
}

function sf_exists($pre_sf)
{
    return file_exists($pre_sf) || file_exists("./{$pre_sf}") || file_exists("../{$pre_sf}");
}