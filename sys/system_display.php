<?php

function display_main(): void
{
    if (!isset($_GET["display"])) {
        $_GET["display"] = "notice";
    }

    $display = $_GET["display"];

    switch ($display) {
        case "register":
            display_register();
            break;
        case "clarifications":
            display_clarifications();
            break;
        case "account":
            display_account();
            break;
        case "problem":
            display_problem();
            break;
        case "submissions":
            display_submissions();
            break;
        case "rankings":
            display_rankings();
            break;
        case "code":
            display_code();
            break;
        case "scoreboard":
            display_scoreboard();
            break;
        case "adminsettings":
            display_adminsettings();
            break;
        case "admindata":
            display_admindata();
            break;
        case "adminproblem":
            display_adminproblem();
            break;
        case "adminteam":
            display_adminteam();
            break;
        case "admingroup":
            display_admingroup();
            break;
        case "adminlogs":
            display_adminlogs();
            break;
        case "doc":
            display_doc();
            break;
        case "faq":
            display_faq();
            break;
        case "notice":
        default:
            display_notice();
            break;
    }
}

function display_notice(): void
{
    echo "<table class='table table-borderless'><thead><tr class='table-primary'><th><h3>Important Notices</h3></th></tr></thead></table>";

    $edit = (isset($_GET["edit"]) && $_GET["edit"] == 1) ? 1 : 0;

    if ($edit) {
        display_edit_notice();
    } else {
        display_read_notice();
    }

    if (!$edit && $_SESSION["status"] == "Admin") {
        echo "<div class='mb-3'>
                <button type='button' class='btn btn-primary' onclick=\"window.location='?display=notice&edit=1';\">Edit Notice</button>
              </div>";
    }
}

function display_edit_notice(): void
{
    global $admin;

    echo "<div class='mb-3'>
            <form action='?action=noticeupdate' method='post'>
            <div class='mb-3'>
              <textarea class='form-control' name='notice'>";

                if (isset($admin["notice"])) {
                    echo stripslashes($admin["notice"]);
                }

    echo "</textarea>
          </div>
                <div class='mb-3'>
                    <button type='submit' class='btn btn-primary'>Update Notice</button>
                    <button type='button' class='btn btn-secondary' onclick='window.location.reload();'>Clear Changes</button>
                    <button type='button' class='btn btn-danger' onclick=\"window.location='?display=notice';\">Cancel</button>
                </div>
            </form>
            </div>";
}

function display_read_notice(): void
{
    global $admin;

    if (isset($admin["notice"])) {
        $data = $admin["notice"];
        $lines = explode("\n", $data);
        $isHeader = true;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line != "") {
                if ($isHeader) {
                    echo "<table class='table table-borderless'><thead><tr class='table-info'><th>" . stripslashes($line) . "</th></tr><tr></thead><td>";
                } else {
                    echo stripslashes($line);
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

function action_noticeupdate(): void
{
    global $admin;

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied: You need to be an Administrator to perform this action.";
        return;
    }
    if (isset($_POST["notice"])) {
        $admin["notice"] = $_POST["notice"];
        $_SESSION["message"][] = "Notice Updation Successful";
    } else {
        $_SESSION["message"][] = "Notice Updation Error: Insufficient Data";
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

    echo "<div class='mb-3' onclick='$(this).slideUp(250);' title='Click to hide'>";

    if ((isset($admin["mode"]) && $admin["mode"] == "Lockdown") && $_SESSION["status"] != "Admin") {
        echo "Lockdown Mode";
    } else {
        foreach ($_SESSION["message"] as $line) {
            echo "<div class='alert alert-info'>" . filter($line) . "</div>";
        }
    }

    echo "</div>";
    $_SESSION["message"] = array();
}
