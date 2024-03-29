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
            $filters[] = "<a class='list-group-item' href='?" . str_replace("&tid=$filter[tid]", "", $urlargs) . "'>$teamdata[teamname]</a>";
            $rejudge .= "&tid=$filter[tid]";
        }
    }
    if (!empty($_GET["pid"]) && is_numeric($_GET["pid"])) {
        $t = mysqli_query($link, "SELECT * FROM problems WHERE pid=" . $_GET["pid"] . " and status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ";");
        if (mysqli_num_rows($t)) {
            $filter["pid"] = $_GET["pid"];
            $probdata = mysqli_fetch_array($t);
            $filters[] = "<a class='list-group-item' href='?" . str_replace("&pid=$filter[pid]", "", $urlargs) . "'>$probdata[name]</a>";
            $rejudge .= "&pid=$filter[pid]";
        }
    }
    if (!empty($_GET["lan"]) && key_exists($_GET["lan"], $extension)) {
        $filter["language"] = $_GET["lan"];
        $filters[] = "<a class='list-group-item' href='?" . str_replace("&lan=" . urlencode($filter["language"]), "", $urlargs) . "'>" . ($filter["language"]) . "</a>";
        $rejudge .= "&lan=" . urlencode($_GET["lan"]);
    }
    if (!empty($_GET["res"]) && key_exists($_GET["res"], $fullresult)) {
        $filter["result"] = $_GET["res"];
        $filters[] = "<a class='list-group-item' href='?" . str_replace("&res=$filter[result]", "", $urlargs) . "'>" . $fullresult[$filter["result"]] . "</a>";
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
            $filters[] = "<a class='list-group-item' href='?" . str_replace("&pgr=" . urlencode($filter["pgroup"]), "", $urlargs) . "'>" . filter(preg_replace('/^#[0-9]+ /', '', $filter["pgroup"])) . "</a>";
            $rejudge .= "&pgr=$filter[pgroup]";
            $condition .= " AND pid in (SELECT pid FROM problems WHERE pgroup='$filter[pgroup]')";
        }
    }

    if (isset($filter["tid"])) {
        echo "<a onClick=\"$('#team-information').slideToggle();$('#problem-information').slideUp();$('#submission-statistics').slideUp();\" title='Click here to show/hide team information.'>$teamdata[teamname] : Team Information</a>";
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
        echo "<table><tr><th>Team Members</th><td>$members</td></tr><tr><th>Score</th><td>$teamdata[score]</td></tr><tr><th>Problems Solved</th><td>$solvedp ($solvedn)</td></tr>";
        echo "</table></div>";
    }

    $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition ORDER BY rid DESC");
    $totalResult = mysqli_fetch_array($totalQuery);
    $totalCount = $totalResult["total"];

    if (isset($admin["substatpage"]) && $admin["substatpage"] >= 0) {
        $perpage = $admin["substatpage"];
    } else {
        $perpage = 5;
    }
    $x = paginate($urlargs, $totalCount, $perpage);
    $page = $x[0];
    echo "<div class='mb-3'>
        <table class='table table-borderless'>";

            if (count($filter)) {
                echo "<caption>Active Filter(s) : " . implode(" , ", $filters) . " (Click to Remove) </caption>";
            } else {
                echo "<caption>Active Filter(s) : None </caption>";
            }
        
            echo
            " <thead>
                <tr class='table-primary'>
                    <th colspan='7'>
                        <h3>Submission Status</h3>
                    </th>
                </tr>
                <tr class='table-info'>
                    <th>Run ID</th>
                    <th>Team</th>
                    <th>Problem</th>
                    <th>Language</th>
                    <th>Time</th>
                    <th>Result</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>";
    $data = mysqli_query($link, "SELECT * FROM runs WHERE access!='deleted' AND tid in (SELECT tid FROM teams WHERE status='Normal' OR status='Admin') AND pid in (SELECT pid FROM problems WHERE status" . (($_SESSION["status"] == "Admin") ? "!='Delete'" : "='Active'") . ") $condition ORDER BY rid DESC LIMIT " . (($page - 1) * $perpage) . "," . $perpage);
    for ($i = 0; $temp = mysqli_fetch_array($data); $i++) {
        if ($i == $perpage) {
            break;
        }
        $temp["lan"] = $temp["language"];
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

        // Assign Bootstrap classes based on result
        $rowClass = '';
        if ($temp["result"] == "AC") {
            $rowClass = 'table-success'; // Green row for AC
        } elseif ($temp["result"] == "WA") {
            $rowClass = 'table-danger'; // Red row for WA
        } elseif ($temp["result"] == "TLE") {
            $rowClass = 'table-secondary'; // Light blue row for Time Limit Exceeded
        } elseif ($temp["result"] == "CE") {
            $rowClass = 'table-warning'; // Yellow row for CE
        } elseif ($temp["result"] == "RTE") {
            $rowClass = 'table-warning'; // Yellow row for RE
        } elseif ($temp["result"] == "PE") {
            $rowClass = 'table-warning'; // Yellow blue row for PE
        } elseif ($temp["result"] == "SC") {
            $rowClass = 'table-danger'; // Red row for Suspicious code
        } else {
            $rowClass = 'table-secondary'; // Gray row for other results
        }

        if ($_SESSION["status"] == "Admin" || $_SESSION["tid"] == $temp["tid"] || $temp["access"] == "public") {
            echo "<tr class='$rowClass'>
                    <td><a class='list-group-item' href='?display=code&rid=$temp[rid]' title='Link to Code'>$temp[rid]</a></td>
                    <td><a class='list-group-item' href='?" . str_replace("&tid=$temp[tid]", "", $urlargs) . "&tid=$temp[tid]' title='Link to Team'>$teamname</a></td>
                    <td><a class='list-group-item' href='?" . str_replace("&pid=$temp[pid]", "", $urlargs) . "&pid=$temp[pid]' title='Link to Problem'>$probname</a></td>
                    <td><a class='list-group-item' href='?" . str_replace("&lan=" . urlencode($temp["language"]), "", $urlargs) . "&lan=" . urlencode($temp["language"]) . "' title='Link to $temp[lan] Submissions'>$temp[lan]</a></td>
                    <td>$temp[time]</td>
                    <td class='$result'><a class='list-group-item' href='?$urlargs&res=$r'>$fresult</a></td>";
        } else {
            echo "<tr class='$rowClass'>
                    <td>$temp[rid]</td>
                    <td><a class='list-group-item' href='?" . str_replace("&tid=$temp[tid]", "", $urlargs) . "&tid=$temp[tid]'>$teamname</a></td>
                    <td><a class='list-group-item' href='?" . str_replace("&pid=$temp[pid]", "", $urlargs) . "&pid=$temp[pid]'>$probname</a></td>
                    <td><a class='list-group-item' href='?" . str_replace("&lan=" . urlencode($temp["language"]), "", $urlargs) . "&lan=" . urlencode($temp["language"]) . "' title='Link to $temp[lan] Submissions'>$temp[lan]</a></td>
                    <td>$temp[time]</td>
                    <td class='$result'><a class='list-group-item' href='?$urlargs&res=$r'>$fresult</a></td>";
        }

        if ($_SESSION["status"] == "Admin") {
            echo "<td>
                    <button class='btn btn-outline-warning mx-1' onClick=\"window.location='?action=rejudge&rid=$temp[rid]';\">Rejudge</button>";
            if ($temp["access"] == "private") {
                echo "<button class='btn btn-outline-secondary mx-1' title='Make this code Public (visible to all).' onClick=\"window.location='?action=makecodepublic&rid=$temp[rid]';\">Private</button>";
            } else {
                echo "<button class='btn btn-outline-info mx-1' title='Make this code Private (visible only to the team that submitted it).' onClick=\"window.location='?action=makecodeprivate&rid=$temp[rid]';\">Public</button>";
            }
            echo "<button class='btn btn-outline-danger mx-1' onClick=\"if(confirm('Are you sure you wish to delete Run ID $temp[rid]?'))window.location='?action=makecodedeleted&rid=$temp[rid]';\">Delete</button>
                </td>";
        } else if ($_SESSION["status"] == "Admin" || $_SESSION["tid"] == $temp["tid"] || $temp["access"] == "public") {
            echo "<td>
                    <button class='btn btn-info mx-1' onClick=\"window.location='?display=code&rid=$temp[rid]';\">Code</button>
                </td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table></div>";
    $totalpages = max(1, ceil($totalCount / $perpage));
    $currentPage = $x[0];

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
            echo "<table class='table table-borderless'><tr class='table-primary'><th colspan='10'><h3>Status overview</h3></th></tr>
            <tr class='table-info'><th>Total Submissions</th>";
            foreach ($fullresult as $key => $value) {
                echo "<th><a class='list-group-item' href='?" . ($urlargs) . "&res=$key'>" . ($value) . "</a></th>";
            }
            echo "<th>Unjudged Submissions</th></tr><tr><td>" . $info["TOT"] . "</td>";
            foreach ($fullresult as $key => $value) {
                echo "<td>" . $info[$key] . "</td>";
            }
            echo "<td>" . $info["..."] . "</td></tr></table>";
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
            echo "<table class='table table-borderless'><tr class='table-primary'><th colspan='6'><h3>Languages overview</h3></th></tr><tr class='table-info'>";
            foreach ($extension as $key => $value) {
                echo "<th><a class='list-group-item' href='?" . ($urlargs) . "&lan=" . urlencode($key) . "'>" . ($key) . "</a></th>";
            }
            echo "</tr><tr>";
            foreach ($extension as $key => $value) {
                echo "<td>" . $info[$key] . "</td>";
            }
            echo "</tr></table>";
        }
    }
    if (!isset($filter["result"]) || !isset($filter["language"])) {
        echo "</div>";
    }

    if (isset($filter["pid"])) {
        echo "<div id='problem-information' style='display:none;'>
            <table class='table table-borderless'>
            <tr class='table-primary'>
                <th colspan='6'>
                    <h3>Information</h3>
                </th>
            </tr>
			<tr>
                <th class='table-info'>Problem ID</th>
                <td>$probdata[pid]</td>
                <th class='table-info'>Problem Type</th>
                <td>$probdata[type]</td>
                <th class='table-info'>Time Limit</th>
                <td>$probdata[timelimit] sec</td>
            </tr>
			<tr>
                <th class='table-info'>Problem Code</th>
                <td>$probdata[code]</td>
                <th class='table-info'>Input File Size</th>
                <td>" . display_filesize(strlen($probdata["input"])) . "</td>
                <th class='table-info'>Score</th>
                <td>$probdata[score]</td>
            </tr>";
        echo "</table></div>";
    }

    if (isset($filter["pid"])) {
        echo "<div class='mb-3'><button class=\"btn btn-outline-primary mx-1\" onClick=\"$('#problem-information').slideToggle();$('#team-information').slideUp();$('#submission-statistics').slideUp();\" title='Click here to show/hide problem information.'>$probdata[name] : Problem Information</button></div>";
    }
    echo "<div class='d-flex mb-3 justify-content-center'>";
    if ($_SESSION["status"] == "Admin") {
        if ($rejudge == "action=rejudge") {
            $rejudge .= "&all=1";
        }
        echo "<button class='btn btn-outline-warning mx-1' onClick=\"if(confirm('Are you sure you wish to rejudge all currently selected submissions?'))window.location='?$rejudge';\">Rejudge Submissions</button>";    }
    if (!isset($filter["result"]) || !isset($filter["language"])) {
        echo "<button class='btn btn-outline-success mx-1' onClick=\"$('#submission-statistics').slideToggle();$('#problem-information').slideUp();$('#team-information').slideUp();\" title='Click here to show/hide submission statistics.'>Statistics</button>";
    }
    echo "</div>";
    echo "
        <div class='mb-3 d-flex justify-content-center'>
            <nav aria-label='Page navigation example'>
                <ul class='pagination'>
                    <li class='page-item" . ($currentPage == 1 ? " disabled" : "") . "'>
                        <a class='page-link' href='?$urlargs&" . ($currentPage == 1 ? "" : "page=" . ($currentPage - 1)) . "' aria-label='Previous'>
                        <span aria-hidden='true'>&laquo;</span>
                        </a>
                    </li>";

    for ($page = max(1, $currentPage - 2); $page <= min($currentPage + 2, $totalpages); $page++) {
        echo "<li class='page-item" . ($currentPage == $page ? " active" : "") . "'><a class='page-link' href='?$urlargs&page=$page'>$page</a></li>";
    }

    echo "
                    <li class='page-item" . ($currentPage == $totalpages ? " disabled" : "") . "'>
                        <a class='page-link' href='?$urlargs&" . ($currentPage == $totalpages ? "" : "page=" . ($currentPage + 1)) . "' aria-label='Next'>
                        <span aria-hidden='true'>&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>";
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
    echo "<h3>Current Rankings</h3>";
    echo "This page displays the current Team Scores and Ranking based on the current Teams and Problems. The information being displayed here shall be used to update the <a class='list-group-item' href='?display=scoreboard'>Main Team Scores and Rankings</a>";

    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<2");
    echo "<div class='mb-3'> Filter for Group : <select class='form-select' onChange=\"document.location='?display=rankings&group='+this.value;\">";
    echo "<option selected>All Groups</option>";
    while ($row = mysqli_fetch_assoc($data)) {
        echo "<option value=$row[gid] " . ($row["gid"] == $group ? "selected='selected'" : "") . ">$row[groupname]</option>";
    }
    echo "</select></div>";

    echo "<div clas='mb-3'>
    <table class='table table-borderless'>
    <thead>
    <tr class='table-primary'>
        <th>Rank</th>
        <th>Team</th>" . ($group == 0 ? "<th>Team Group</th>" : "") . "
        <th>Problems Solved / Attempted</th>
        <th>Score</th>
    </tr>
    </thead>
    <tbody>";
    $data = mysqli_query($link, "SELECT * FROM teams WHERE status='Normal' " . ($group == 0 ? "" : " AND gid=$group ") . " ORDER BY score DESC, penalty ASC LIMIT " . (($page - 1) * $perpage) . "," . $perpage);
    if(mysqli_num_rows($data) > 0){
        $rank = ($page - 1) * $perpage + 1;
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
    
            echo "
            <tr>
                <td>$rank</td>
                <td>
                    <a class='list-group-item' href='?display=submissions&tid=$temp[tid]'>$temp[teamname]</a>
                </td>" . ($group == 0 ? "<td>$groupname</td>" : "") . "
                <td>$solvedn / $solveda</div>
                </td><td>$temp[score]</td>
                </tr>";
    
            $rank++;
        }
    }else{
        echo "<tr><th colspan='4'>Not Available</th></tr>";
    }

    echo "</tbody></table></div>";
    $urlargs = "display=rankings";
    $totalpages = max(1, ceil($totalCount / $perpage));
    $currentPage = $x[0];
    echo "
    <div class='d-flex justify-content-center'>
        <nav aria-label='Page navigation example'>
            <ul class='pagination'>
                <li class='page-item" . ($currentPage == 1 ? " disabled" : "") . "'>
                    <a class='page-link' href='?$urlargs&" . ($currentPage == 1 ? "" : "group=" . ($currentPage - 1)) . "' aria-label='Previous'>
                    <span aria-hidden='true'>&laquo;</span>
                    </a>
                </li>";

    for ($page = max(1, $currentPage - 2); $page <= min($currentPage + 2, $totalpages); $page++) {
        echo "<li class='page-item" . ($currentPage == $page ? " active" : "") . "'><a class='page-link' href='?$urlargs&group=$page'>$page</a></li>";
    }

    echo "
                <li class='page-item" . ($currentPage == $totalpages ? " disabled" : "") . "'>
                    <a class='page-link' href='?$urlargs&" . ($currentPage == $totalpages ? "" : "group=" . ($currentPage + 1)) . "' aria-label='Next'>
                    <span aria-hidden='true'>&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>";
}


function display_scoreboard(): void
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    $tempQuery = mysqli_query($link, "SELECT value FROM admin WHERE variable='scoreboard'");
    if (mysqli_num_rows($tempQuery) == 1) {
        $tempResult = mysqli_fetch_array($tempQuery);
        echo $tempResult["value"];
    } else {
        echo "<div class='mb-3'><table class='table table-borderless'><thead><tr><td colspan='4' class='table-primary'><h3>Main Scoreboard</h3></td></tr><tr class='table-info'><th>Rank</th><th>Team ID</th><th>Team Name</th><th>Total</th></tr></thead><tbody><tr><th colspan='4'>Not Available</th></tr></tbody></table></div>";
    }
    echo "<div class='mb-3'>This page displays the Team Scores and Rank based on the results of past competitions, and do not have anything to do with the <a class='list-group-item' href='?display=rankings'>Current Team Scores and Rankings</a></div>";
}
