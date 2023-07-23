<?php

function display_main(): void
{
    if (!isset($_GET["display"])) {
        $_GET["display"] = "notice";
    }
    if ($_GET["display"] == "register") {
        display_register();
    } else if ($_GET["display"] == "clarifications") {
        display_clarifications();
    } else if ($_GET["display"] == "account") {
        display_account();
    } else if ($_GET["display"] == "problem") {
        display_problem();
    } else if ($_GET["display"] == "submissions") {
        display_submissions();
    } else if ($_GET["display"] == "rankings") {
        display_rankings();
    } else if ($_GET["display"] == "code") {
        display_code();
    } else if ($_GET["display"] == "scoreboard") {
        display_scoreboard();
    } else if ($_GET["display"] == "adminsettings") {
        display_adminsettings();
    } else if ($_GET["display"] == "admindata") {
        display_admindata();
    } else if ($_GET["display"] == "adminproblem") {
        display_adminproblem();
    } else if ($_GET["display"] == "adminteam") {
        display_adminteam();
    } else if ($_GET["display"] == "admingroup") {
        display_admingroup();
    } else if ($_GET["display"] == "adminlogs") {
        display_adminlogs();
    } else if ($_GET["display"] == "doc") {
        display_doc();
    } else if ($_GET["display"] == "faq") {
        display_faq();
    } else if ($_GET["display"] == "notice") {
        display_notice();
    } else {
        display_notice();
    }
}

function display_notice(): void
{
    global $admin;
    echo "<h4>Important Notices</h4>";
    $edit = (isset($_GET["edit"]) && $_GET["edit"] == 1) ? 1 : 0;
    if ($edit) {
        echo "<form action='?action=noticeupdate' method='post'>
              <textarea class='form-control' name='notice'>";
        if (isset($admin["notice"])) {
            echo stripslashes($admin["notice"]);
        }
        echo "</textarea>
                  <div class='border rounded mb-3'>
                    <input type='submit' value='Update Notice' class='btn btn-primary' >
                    <input type='button' value='Clear Changes' class='btn btn-secondary' class='bi bi-trash3'  onClick='window.location.reload();'>
                    <input type='button' value='Cancel' class='btn btn-danger'  onClick=\"window.location='?display=notice';\">
                  </div>
              </form>";
    } else {
        if (isset($admin["notice"])) {
            $data = $admin["notice"];
            $lines = explode("\n", $data);
            $isHeader = true;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line != "") {
                    if ($isHeader) {
                        echo "<table class='table table-borderless'><tr><th>" . stripslashes($line) . "</th></tr><tr><td>";
                    } else {
                        echo "<li>" . stripslashes($line) . "</li>";
                    }
                    $isHeader = false;
                } else {
                    if (!$isHeader) {
                        echo "</td></tr></table>";
                    }
                    $isHeader = true;
                }
            }
            if (!$isHeader) {
                echo "</td></tr></table>";
            }
        }
    }
    if (!$edit && $_SESSION["status"] == "Admin") {
        echo "
              <div>
                <input type='button' class='btn btn-primary' value='Edit Notice' onClick=\"window.location='?display=notice&edit=1';\">
              </div>";
    }
}


function action_noticeupdate(): void
{
    global $admin;
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (isset($_POST["notice"])) {
        $admin["notice"] = $_POST["notice"];
        $_SESSION["message"][] = "Notice Updation Successful";
    } else {
        $_SESSION["message"][] = "Notice Updation Error : Insufficient Data";
    }
}

function display_faq(): void
{
    include("sys/faq.php");
}

function display_doc(): void
{
    include("sys/doc.php");
}

function display_message(): void
{
    global $currentmessage, $admin;
    if (empty($_SESSION["message"])) {
        return;
    }
    $currentmessage = $_SESSION["message"];
    echo "<div class='messagebox' onClick='$(this).slideUp(250);' title='Click to hide'>";
    if ((isset($admin["mode"]) && $admin["mode"] == "Lockdown") && $_SESSION["status"] != "Admin") {
        echo "Lockdown Mode";
    } else {
        foreach ($_SESSION["message"] as $line) {
            echo filter($line) . "<br>";
        }
    }
    echo "</div>";
    $_SESSION["message"] = array();
}

?>