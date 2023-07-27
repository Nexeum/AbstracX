<?php

function display_code(): void
{
    global $fullresult, $extension, $brush;
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if (empty($_GET["rid"])) {
        $rid = 0;
    } else {
        $rid = $_GET["rid"];
    }
    $run = mysqli_query($link,"SELECT * FROM runs WHERE rid=$rid AND access!='deleted'");
    $error = 0;
    if (mysqli_num_rows($run) == 0) {
        $error = 1;
    } else {
        $run = mysqli_fetch_array($run);
    }
    if (!$error) {
        $problemQuery = mysqli_query($link,"SELECT * FROM problems WHERE pid=$run[pid]");
        if (mysqli_num_rows($problemQuery) == 0) {
            $error = 2;
        } else {
            $problem = mysqli_fetch_array($problemQuery);
        }
    }
    if (!$error) {
        $team = mysqli_query($link,"SELECT * FROM teams WHERE tid=$run[tid]");
        if (mysqli_num_rows($team) == 0) {
            $error = 3;
        } else {
            $team = mysqli_fetch_array($team);
        }
    }
    if ($_SESSION["status"] != "Admin" && $_SESSION["tid"] != $run["tid"] && $run["access"] != "public") {
        $error = 4;
    }

    if ($error) {
        echo "<table><tr><th>Run ID</th><td>NA</td><th>Team Name</th><td>NA</td><th>Result</th><td>NA</td><th>File Name</th><td>NA</td><th rowspan=2>Options</th><td rowspan=2>NA</td></tr>";
        echo "<tr><th>Language</th><td>NA</td><th>Problem Name</th><td>NA</td><th>Run Time</th><td>NA</td><th>Submission Time</th><td>NA</td></tr>";
    }
    if ($error == 1) {
        echo "<tr><td colspan=10>Code you requested does not exist in the Database.</td></tr>";
    }
    if ($error == 2) {
        echo "<tr><td colspan=10>The problem for which this code is a solution does not exist.</td></tr>";
    }
    if ($error == 3) {
        echo "<tr><td colspan=10>The team which submitted this code does not exist.</td></tr>";
    }
    if ($error == 4) {
        echo "<tr><td colspan=10>You are not authorized to access this code.</td></tr>";
    } else if (!$error) {
        $filename = $run["name"] . "." . $extension[$run["language"]];
        $result = $run["result"];
        if (isset($fullresult[$result])) {
            $result = $fullresult[$result];
        }
        $code = preg_replace("/</", "&lt;", $run["code"]);

        $optionsrowone = "";
        $optionsrowtwo = "";

        if ($_SESSION["tid"] || $run["access"] == "public") {
            $optionsrowone .= "<div class='mb-3'><button class='btn btn-outline-primary' onClick=\"window.location='?display=problem&pid=$run[pid]&edit=$rid#bottom';\">Edit</button></div>";
            $optionsrowone .= "<div class='mb-3'><button class='btn btn-outline-info' onClick=\"window.location='?download=code&rid=$rid';\">Download</button></div>";
        }
        if ($_SESSION["status"] == "Admin") {
            $optionsrowone .= "<div class='mb-3'><button class='btn btn-outline-warning' onClick=\"window.location='?action=rejudge&rid=$run[rid]';\">Rejudge</button></div>";
        
            if ($run["access"] == "private") {
                $optionsrowtwo .= "<div class='mb-3'><button class='btn btn-outline-secondary' title='Make this code Public (visible to all).' onClick=\"window.location='?action=makecodepublic&rid=$rid';\">Private</button></div>";
            } else {
                $optionsrowtwo .= "<div class='mb-3'><button class='btn btn-outline-success' title='Make this code Private (visible only to the team that submitted it).' onClick=\"window.location='?action=makecodeprivate&rid=$rid';\">Public</button></div>";
            }
        
            $optionsrowtwo .= "<div class='mb-3'><button class='btn btn-outline-danger' onClick=\"if(confirm('Are you sure you wish to disqualify Run ID $run[rid]?')) window.location='?action=makecodedisqualified&rid=$run[rid]';\">Disqualify</button></div>";
            $optionsrowtwo .= "<div class='mb-3'><button class='btn btn-outline-danger' onClick=\"if(confirm('Are you sure you wish to delete Run ID $run[rid]?'))window.location='?action=makecodedeleted&rid=$run[rid]';\">Delete</button></div>";
        }
              

        echo "
            <table class='table table-borderless'>
                <thead>
                    <tr class='table-primary'>
                        <th colspan='5'>
                            <h3>Source Code</h3>
                        </th>
                    </tr>  
                    <tr class='table-info'>
                        <th>Run ID</th>
                        <th>Team Name</th>
                        <th>Problem Name</th>
                        <th>Result</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tr>
                    <td>$rid</td>
                    <td><a class='list-group-item' href='?display=submissions&tid=$team[tid]'>$team[teamname]</a></td>
                    <td><a class='list-group-item' href='?display=problem&pid=$problem[pid]' title='$problem[code]'>$problem[name]</td>
                    <td>$result</td>
                    <td>$optionsrowone</td>
                </tr>
                <tr class='table-info'>
                    <th>Language</th>
                    <th>File Name</th>
                    <th>Submission Time</th>
                    <th>Run Time</th>
                    <th>Advanced</th>
                </tr>
                <tr>
                    <td>" . ($run["language"]) . "</td>
                    <td>$filename</td>
                    <td>" . fdate($run["submittime"]) . "</td>
                    <td>$run[time]</td>
                    <td>$optionsrowtwo</td>
                </tr>
                <tr>
                    <th class='table-info' colspan='5'>Code</th>
                </tr>
                <tr>
                    <td class='text-start' colspan='5'>
                        <pre>
                            <code class='language-".$run['language']."'>$code</code>
                        </pre>
                    </td>
                </tr>";
        
        if (($run["result"] != "RTE" || $_SESSION["status"] == "Admin") && !empty($run["error"])) {
            echo "
                <tr>
                    <th colspan=10>Error Message</th>
                </tr>
                <tr>
                    <td colspan='5'>
                        <div class='limit'>
                            <pre class='brush:text'>" . htmlentities(preg_replace("/<br>/i", "\n", filter($run["error"]))) . "</pre>
                        </div>
                    </td>
                </tr>";
        }
        if (($_SESSION["status"] == "Admin" || $run["access"] == "public") and isset($_GET["io"]) and $_GET["io"] == "yes") {
            $problem['input'] = filter($problem['input']);
            $problem['output'] = filter($problem['output']);
            if ($run["result"] == "AC") {
                $run['output'] = $problem['output'];
            } else {
                $run['output'] = filter($run['output']);
            }
            $problem['input'] = explode("<br>", $problem['input']);
            $problem['output'] = explode("<br>", $problem['output']);
            $run['output'] = explode("<br>", $run['output']);
            $k = count($problem["input"]);
            $k = max(count($problem['output']), count($run['output']));
            $l = strlen("" . $k);
            $fm = -1;
            for ($i = 0, $j = 0; $i < $k; $i++) {
                if ($i > count($problem['output'])) {
                    $problem['output'][$i] = "<red>" . $run['output'][$i] . "<red>";
                    $j++;
                    if ($fm == -1) {
                        $fm = $i + 1;
                    }
                    continue;
                }
                if ($i > count($run['output'])) {
                    $problem['output'][$i] = "<green>" . $problem['output'][$i] . "<green>";
                    $j++;
                    if ($fm == -1) {
                        $fm = $i + 1;
                    }
                    continue;
                }
                $p = strstr($problem["options"], "P");
                if (!$p && isset($problem['output'][$i]) && isset($run['output'][$i]) && $problem['output'][$i] == $run['output'][$i]) {
                    continue;
                }
                $pout = preg_replace("/ +/", " ", preg_replace("/ *$/", "", preg_replace("/^ */", "", $problem['output'][$i])));
                if (isset($run['output'][$i])) {
                    $rout = preg_replace("/ +/", " ", preg_replace("/ *$/", "", preg_replace("/^ */", "", $run['output'][$i])));
                }

                if ($p and $pout == $rout) {
                    continue;
                }
                if (isset($run['output'][$i])) {
                    $run['output'][$i] = "<red>" . ($p ? $rout : $run['output'][$i]) . "</red>";
                }
                $problem['output'][$i] = "<green>" . ($p ? $pout : $problem['output'][$i]) . "</green>";
                $j++;
                if ($fm == -1) {
                    $fm = $i + 1;
                }
            }
            if (count($problem['input'])) {
                $problem['input'][0] .= "<br>";
            }
            if (count($problem['output'])) {
                $problem['output'][0] .= "<br>";
            }
            if (count($run['output'])) {
                $run['output'][0] .= "<br>";
            }
            $problem['input'] = str_replace(" ", "&nbsp;", implode("<br>", $problem['input']));
            $problem['output'] = str_replace(" ", "&nbsp;", implode("<br>", $problem['output']));
            $run['output'] = str_replace(" ", "&nbsp;", implode("<br>", $run['output']));
            echo "
                </table>
                <br>
                <table class='table table-borderless io'>
                    <thead>
                        <tr class='table-primary'>
                            <th colspan='3'>Input/Output Info</th>
                        </tr>
                        <tr class='table-info'>
                            <th><a class='list-group-item' href='?download=input&pid=$problem[pid]'>Program Input</a></th>
                            <th><a class='list-group-item' href='?download=output&pid=$problem[pid]'>Correct Output</a></th>
                            <th><a class='list-group-item' href='?download=output&rid=$rid'>Actual Output</a></th>
                        </tr>
                    </thead>
                    <tr>
                        <td>
                            <div id='input'>
                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text-start'>
                                            <code>" . $problem['input'] . "</code>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td>
                            <div id='output'>
                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text-start'>
                                            <code>" . $problem['output'] . "</code>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td>
                            <div id='actual'>
                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text-start'>
                                            <code>" . $run['output'] . "</code>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class='btn btn-outline-primary' onClick=\"window.location=window.location.search.replace(/[\?\&]io\=[^&]*/,'');\">Hide Input Output Files</button>
                        </td>
                        <td colspan='2'>
                            Matching Lines : " . ($k - $j) . "/" . $k . "<br>" . ($fm != -1 ? "First mismatch at line $fm." : "") . "
                        </td>
                    </tr>
                </table>";
    
        } else if (($_SESSION["status"] == "Admin" || $run["access"] == "public") and in_array($run["result"], array("AC", "WA", "PE", "RTE"))) {
            echo "
                </table>
                <div class='d-flex justify-content-center'>
                    <button class='btn btn-outline-primary' style='display: block;' onClick=\"window.location=window.location.search.replace(/[\?\&]io\=[^&]*/,'')+'&io=yes';\">Display Input Output Files</button>
                </div>";
        } else {
            echo "</table>";
        }
        return;
    }
    echo "</table>";
}


function action_submitcode()
{
    global $sessionid, $admin, $maxcodesize;
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($admin["mode"] != "Passive" && $admin["mode"] != "Active" && $_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Code Submission Error : You are not allowed to submit solutions right now!";
        return;
    }

    if (empty($_POST["code_pid"]) || empty($_POST["code_lang"]) || empty($_POST["code_name"])) {
        $_SESSION["message"][] = "Code Submission Error : Insufficient Data";
        return;
    }

    $data = mysqli_query($link,"SELECT status,languages,pgroup FROM problems WHERE pid='$_POST[code_pid]'");
    if (mysqli_num_rows($data) == 0) {
        $_SESSION["message"][] = "Code Submission Error : The specified problem does not exist.";
        return;
    }
    $data = mysqli_fetch_array($data);
    if ($_SESSION["status"] != "Admin" && $data["status"] != "Active") {
        $_SESSION["message"][] = "Code Submission Error : The problem specified is not currently active.";
        return;
    }
    if ($_SESSION["status"] != "Admin" && preg_match("/^#[0-9]+ CQM\-[0-9]+$/i", $data["prgoup"]) && $admin["mode"] == "Passive") {
        $_SESSION["message"][] = "Code Submission Error : You can no longer submit solutions to this problem.";
        return;
    }

    if ($_SESSION["status"] != "Admin" && !in_array($_POST["code_lang"], explode(",", $data["languages"]))) {
        $_SESSION["message"][] = "Code Submission Error : The programming language specified is not allowed for this problem.";
        return;
    }
    if (strlen($_POST["code_text"]) > $maxcodesize) {
        $_SESSION["message"][] = "Code Submission Error : Submitted code exceeds size limits.";
        return;
    }
    $sourcecode = addslashes($_POST["code_text"]);
    $ext = file_upload("code_file", "sys/temp/" . $sessionid . "_code", "text/plain,text/x-c,text/x-c++src,application/octet-stream,application/x-javascript,application/x-ruby", 100 * 1024);
    if ($ext != -1) {
        $sourcecode = addslashes(file_get("sys/temp/" . $sessionid . "_code.$ext"));
        unlink("sys/temp/" . $sessionid . "_code.$ext");
        $_POST["code_name"] = preg_replace("/\.(b|c|cpp|java|pl|php|py|rb|txt)$/i", "", $_FILES['code_file']['name']);
    }
    if (!empty($sourcecode)) {
        mysqli_query($link,"INSERT INTO runs (pid,tid,language,name,code,access,submittime) VALUES ('$_POST[code_pid]','$_SESSION[tid]','$_POST[code_lang]','$_POST[code_name]','$sourcecode','private'," . time() . ")");
        $rid = mysqli_insert_id($link);
        $_SESSION["message"][] = "Code Submission Successful.\nIf the code you submitted is not being evaluated immediately, please contact \"SourceCode\" on DC.";
        $_SESSION["redirect"] = "?display=code&rid=$rid";
    } else {
        $_SESSION["message"][] = "Code Submission Error : Cannot submit empty code.";
    }
    return mysqli_insert_id($link);
}


function action_rejudge(): void
{
    global $extension, $fullresult;
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    $condition = "";
    if (!empty($_GET["rid"]) && is_numeric($_GET["rid"])) {
        $condition .= " AND rid=$_GET[rid] ";
    }
    if (!empty($_GET["tid"]) && is_numeric($_GET["tid"])) {
        $condition .= " AND tid=$_GET[tid] ";
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"])) {
        $condition .= " AND pid=$_GET[pid] ";
    }
    if (!empty($_GET["lan"]) && key_exists($_GET["lan"], $extension)) {
        $condition .= " AND language='$_GET[lan]' ";
    }
    if (!empty($_GET["res"]) && key_exists($_GET["res"], $fullresult)) {
        $condition .= " AND result='$_GET[res]' ";
    }
    if ((!isset($_GET["all"]) || $_GET["all"] != 1) && $condition == "") {
        $_SESSION["message"][] = "Run Data Updation Error : Insufficient Data";
        return;
    }
    mysqli_query($link,"UPDATE runs SET time=NULL,result=NULL WHERE access!='deleted' $condition");
}

function action_makecodepublic(): void
{
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["rid"]) && is_numeric($_GET["rid"])) {
        mysqli_query($link,"UPDATE runs SET access='public' WHERE rid=" . $_GET["rid"]);
    }
    else {
        $_SESSION["message"][] = "Run Data Updation Error : Insufficient Data";
        return;
    }
}

function action_makecodeprivate(): void
{
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["rid"]) && is_numeric($_GET["rid"])) {
        mysqli_query($link,"UPDATE runs SET access='private' WHERE rid=" . $_GET["rid"]);
    } else {
        $_SESSION["message"][] = "Run Data Updation Error : Insufficient Data";
        return;
    }
}

function action_makecodedisqualified(): void
{
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["rid"]) && is_numeric($_GET["rid"])) {
        mysqli_query($link,"UPDATE runs SET result='DQ' WHERE rid=" . $_GET["rid"]);
        $_SESSION["message"][] = "Code Disqualification Successful.";
    } else {
        $_SESSION["message"][] = "Run Data Updation Error : Insufficient Data";
        return;
    }
}

function action_makecodedeleted(): void
{
    $link = mysqli_connect("localhost", "root", "","nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["rid"]) && is_numeric($_GET["rid"])) {
        mysqli_query($link,"UPDATE runs SET access='deleted' WHERE rid=" . $_GET["rid"]);
        $_SESSION["message"][] = "Code Deletion Successful.";
    } else {
        $_SESSION["message"][] = "Run Data Updation Error : Insufficient Data";
        return;
    }
}
