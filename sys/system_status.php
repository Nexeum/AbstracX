<?php

function display_submissions()
{
    global $admin, $fullresult, $currentmessage, $extension;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"] = "Access Denied : The contest is currently in Lockdown Mode. Please try again later.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }

    $urlargs = "display=submissions";
    foreach ($_GET as $key => $value) {
        if ($key != "display" && $key != "page") {
            $urlargs .= "&" . urlencode($key) . "=" . urlencode($value);
        }
    }
    $rejudge = "action=rejudge";
    $filter = array();
    $filters = array();
    if (!empty($_GET["tid"]) && is_numeric($_GET["tid"])) {
        $t = mysqli_query($link, "SELECT * FROM teams WHERE tid=" . $_GET["tid"] . " and (status='Normal' or status='Admin')");
        if (mysqli_num_rows($t)) {
            $filter["tid"] = $_GET["tid"];
            $teamdata = mysqli_fetch_array($t);
            $filters[] = "<a href='?" . str_replace("&tid=$filter[tid]", "", $urlargs) . "'>$teamdata[teamname]</a>";
            $rejudge .= "&tid=$filter[tid]";
        }
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"])) {
        $t = mysqli_query($link, "SELECT * FROM problems WHERE pid=" . $_GET["pid"] . " and status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ";");
        if (mysqli_num_rows($t)) {
            $filter["pid"] = $_GET["pid"];
            $probdata = mysqli_fetch_array($t);
            $filters[] = "<a href='?" . str_replace("&pid=$filter[pid]", "", $urlargs) . "'>$probdata[name]</a>";
            $rejudge .= "&pid=$filter[pid]";
        }
    }
    if (!empty($_GET["lan"]) && key_exists($_GET["lan"], $extension)) {
        $filter["language"] = $_GET["lan"];
        $filters[] = "<a href='?" . str_replace("&lan=" . urlencode($filter["language"]), "", $urlargs) . "'>" . ($filter["language"] == "Brain" ? "Brainf**k" : $filter["language"]) . "</a>";
        $rejudge .= "&lan=" . urlencode($_GET["lan"]);
    }
    if (!empty($_GET["res"]) && key_exists($_GET["res"], $fullresult)) {
        $filter["result"] = $_GET["res"];
        $filters[] = "<a href='?" . str_replace("&res=$filter[result]", "", $urlargs) . "'>" . $fullresult[$filter["result"]] . "</a>";
        $rejudge .= "&res=" . urlencode($_GET["res"]);
    }
    $condition = "";
    foreach ($filter as $key => $value)
        if ($key == "result" && $value == "NA") {
            $condition .= " AND (result='' OR result='...') ";
        } else {
            $condition .= "AND $key=" . (is_numeric($value) ? "$value" : "'$value'") . " ";
        }

    if (($g = mysqli_getdata("SELECT distinct pgroup FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . " ORDER BY pgroup")) != NULL) {
        $t = array();
        foreach ($g as $gn) {
            $t[] = $gn["pgroup"];
        }
        $g = $t;
        unset($t);
    } else {
        $g = array();
    }
    if (!empty($_GET["pgr"]) && in_array($_GET["pgr"], $g)) {
        $t = mysqli_query($link, "SELECT * FROM problems WHERE pgroup='" . addslashes($_GET["pgr"]) . "' and status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . "");
        if (mysqli_num_rows($t)) {
            $filter["pgroup"] = $_GET["pgr"];
            $probdata = mysqli_fetch_array($t);
            $filters[] = "<a href='?" . str_replace("&pgr=" . urlencode($filter["pgroup"]), "", $urlargs) . "'>" . filter(preg_replace('/^#[0-9]+ /', '', $filter["pgroup"])) . "</a>";
            $rejudge .= "&pgr=$filter[pgroup]";
            $condition .= " AND pid in (SELECT pid FROM problems WHERE pgroup='$filter[pgroup]')";
        }
    }

    echo "<center>";

    if (count($filter)) {
        echo "<div class='filter'><b>Active Filter(s)</b> : " . implode(" , ", $filters) . " (Click to Remove)</div>";
    } else {
        echo "<div class='filter'><b>Active Filter(s)</b> : None</div>";
    }

    if (isset($filter["tid"]) || isset($filter["pid"]) || !isset($filter["result"]) || !isset($filter["language"])) echo "<br><h3>";
    if (isset($filter["tid"])) {
        echo "<a onClick=\"$('#team-information').slideToggle();$('#problem-information').slideUp();$('#submission-statistics').slideUp();\" title='Click here to show/hide team information.'>$teamdata[teamname] : Team Information</a>";
        if (isset($filter["pid"]) || !isset($filter["result"]) || !isset($filter["language"])) {
            echo " | ";
        }
    }
    if (isset($filter["pid"])) {
        echo "<a onClick=\"$('#problem-information').slideToggle();$('#team-information').slideUp();$('#submission-statistics').slideUp();\" title='Click here to show/hide problem information.'>$probdata[name] : Problem Information</a>";
        if (!isset($filter["result"]) || !isset($filter["language"])) {
            echo " | ";
        }
    }
    if (!isset($filter["result"]) || !isset($filter["language"])) {
        echo "<a onClick=\"$('#submission-statistics').slideToggle();$('#problem-information').slideUp();$('#team-information').slideUp();\" title='Click here to show/hide submission statistics.'>Submission Statistics</a>";
    }
    if (isset($filter["tid"]) || isset($filter["pid"]) || !isset($filter["result"]) || !isset($filter["language"])) {
        echo "</h3>";
    }

    if (isset($filter["tid"])) {
        $members = array();
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($teamdata["name" . $i])) {
                $members[] = $teamdata["name" . $i];
            }
        }
        $members = implode(", ", $members);

        $data = mysqli_query($link, "SELECT distinct(runs.pid),problems.name,problems.code FROM runs,problems WHERE runs.tid='$filter[tid]' and runs.result='AC' and runs.pid=problems.pid and problems.status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . " and runs.access!='deleted'");
        $solvedn = mysqli_num_rows($data);
        $solvedp = array();
        while ($temp = mysqli_fetch_array($data)) {
            $solvedp[] = "<a href='?display=problem&pid=$temp[pid]' title=\"$temp[code]\">$temp[name]</a>";
        }
        $solvedp = implode(", ", $solvedp);

        echo "<div id='team-information' style='display:none;'>";
        echo "<table width=80%><tr><th>Team Members</th><td>$members</td></tr><tr><th>Score</th><td>$teamdata[score]</td></tr><tr><th>Problems Solved</th><td>$solvedp ($solvedn)</td></tr>";
        echo "</table><br></div>";

    }

    if (isset($filter["pid"])) {
        echo "<div id='problem-information' style='display:none;'><table width=80%>
			<tr><th>Problem ID</th><td>$probdata[pid]</td><th>Problem Type</th><td>$probdata[type]</td><th>Time Limit</th><td>$probdata[timelimit] sec</td></tr>
			<tr><th>Problem Code</th><td>$probdata[code]</td><th>Input File Size</th><td>" . display_filesize(strlen($probdata["input"])) . "</td><th>Score</th><td>$probdata[score]</td></tr>";
        echo "</table><br>";
        echo "<a href='?display=problem&pid=$probdata[pid]'>Link to Problem</a><br></div>";
    }

    if (!isset($filter["result"]) || !isset($filter["language"])) {
        echo "<div id='submission-statistics' style='display:none;'>";
    }
    if (!isset($filter["result"])) {
        $t1 = mysqli_query($link, "SELECT result,count(*) as cnt FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition group by result;");
        for ($info2 = array(); $t2 = mysqli_fetch_array($t1);) {
            if ($t2["result"] == "") {
                $t2["result"] = "...";
            }
            $info2[$t2["result"]] = $t2["cnt"];
        }
        if (!isset($info2["..."])) {
            $info2["..."] = 0;
        }
        $info = array("TOT" => $info2["..."], "..." => $info2["..."]);
        foreach ($fullresult as $key => $value) {
            if (key_exists($key, $info2)) {
                $info["TOT"] += $info[$key] = $info2[$key];
            } else {
                $info[$key] = 0;
            }
        }
        if ($info != NULL) {
            echo "<table class='substat'><tr><th>Total Submissions</th>";
            foreach ($fullresult as $key => $value) {
                echo "<th><a href='?" . ($urlargs) . "&res=$key'>" . ($value) . "</a></th>";
            }
            echo "<th>Unjudged Submissions</th></tr><tr><td>" . $info["TOT"] . "</td>";
            foreach ($fullresult as $key => $value) {
                echo "<td>" . $info[$key] . "</td>";
            }
            echo "<td>" . $info["..."] . "</td></tr></table><br>";
        }
    }
    if (!isset($filter["language"])) {
        $t1 = mysqli_query($link, "SELECT language,count(*) as cnt FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition group by language;");
        for ($info2 = array(); $t2 = mysqli_fetch_array($t1);) {
            $info2[$t2["language"]] = $t2["cnt"];
        }
        $info = array();
        foreach ($extension as $key => $value) {
            if (key_exists($key, $info2)) {
                $info[$key] = $info2[$key];
            } else {
                $info[$key] = 0;
            }
        }
        if ($info != NULL) {
            echo "<table class='substat'><tr>";
            foreach ($extension as $key => $value) {
                echo "<th><a href='?" . ($urlargs) . "&lan=" . urlencode($key) . "'>" . ($key == "Brain" ? "Brainf**k" : $key) . "</a></th>";
            }
            echo "</tr><tr>";
            foreach ($extension as $key => $value) {
                echo "<td>" . $info[$key] . "</td>";
            }
            echo "</tr></table><br>";
        }
    }
    if (!isset($filter["result"]) || !isset($filter["language"])) {
        echo "</div>";
    }

    echo "<h2>Submission Status</h2>";
    if ($_SESSION["status"] == "Admin") {
        if ($rejudge == "action=rejudge") {
            $rejudge .= "&all=1";
        }
        echo "<input class='btn btn-warning' type='button' value='Rejudge Selected Submissions' onClick=\"if(confirm('Are you sure you wish to rejudge all currently selected submissions?'))window.location='?$rejudge';\"><br><br>";
    }

    $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition ORDER BY rid DESC");
    $totalResult = mysqli_fetch_array($totalQuery);
    $totalCount = $totalResult["total"];

    if (isset($admin["substatpage"]) && $admin["substatpage"] >= 0) {
        $perpage = $admin["substatpage"];
    } else {
        $perpage = 25;
    }
    $x = paginate($urlargs, $totalCount, $perpage);
    $page = $x[0];
    $pagenav = $x[1];

    echo $pagenav . "<br><br>";
    echo "<table class='submission'><th>Run ID</th><th>Team</th><th>Problem</th><th>Language</th><th>Time</th><th>Result</th><th " . ($_SESSION["status"] == "Admin" ? "style='width:170px;'" : "") . ">Options</th></tr>";
    $data = mysqli_query($link, "SELECT * FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition ORDER BY rid DESC LIMIT " . (($page - 1) * $perpage) . "," . $perpage);
    $n = mysqli_num_rows($data);
    for ($i = 0; $temp = mysqli_fetch_array($data); $i++) {
        if ($i == $perpage) {
            break;
        }
        if ($temp["language"] == "Brain") {
            $temp["lan"] = "Brainf**k";
        } else {
            $temp["lan"] = $temp["language"];
        }
        $t = mysqli_query($link, "SELECT teamname FROM teams WHERE tid=$temp[tid] and (status='Normal' or status='Admin')");
        if (mysqli_num_rows($t) == 1) {
            $tResult = mysqli_fetch_array($t);
            $teamname = $tResult['teamname'];
        } else {
            continue;
        }
        $t = mysqli_query($link, "SELECT name FROM problems WHERE pid=$temp[pid] and status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ";");
        if (mysqli_num_rows($t) == 1) {
            $tResult = mysqli_fetch_array($t);
            $probname = $tResult['name'];
        } else {
            continue;
        }
        $fresult = $result = $temp["result"];
        if (isset($fullresult[$result])) {
            $fresult = $fullresult[$result];
        }

        $r = $result;
        if ($result == "") {
            $r = "NA";
            $fresult = "Queued";
        } elseif ($result == "...") {
            $r = "NA";
            $fresult = "Evaluating";
        } elseif ($result != "AC") $result = "NAC";

        if ($_SESSION["status"] == "Admin" || $_SESSION["tid"] == $temp["tid"] || $temp["access"] == "public") {
            echo "<tr class='$result'><td><a href='?display=code&rid=$temp[rid]' title='Link to Code'>$temp[rid]</a></td><td><a href='?" . str_replace("&tid=$temp[tid]", "", $urlargs) . "&tid=$temp[tid]' title='Link to Team'>$teamname</td><td><a href='?" . str_replace("&pid=$temp[pid]", "", $urlargs) . "&pid=$temp[pid]' title='Link to Problem'>$probname</a></td><td><a href='?" . str_replace("&lan=" . urlencode($temp["language"]), "", $urlargs) . "&lan=" . urlencode($temp["language"]) . "' title='Link to $temp[lan] Submissions'>$temp[lan]</a></td><td>$temp[time]</td><td class='$result'><a href='?$urlargs&res=$r'>$fresult</a></td>";
        } else {
            echo "<tr class='$result'><td>$temp[rid]</td><td><a href='?" . str_replace("&tid=$temp[tid]", "", $urlargs) . "&tid=$temp[tid]'>$teamname</td><td><a href='?" . str_replace("&pid=$temp[pid]", "", $urlargs) . "&pid=$temp[pid]'>$probname</a></td><td><a href='?" . str_replace("&lan=" . urlencode($temp["language"]), "", $urlargs) . "&lan=" . urlencode($temp["language"]) . "' title='Link to $temp[lan] Submissions'>$temp[lan]</a></td><td>$temp[time]</td><td class='$result'><a href='?$urlargs&res=$r'>$fresult</a></td>";
        }
        if ($_SESSION["status"] == "Admin") {
            echo "<td><input class='btn btn-warning' type='button' value='Rejudge' onClick=\"window.location='?action=rejudge&rid=$temp[rid]';\">";
            if ($temp["access"] == "private") {
                echo "<input class='btn btn-secondary' type='button' value='Private' title='Make this code Public (visible to all).' onClick=\"window.location='?action=makecodepublic&rid=$temp[rid]';\">";
            } else {
                echo "<input class='btn btn-info' type='button' value='Public' title='Make this code Private (visible only to the team that submitted it).' onClick=\"window.location='?action=makecodeprivate&rid=$temp[rid]';\">";
            }
            echo "<input class='btn btn-danger' type='button' value='Delete' onClick=\"if(confirm('Are you sure you wish to delete Run ID $temp[rid]?'))window.location='?action=makecodedeleted&rid=$temp[rid]';\">";
            echo "</td>";
        } else if ($_SESSION["status"] == "Admin" || $_SESSION["tid"] == $temp["tid"] || $temp["access"] == "public") {
            echo "<td><input type='button' value='Code' onClick=\"window.location='?display=code&rid=$temp[rid]';\"></td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";

    echo $pagenav . "</center>";
}


function display_rankings(): void
{
    global $admin, $currentmessage;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($admin["mode"] == "Lockdown" && $_SESSION["tid"] == 0) {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"] = "Access Denied : The contest is currently in Lockdown Mode. Please try again later.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }

    $group = (isset($_GET["group"]) and is_numeric($_GET["group"])) ? $_GET["group"] : 0;
    $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM teams WHERE status='Normal'" . ($group == 0 ? "" : " AND gid=$group;"));
    $totalResult = mysqli_fetch_array($totalQuery);
    $totalCount = $totalResult["total"];
    $perpage = $admin["rankpage"] ?? 25;
    $x = paginate("display=rankings&group=$group", $totalCount, $perpage);
    $page = $x[0];
    $pagenav = $x[1];
    echo "<center><h2>Current Rankings</h2>";
    echo "This page displays the current Team Scores and Ranking based on the current Teams and Problems. The information being displayed here shall be used to update the <a href='?display=scoreboard'>Main Team Scores and Rankings</a>.<br><br>";

    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<2");
    echo "Filter for Group : <select class='form-select' onChange=\"document.location='?display=rankings&group='+this.value;\">";
    echo "<option value=0 " . ($row["gid"] == $group ? "selected='selected'" : "") . ">All Groups</option>";
    while ($row = mysqli_fetch_assoc($data)) {
        echo "<option value=$row[gid] " . ($row["gid"] == $group ? "selected='selected'" : "") . ">$row[groupname]</option>";
    }
    echo "</select><br><br>";

    echo $pagenav . "<br><br>";
    echo "<table><th>Rank</th><th>Team</th>" . ($group == 0 ? "<th>Team Group</th>" : "") . "<th>Problems Solved / Attempted</th><th>Score</th></tr>";
    $data = mysqli_query($link, "SELECT * FROM teams WHERE status='Normal' " . ($group == 0 ? "" : " AND gid=$group ") . " ORDER BY score DESC, penalty ASC LIMIT " . (($page - 1) * $perpage) . "," . $perpage);

    while ($temp = mysqli_fetch_array($data)) {
        $solvedn_query = mysqli_query($link, "SELECT count(distinct runs.pid) as x FROM runs,problems WHERE runs.tid='$temp[tid]' and runs.result='AC' and runs.pid=problems.pid and problems.status='Active'");
        $solvedn_result = mysqli_fetch_array($solvedn_query);
        $solvedn = $solvedn_result["x"];

        $solveda_query = mysqli_query($link, "SELECT count(distinct runs.pid) as x FROM runs,problems WHERE runs.tid='$temp[tid]' and runs.pid=problems.pid and problems.status='Active'");
        $solveda_result = mysqli_fetch_array($solveda_query);
        $solveda = $solveda_result["x"];

        $groupname_query = mysqli_query($link, "SELECT groupname FROM groups WHERE statusx<3 AND gid=$temp[gid];");
        $groupname = "";

        if (mysqli_num_rows($groupname_query) > 0) {
            $groupname_result = mysqli_fetch_assoc($groupname_query);
            $groupname = $groupname_result["groupname"];
        }

        $rank = ($page - 1) * $perpage + 1;
        echo "<tr><td>$rank</td><td><a href='?display=submissions&tid=$temp[tid]'>$temp[teamname]</a></td>" . ($group == 0 ? "<td>$groupname</td>" : "") . "<td>$solvedn / $solveda</div></td><td>$temp[score]</td></tr>";

        $rank++;
    }
    echo "</table><br>$pagenav</center>";
}


function display_scoreboard(): void
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    echo "<center><h2>Main Scoreboard</h2>";
    echo "This page displays the Team Scores and Rank based on the results of past competitions, and do not have anything to do with the <a href='?display=rankings'>Current Team Scores and Rankings</a>.<br><br>";
    $tempQuery = mysqli_query($link, "SELECT value FROM admin WHERE variable='scoreboard'");
    if (mysqli_num_rows($tempQuery) == 1) {
        $tempResult = mysqli_fetch_array($tempQuery);
        echo $tempResult["value"];
    } else {
        echo "<table><tr><th>Rank</th><th>Team ID</th><th>Team Name</th><th>Total</th></table>";
    }
    echo "</center>";
}


?>