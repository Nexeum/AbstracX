<?php

function display_problem()
{
    global $admin, $currentmessage, $defaultlang, $maxcodesize, $execoptions;
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($admin["mode"] == "Lockdown" && $_SESSION["tid"] == 0) {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"] = "Access Denied : The contest is currently in Lockdown Mode. Please try again later.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
    if (!empty($_GET["pid"])) {
        $pid = $_GET["pid"];
    } else {
        $pid = 0;
    }
    if ($_SESSION["status"] == "Admin") {
        $data = mysqli_query($link,"SELECT * FROM problems WHERE pid=$pid ");
    } else {
        $data = mysqli_query($link,"SELECT * FROM problems WHERE status='Active' and pid=$pid");
    }
    if ($pid != 0) {
        if (mysqli_num_rows($data) != 1) {
            $_SESSION["message"] = $currentmessage;
            $_SESSION["message"][] = "Problem Access Error : The problem you requested does not exist or is currently inactive.";
            $pid = 0;
        }
    }
    if ($pid == 0) {
        echo "<center>";
        if (($g = mysqli_getdata("SELECT distinct pgroup FROM problems WHERE status='Active' ORDER BY pgroup")) != NULL) {
            $t = array();
            foreach ($g as $gn) {
                $t[] = $gn["pgroup"];
            }
            $g = $t;
            unset($t);
            if (in_array("", $g)) {
                unset($g[array_search("", $g)]);
                $g[] = "";
            }
            echo "<div class='filter'><b>Select Group<b> : <select class='form-select' id='category-select' onChange=\"$('input#query').attr('value',''); problem_search(); if(this.value==0){ $('span.group').slideDown(250); } else { for(i=1;i<=" . count($g) . ";i++){ if(this.value=='group'+i) $('span#group'+i).slideDown(250); else $('span#group'+i).slideUp(250); } }\"><option value=0>All Groups</option>";
            foreach ($g as $i => $gn) {
                echo "<option value='group" . ($i + 1) . "'>" . preg_replace("/^#[0-9]+ /", "", ($gn == "" ? "Unclassified" : $gn)) . "</option>";
            }
            echo "</select> <input class='form-control' placeholder='Enter Search Term Here' id='query' onKeyUp=\"$('#category-select').val(0); $('span.group').slideDown(250); problem_search();\"> <input class='btn btn-danger' type='button' value='Clear' onClick=\"$('input#query').attr('value',''); problem_search();\"></div>";
            if (($nac = mysqli_getdata("SELECT distinct pid FROM runs WHERE tid=$_SESSION[tid] AND result!='AC' AND access!='deleted'")) == NULL) {
                $nac = array();
            } else {
                $t = array();
                foreach ($nac as $sp) {
                    $t[] = $sp["pid"];
                }
                $nac = $t;
                unset($t);
            }
            if (($ac = mysqli_getdata("SELECT distinct pid FROM runs WHERE tid=$_SESSION[tid] AND result='AC' AND access!='deleted'")) == NULL) {
                $ac = array();
            } else {
                $t = array();
                foreach ($ac as $sp) {
                    $t[] = $sp["pid"];
                }
                $ac = $t;
                unset($t);
            }
            echo "<div id='probindex' class='probindex'>";
            echo "<div class='probheaders2' style='display:none;'><h2>Search Results</h2>";
            echo "<table><th>Problem ID</th><th>Problem Name</th><th>Problem Code</th><th>Problem Type</th><th>Score</th><th>Statistics</th></tr></table></div>";
            foreach ($g as $i => $gn) {
                echo "<span id='group" . ($i + 1) . "' class='group'><div class='probheaders1'><h2><a id='aSubmissions' href='?display=submissions&pgr=" . urlencode($gn) . "'>Problem Group : " . preg_replace("/^#[0-9]+ /i", "", ($gn == "" ? "Unclassified" : $gn)) . "</a></h2>";
                echo "<table><th>Problem ID</th><th>Problem Name</th><th>Problem Code</th><th>Problem Type</th><th>Score</th><th>Statistics</th></tr></table></div>";
                $data = mysqli_query($link,"SELECT * FROM problems WHERE status='Active' and pgroup='" . $gn . "' ORDER BY pid");
                while ($problem = mysqli_fetch_array($data)) {
                    $t = mysqli_query($link,"SELECT (SELECT count(*) FROM runs WHERE pid=$problem[pid] AND result='AC' AND access!='deleted') as ac, (SELECT count(*) FROM runs WHERE pid=$problem[pid] AND access!='deleted') as tot");
                    if (mysqli_num_rows($t) && $t = mysqli_fetch_array($t)) {
                        $statistics = "<a class='list-group-item' title='Accepted Solutions / Total Submissions' class='list-group-item' href='?display=submissions&pid=$problem[pid]'>" . $t["ac"] . " / " . $t["tot"] . "</a>";
                    } else {
                        $statistics = "NA";
                    }
                    if ($_SESSION["tid"] < 1) {
                        $highlight = "NA";
                    }
                    elseif (in_array($problem["pid"], $ac)) {
                        $highlight = "AC";
                    }
                    elseif (in_array($problem["pid"], $nac)) {
                        $highlight = "NAC";
                    } else {
                        $highlight = "NA";
                    }
                    echo "<div class='problem'><table class='submission'><tr class='$highlight'><td><a href='?display=problem&pid=$problem[pid]'>$problem[pid]</a></td><td><a href='?display=problem&pid=$problem[pid]'>" . stripslashes($problem["name"]) . "</a></td><td><a href='?display=problem&pid=$problem[pid]'>" . stripslashes($problem["code"]) . "</a></td>";
                    if ($admin["mode"] != "Active" || $_SESSION["status"] == "Admin") {
                        echo "<td><a href='#' onClick=\"$('input#query').attr('value','" . $problem["type"] . "'); problem_search();\">" . stripslashes($problem["type"]) . "</td>";
                    } else {
                        echo "<td>NA</td>";
                    }
                    echo "<td><a href='?display=problem&pid=$problem[pid]'>$problem[score]</a></td><td>$statistics</td></tr></table></div>";
                }
                echo "</span>";
            }
        }
        echo "</div>";
        return;
    }
    $row = mysqli_fetch_array($data);

    $statement = stripslashes($row["statement"]);
    $statement = preg_replace("/\n/i", "<br>", $statement);
    if ($_SESSION["status"] == "Admin") {
        $statement2 = stripslashes($row["statement"]);
    }
    $statement = preg_replace("/<image\s*\/?>/i", "<img src='data:image/jpeg;base64," . $row['image'] . "' />", $statement);
    $tQuery = mysqli_query($link,"SELECT (SELECT count(*) FROM runs WHERE pid=$pid AND result='AC' AND access!='deleted') as ac, (SELECT count(*) FROM runs WHERE pid=$pid AND access!='deleted') as tot");
    if (mysqli_num_rows($tQuery) && $tResult = mysqli_fetch_array($tQuery)) {
        $statistics = "<a class='list-group-item' title='Accepted Solutions / Total Submissions' href='?display=submissions&pid=$pid'>" . $tResult["ac"] . "/" . $tResult["tot"] . "</a>";
    } else {
        $statistics = "NA";
    }
    $pgroup = preg_replace("/^#[0-9]+ /i", "", $row["pgroup"]);
    echo "<table class='table table-borderless'><thead><tr class='table-primary'><th colspan='6'><h3>Problem : $row[name] (" . $pgroup . " Group)</h3></th></tr></thead><tbody>
		<tr><th class='table-info'>Problem ID</th><th>$pid</th><th class='table-info'>Input File Size</th><th>" . display_filesize(strlen($row["input"])) . "</th><th class='table-info'><a class='list-group-item' href='?display=submissions&pid=$pid'>Submissions</a></th><th>$statistics</th></tr>
		<tr><th class='table-info'>Problem Code</th><th>$row[code]</th><th class='table-info'>Time Limit</th><th>$row[timelimit] sec</th><th class='table-info'>Points</th><th>$row[score]</th></tr>";
    if ($_SESSION["status"] == "Admin") {
        echo "<tr><th class='table-info'>Special Options</th><th colspan=3>" . $execoptions[$row["options"]] . "</th><th colspan=2><input class='btn btn-outline-info'type='button' value='" . ((isset($_GET["edit"]) and $_GET["edit"] == "0") ? "Reset" : "Edit") . " HTML Source' onClick=\"window.location=window.location.search.replace('&edit=0','')+'&edit=0';\"></th></tr>";
    }
    echo "<tr><td class='text-start' colspan='6'>";
    if ($_SESSION["status"] == "Admin" and isset($_GET["edit"]) and $_GET["edit"] == "0") {
        echo "<form method='post' action='?action=updateproblemhtml&pid=$pid'><textarea name='statement' id='statement'>" . ($statement2) . "</textarea><input type='submit' value='Update Problem Statement'> <input type='button' value='Cancel' onClick=\"window.location=window.location.search.replace('&edit=0','');\"></form>";
    } else {
        echo $statement;
    }
    echo "</td></tr><tr><td colspan='6'><b>Language(s) Allowed</b> : ";
    echo preg_replace("/,/", ", ", $row["languages"]);
    echo "</td></tr>";

    $languages = "";
    if (isset($row["languages"])){
        foreach (explode(",", $row["languages"]) as $l){
            if ($l == $defaultlang) {
                $languages .= "<option value='$l' selected='selected'>$l</option>";
            } else {
                $languages .= "<option value='$l'>$l</option>";
            }            
        }
    }
    $data = mysqli_query($link,"SELECT * FROM clar WHERE access='Public' and clar.pid=$pid ORDER BY time ASC");
    if (mysqli_num_rows($data) > 0) {
        if (mysqli_num_rows($data)) {
            echo "<tr><th colspan='20'><a href='?display=clarifications'>Clarifications</a></th></tr><tr><td colspan='20'>";
            while ($temp = mysqli_fetch_array($data)) {
                $teamnameQuery = mysqli_query($link,"SELECT teamname FROM teams WHERE tid=" . $temp["tid"]);
                if (mysqli_num_rows($teamnameQuery) == 1) {
                    $teamnameResult = mysqli_fetch_array($teamnameQuery);
                    $teamname = $teamnameResult["teamname"];
                } else {
                    $teamname = "Anonymous";
                }
                echo "<p><b><a href='?display=submissions&tid=" . $temp["tid"] . "'>" . filter($teamname) . "</a></b> : $temp[query]";
                if (!empty($temp["reply"])) {
                    echo "<br><i><b>Response</b> : $temp[reply]</i>";
                }
                echo "</p>";
            }
            echo "</td></tr>";
        }
    }
    echo "</tbody></table>";
    if ($_SESSION["tid"] == 0) {
        echo "<p>Please login to submit solutions.</p>";
    } else if ($admin["mode"] != "Active" && $admin["mode"] != "Passive" && $_SESSION["status"] != "Admin") {
        echo "<p>You cannot submit solutions at the moment as the contest is not running. Please try again later.</p>";
    } else if ($admin["mode"] == "Passive" && $_SESSION["status"] != "Admin" && preg_match("/^CQM\-[0-9]+$/i", $pgroup)) {
        echo "<p>You can no longer submit solutions to this problem.</p>";
    } else {
        $editcode = "";
        if (isset($_GET["edit"])) {
            $rid = $_GET["edit"];
            if (!is_numeric($rid)) {
                $rid = 0;
            }
            $t = mysqli_query($link,"SELECT tid,language,code,access FROM runs WHERE rid=$rid AND access!='deleted'");
            if (mysqli_num_rows($t) == 1) {
                $run = mysqli_fetch_array($t);
                if ($_SESSION["tid"] == $run["tid"] || $run["access"] == "public" || $_SESSION["status"] == "Admin") {
                    $editcode = preg_replace("/</i", "&lt;", $run["code"]);
                }
                $languages = str_replace(">$run[language]</option>", " selected='selected'>$run[language]</option>", str_replace(" selected='selected'", "", $languages));
            }
        }
        global $extension, $codemirror;
        $extcompare = "";
        foreach ($extension as $lang => $ext) {
            $extcompare .= "if(ext=='$ext'){ $('select#code_lang').attr('value','" . ($lang) . "'); } ";
        }
        echo "<center><h2>Submit Solution : $row[name]</h2>
			<script>
			    function code_validate(){ 
                    if(document.forms['submitcode'].code_file.value=='' && document.forms['submitcode'].code_text.value==''){ 
                        alert('Code file not specified and textarea empty. Cannot submit nothing.'); 
                        return false; 
                    } if(document.forms['submitcode'].code_lang.value=='Java' && document.forms['submitcode'].code_file.value=='' && document.forms['submitcode'].code_text.value!=''){ 
                        x = prompt('You are copy-pasting Java code here. Please enter the class name you have used so\\nthat the server can create a source file of the same name while evaluating your code :\\n '); 
                    }
                    if(!x) {
                       return false;
                    } else {
                        $('input#code_name').val(x); 
                    } 
                    
                    document.forms['submitcode'].code_text.value=addslashes(document.forms['submitcode'].code_text.value); 
                    return true; 
                }
            </script>
			<form action='?action=submitcode' method='post' name='submitcode' enctype='multipart/form-data' onSubmit=\"return code_validate();\"><input type='hidden' name='code_pid' value='$pid'>
			<table width=100%><tr><th>Language</th><th><select id='code_lang' name='code_lang'>" . $languages . "</select></th><input type='hidden' name='MAX_FILE_SIZE' value='$maxcodesize' />";
        echo "<th>Code File</th><th><input type='file' name='code_file' onChange=\"if(this.value!=''){ filename = this.value.split('.'); ext = filename[filename.length-1]; $extcompare }\" /></th></tr>
			<tr><td colspan=20><textarea id='code_text' name='code_text' class='code' onChange=\"if(this.value!='') $('select#code_mode').attr('value','Text');\">$editcode</textarea></td></tr></table>
			<table width=100%> <input type='hidden' name='code_name' id='code_name' value='code'>
			<tr><th><div class='small'>If you submit both File and Text (copy-pasted in the above textarea), the Text will be ignored.</div></th><th><input type='submit' value='Submit Code'></th></tr>
			</table></form></center>";
    }
}


function action_makeproblem()
{
    global $sessionid, $invalidchars, $maxfilesize;
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    foreach ($_POST as $key => $value) {
        if (preg_match("/^make_/i", $key)) {
            if (empty($value) && $key != "make_type" && $key != "make_score" && $key != "make_options") {
                $_SESSION["message"][] = "Problem Creation Error : Insufficient (Text) Data" . $key;
                return;
            }
        }
    }
    if (!isset($_FILES["make_file_statement"]) || !isset($_FILES["make_file_input"]) || !isset($_FILES["make_file_output"])) {
        $_SESSION["message"][] = "Problem Creation Error : Insufficient (File) Data";
        return;
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^make_/i", $key) && preg_match("/$invalidchars/i", $value)) {
            $_SESSION["message"][] = "Problem Creation Error: Value of $key contains invalid characters.";
            return;
        }
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^make_/i", $key) && $key != "make_languages" && strlen($value) > 30) {
            $_SESSION["message"][] = "Problem Creation Error: Value of $key is too long.";
            return;
        }
    }

    if (empty($_POST["make_score"])) {
        $_POST["make_score"] = "0";
    }
    $temp1 = $temp2 = array();
    foreach ($_POST as $key => $value) {
        if (preg_match("/^make_/i", $key) && !preg_match("/^make_file_/i", $key)) {
            $temp1[] = preg_replace("/^make_/i", "", $key);
            $temp2[] = filter($value);
        }
    }
    foreach (array("statement", "input", "output") as $item) {
        $ext = file_upload("make_file_$item", "sys/temp/" . $sessionid . "_$item", "text/plain", $maxfilesize);
        if ($ext == -1) {
            $_SESSION["message"][] = "Problem Creation Error : Could not upload $item File";
            return;
        }
        $temp1[] = $item;
        $temp2[] = addslashes(preg_replace("/\r/i", "", file_get_contents("sys/temp/" . $sessionid . "_$item.$ext")));
        unlink("sys/temp/" . $sessionid . "_$item.$ext");
    }
    $ext = file_upload("make_file_image", "sys/temp/image", "image/jpeg,image/gif,image/png", $maxfilesize);
    if ($ext != -1) {
        $f = fopen("sys/temp/image.$ext", "rb");
        $temp1[] = "image";
        $temp2[] = base64_encode(fread($f, filesize("sys/temp/image.$ext")));
        fclose($f);
        $temp1[] = "imgext";
        $temp2[] = $ext;
    }
    mysqli_query($link,"INSERT INTO problems (" . implode(",", $temp1) . ",status) VALUES ('" . implode("','", $temp2) . "','Inactive')");
    {
        $_SESSION["message"][] = "Problem Creation Successful";
        return;
    }
}


function action_updateproblem()
{
    global $sessionid, $invalidchars, $maxfilesize;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if (empty($_POST["update_pid"])) {
        $_SESSION["message"][] = "Problem Updation Error : Insufficient Data";
        return;
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && preg_match("/$invalidchars/i", $value)) {
            $_SESSION["message"][] = "Problem Updation Error: Value of $key contains invalid characters.";
            return;
        }
    }
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && $key != "update_languages" && strlen($value) > 30 && $key != "update_languages") {
            $_SESSION["message"][] = "Problem Updation Error: Value of $key is too long.";
            return;
        }
    }
    $pid = $_POST["update_pid"];
    foreach ($_POST as $key => $value) {
        if (preg_match("/^update_/i", $key) && !preg_match("/^update_file_/i", $key) && $key != "update_pid" && $key != "update_delete") {
            mysqli_query($link, "UPDATE problems SET " . preg_replace("/^update_/i", "", $key) . "='" . addslashes(preg_replace("/\"/i", "'", $value)) . "' WHERE pid=$pid");
        }
    }

    foreach (array("statement", "input", "output") as $item) {
        $ext = file_upload("update_file_$item", "sys/temp/" . $sessionid . "_$item", "text/plain", $maxfilesize);
        if ($ext == -1) {
            continue;
        }
        mysqli_query($link, "UPDATE problems SET $item='" . addslashes(preg_replace("/\r/i", "", file_get_contents("sys/temp/" . $sessionid . "_$item.$ext"))) . "' WHERE pid=$pid");
        unlink("sys/temp/" . $sessionid . "_$item.$ext");
    }

    $ext = file_upload("update_file_image", "sys/temp/image", "image/jpeg,image/gif,image/png", $maxfilesize);
    if ($ext != -1) {
        $f = fopen("sys/temp/image.$ext", "rb");
        $img = base64_encode(fread($f, filesize("sys/temp/image.$ext")));
        fclose($f);
        mysqli_query($link,"UPDATE problems SET image='$img', imgext='$ext' WHERE pid=$pid");
    }

    if (0) {
        if (isset($_POST["update_status"]) && $_POST["update_status"] == "Delete") {
            mysqli_query($link, "DELETE FROM problems WHERE pid=$pid");
        }
        $_SESSION["message"][] = "Problem Updation Successful";
        return;
    }
}

function action_updateproblemhtml()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"]) && !empty($_POST["statement"])) {
        mysqli_query($link,"UPDATE problems SET statement='" . addslashes($_POST["statement"]) . "' WHERE pid=" . $_GET["pid"]);
        $_SESSION["redirect"] = "?display=problem&pid=" . $_GET["pid"];
    } else {
        $_SESSION["message"][] = "Problem HTML Source Updation Error : Insufficient Data";
        return;
    }
}

function action_makeproblemactive()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"])) {
        mysqli_query($link,"UPDATE problems SET status='Active' WHERE pid=" . $_GET["pid"]);
    } else {
        $_SESSION["message"][] = "Problem Status Updation Error : Insufficient Data";
        return;
    }
}

function action_makeprobleminactive()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"])) {
        mysqli_query($link,"UPDATE problems SET status='Inactive' WHERE pid=" . $_GET["pid"]);
    }
    else {
        $_SESSION["message"][] = "Problem Status Updation Error : Insufficient Data";
        return;
    }
}

function action_problem_status($type)
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform this action.";
        return;
    }
    mysqli_query($link,"UPDATE problems SET status='$type';");
    $_SESSION["message"][] = "Problem Status Updation Successful. All problems are now $type.";
}

?>