<?php

function display_adminproblem()
{
    global $currentmessage;
    if ($_SESSION["status"] == "Admin") {
        include("sys/adminproblem.php");
    } else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
}

function display_adminteam()
{
    global $currentmessage;
    if ($_SESSION["status"] == "Admin") include("sys/adminteam.php");
    else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
}

function display_adminsettings()
{
    global $currentmessage, $admin;
    if ($_SESSION["status"] == "Admin") {
        include("sys/adminsettings.php");
    } else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
}

function display_admindata()
{
    global $currentmessage, $admin;
    if ($_SESSION["status"] == "Admin") {
        include("sys/admindata.php");
    } else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
}

function display_adminlogs()
{
    global $currentmessage, $admin;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] == "Admin") {
        $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM logs ORDER BY time DESC");
        $totalResult = mysqli_fetch_array($totalQuery);
        $totalCount = $totalResult["total"];
        $limit = $admin["logpage"] ?? 25;
        if (isset($_GET["page"]) && is_numeric($_GET["page"])) {
            $page = max(0, $_GET["page"]);
        } else {
            $page = 0;
        }
        $x = paginate("display=adminlogs", $totalCount, $limit);
        $page = $x[0];
        $pagenav = $x[1];

        echo "<h3>Administrator Options : Access Logs</h3>";
        echo "<table class='table table-borderless'><thead><tr class='table-primary'><th>Date & Time</th><th>IP Address</th><th>Team ID/Name</th><th>Request</th></tr></thead>";
        $teams = mysqli_query($link, "SELECT tid,teamname FROM teams");
        while ($team = mysqli_fetch_array($teams)) {
            $teamnames[$team["tid"]] = $team["teamname"];
        }
        $logs = mysqli_query($link, "SELECT * FROM logs ORDER BY time DESC LIMIT " . (($page - 1) * $limit) . "," . $limit);
        while ($log = mysqli_fetch_array($logs)) {
            if (isset($teamnames[$log["tid"]])) {
                $teamname = "$log[tid] : " . $teamnames[$log["tid"]];
            } else {
                $teamname = $log["tid"] . " : Anonymous";
            }
            echo "<tr><td>" . date("d M Y, H:i:s", intval($log["time"])) . "</td><td>$log[ip]</td><td>" . $teamname . "</td><td>" . str_replace(",", ", ", $log["request"]) . "</td></tr>";
        }
        echo "</table><br>$pagenav</center>";
    } else {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
}

function action_updateaccount()
{
    global $admin;
    if (isset($_POST["contest_multilogin"]) && is_numeric($_POST["contest_multilogin"])) {
        $admin["multilogin"] = intval($_POST["contest_multilogin"]);
    }
    if (isset($_POST["content_regautoauth"]) && is_numeric($_POST["content_regautoauth"])) {
        $admin["regautoauth"] = intval($_POST["content_regautoauth"]);
    }
}

function action_updatecontest()
{
    global $admin;
    if (isset($_POST["contest_mode"]) && !empty($_POST["contest_mode"])) {
        if ($admin["mode"] != "Active" && $_POST["contest_mode"] == "Active") $admin["endtime"] = (time() + 3 * 60 * 60);
        if ($admin["mode"] != $_POST["contest_mode"]) $_SESSION["message"][] = "Administrator Options : Contest Status Updated Successfully";
        $admin["mode"] = $_POST["contest_mode"];
    }
    if (isset($_POST["contest_endtime"]) && !empty($_POST["contest_endtime"])) {
        $admin["endtime"] = (time() + $_POST["contest_endtime"] * 60);
        $_SESSION["message"][] = "Administrator Options : Contest End Time Updated Successfully";
    }
    if (isset($_POST["contest_penalty"]) && is_numeric($_POST["contest_penalty"])) {
        $admin["penalty"] = intval($_POST["contest_penalty"]);
        $_SESSION["message"][] = "Administrator Options : Incorrect Submission Penalty Updated Successfully";
    }
    if (isset($_POST["contest_ajaxrr"]) && is_numeric($_POST["contest_ajaxrr"])) {
        $admin["ajaxrr"] = intval($_POST["contest_ajaxrr"]);
        $_SESSION["message"][] = "Administrator Options : Ajax Refresh Rate Updated Successfully";
    }
    if ($admin["mode"] == "Active" && time() >= $admin["endtime"]) $admin["mode"] = "Disabled";
}

function action_updatestyle()
{
    global $admin;
    if (isset($_POST["contest_mysublist"]) && is_numeric($_POST["contest_mysublist"])) {
        $admin["mysublist"] = intval($_POST["contest_mysublist"]);
    }
    if (isset($_POST["contest_allsublist"]) && is_numeric($_POST["contest_allsublist"])) {
        $admin["allsublist"] = intval($_POST["contest_allsublist"]);
    }
    if (isset($_POST["contest_ranklist"]) && is_numeric($_POST["contest_ranklist"])) {
        $admin["ranklist"] = intval($_POST["contest_ranklist"]);
    }
    if (isset($_POST["contest_clarprivate"]) && is_numeric($_POST["contest_clarprivate"])) {
        $admin["clarprivate"] = intval($_POST["contest_clarprivate"]);
    }
    if (isset($_POST["contest_clarpublic"]) && is_numeric($_POST["contest_clarpublic"])) {
        $admin["clarpublic"] = intval($_POST["contest_clarpublic"]);
    }
    if (isset($_POST["contest_clarpage"]) && is_numeric($_POST["contest_clarpage"])) {
        $admin["clarpage"] = intval($_POST["contest_clarpage"]);
    }
    if (isset($_POST["contest_substatpage"]) && is_numeric($_POST["contest_substatpage"])) {
        $admin["substatpage"] = intval($_POST["contest_substatpage"]);
    }
    if (isset($_POST["contest_rankpage"]) && is_numeric($_POST["contest_rankpage"])) {
        $admin["rankpage"] = intval($_POST["contest_rankpage"]);
    }
    if (isset($_POST["contest_probpage"]) && is_numeric($_POST["contest_probpage"])) {
        $admin["probpage"] = intval($_POST["contest_probpage"]);
    }
    if (isset($_POST["contest_teampage"]) && is_numeric($_POST["contest_teampage"])) {
        $admin["teampage"] = intval($_POST["contest_teampage"]);
    }
    if (isset($_POST["contest_logpage"]) && is_numeric($_POST["contest_logpage"])) {
        $admin["logpage"] = intval($_POST["contest_logpage"]);
    }
}

function display_executionprotocol()
{
    global $admin, $fullresult, $extension, $currentmessage;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }

    if (file_exists("env/lock.txt")) {
        echo "<center><h2>Execution Protocol</h2>Could not obtain a lock on Execution Protocol.<br><br><input type='button' id='gobackbutton' onClick='gobackin(0);'></center>";
        echo "<script>gobackin(5); function gobackin(sec){ document.getElementById(\"gobackbutton\").value=\"Going back in \"+sec+\" seconds ...\"; if(sec<=0) window.location = '?display=admincontest'; else window.setTimeout('gobackin('+(sec-1)+');',1000); }</script>";
        return;
    } else {
        file_set("env/lock.txt", "");
    }

    $admin["lastjudge"] = time();

    echo "<center><h2>Execution Protocol</h2></center>";
    echo "<table width=100%><tr><th>Run ID</th><th>Problem</th><th>Language</th><th>Team</th><th>File Name</th><th>Time</th><th>Result</th></tr>";

    $invalid = 0;
    $temp = mysqli_query($link,"SELECT * FROM runs WHERE result is NULL ORDER BY rid ASC");
    if (mysqli_num_rows($temp) == 0) {
        $invalid = 1;
    }
    if (!$invalid) {
        $run = mysqli_fetch_array($temp);
        $realname = $run["name"] . "." . $extension[$run["language"]];
        if ($run["language"] != "Java") {
            $run["name"] = "code";
        }
        mysqli_query($link,"UPDATE runs SET result='...' WHERE rid='$run[rid]'");
        $temp = mysqli_query($link,"SELECT * FROM problems WHERE pid='$run[pid]'");
        if (mysqli_num_rows($temp) == 0) {
            $invalid = 1;
        }
    }
    if (!$invalid) {
        $problem = mysqli_fetch_array($temp);
        $temp = mysqli_query($link,"SELECT * FROM teams WHERE tid='$run[tid]'");
        if (mysqli_num_rows($temp) > 0) {
            $temp = mysqli_fetch_array($temp);
            $teamname = $temp["teamname"];
        } else {
            $teamname = "NA";
        }

        foreach (folder_get("env") as $file) {
            if ($file != "run.py" && $file != "lock.txt") {
                unlink("env/$file");
            }
        }
        $prefix = "";
        file_set("env/$run[name]." . $extension[$run["language"]], $prefix . stripslashes($run["code"]));
        file_set("env/args.txt", "$run[language]\n$run[name]\n$problem[timelimit]\n");
        file_set("env/input.txt", stripslashes($problem["input"]));

        echo "<!--";
        system("env\\run.py");
        echo "-->";
        $result = file_get("env/result.txt");

        if ($result == "CE") {
            $run["time"] = "-";
            $run["result"] = "CE";
        } else if ($result == "TLE") {
            $run["time"] = "-";
            $run["result"] = "TLE";
        } else if ($result != "-1") {
            $run["time"] = $result . " s";
            if (!file_exists("env/output.txt")) {
                if ($run["language"] == "C" or $run["language"] == "C++") {
                    system("env\\$run[name].exe <env\\input.txt> env\\output.txt");
                } else if ($run["language"] == "Java") {
                    system("java -classfile env $run[name] <env\\input.txt> env\\output.txt");
                } else if ($run["language"] == "Perl") {
                    system("env\\$run[name].pl <env\\input.txt> env\\output.txt");
                } else if ($run["language"] == "Python") {
                    system("env\\$run[name].py <env\\input.txt> env\\output.txt");
                }
            }
            $run["result"] = ($problem["output"] == file_get("env/output.txt")) ? "AC" : "WA";
        }
        if ($result != "-1") {
            mysqli_query($link,"UPDATE runs SET time='$run[time]' WHERE rid='$run[rid]'");
            mysqli_query($link,"UPDATE runs SET result='$run[result]' WHERE rid='$run[rid]'");
            if (file_exists("env/error.txt")) {
                $error = addslashes(addslashes(file_get("env/error.txt")));
            } else {
                $error = "";
            }
            mysqli_query($link,"UPDATE runs SET error='$error' WHERE rid='$run[rid]'");
        }
    }

    if ($invalid) {
        echo "<tr><td>NA</td><td>NA</td><td>NA</td><td>NA</td><td>NA</td><td>NA</td><td>NA</td></tr>";
        echo "<tr><td colspan=10>Waiting For Submissions</td></tr>";
        echo "</table><br><center><input id='terminate' type='button' value='Terminate Execution Protocol' onClick=\"window.location='?display=admincontest'\"></center>";
        echo "<script>window.setTimeout(\"$('input#terminate').css('display','none'); window.location = window.location;\",3000);</script>";
        unlink("env/lock.txt");
        return;
    }

    $code = filter($run["code"]);
    $code = preg_replace("/\n/", "<br>", $code);
    $code = preg_replace("/\t/", "    ", $code);
    $code = preg_replace("/ /", "&nbsp;", $code);

    $result = $run["result"];
    if (isset($fullresult[$result])) {
        $result = $fullresult[$result];
    }
    echo "<tr><td>$run[rid]</td><td>$problem[name]</td><td>$run[language]</td><td>$teamname</td><td>$realname</td><td>$run[time]</td><td>$result</td></tr>";
    echo "<tr><td colspan='10'><code>$code</code></td></tr>";

    if (file_exists("env/error.txt") && ($errormessage = file_get("env/error.txt")) != "") {
        $filename = "$run[name]." . $extension[$run["language"]];
        if ($run["language"] == "C" || $run["language"] == "C++" || $run["language"] == "Java") {
            $errormessage = preg_replace("/^[^ ]*" . preg_quote($filename, "/") . "/", $realname, $errormessage);
            $errormessage = preg_replace("/\n[^ ]*" . preg_quote($filename, "/") . "/", "\n$realname", $errormessage);
        } else if ($run["language"] == "Perl") {
            $errormessage = str_replace(" " . addslashes($filename) . " ", " $realname ", $errormessage);
        } else if ($run["language"] == "Python") {
            $errormessage = preg_replace("/\"[^ ]*" . preg_quote($filename, "/") . "\"/", "\"$realname\"", $errormessage);
        }
        echo "<tr><th colspan=10>Error Message</th></tr><tr><td colspan=10><code>" . filter($errormessage) . "</code></td></tr>";
        mysqli_query($link,"UPDATE runs SET error='" . addslashes(addslashes($errormessage)) . "' WHERE rid='$run[rid]'");
    }
    echo "</table>";
    echo "<script>window.setTimeout('window.location = window.location;',500);</script>";
    unlink("env/lock.txt");
}


?>