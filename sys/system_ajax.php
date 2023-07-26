<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
function action_adminwork(): void
{
    global $admin, $fullresult;
    $link = mysqli_connect("localhost", "root", "", "nexeum");
    $data = mysqli_query($link, "SELECT pid,score FROM problems WHERE status='Active'");
    $score = array();
    while ($temp = mysqli_fetch_array($data)) {
        $score[$temp["pid"]] = $temp["score"];
    }

    $data = mysqli_query($link, "SELECT * FROM teams");
    while ($temp = mysqli_fetch_array($data)) {
        $tid = $temp["tid"];
        $solvedn = 0;
        $score = 0;
        $penalty = 0;
        $prob = mysqli_query($link, "SELECT distinct(runs.pid) as pid,problems.score FROM runs,problems WHERE runs.tid='$tid' and runs.result='AC' and runs.pid=problems.pid and problems.status='Active' and runs.access!='deleted' ");
        $solvedn = mysqli_num_rows($prob);
        while ($temp = mysqli_fetch_array($prob)) {
            $pid = $temp["pid"];
            $score += $temp["score"];
            $sub = mysqli_query($link, "SELECT rid,submittime FROM runs WHERE result='AC' and tid=$tid and pid=$pid and access!='deleted' ORDER BY rid ASC LIMIT 0,1");
            if ($t = mysqli_fetch_array($sub)) {
                $penalty += $t["submittime"];
                $sub = mysqli_query($link, "SELECT count(*) as incorrect FROM runs WHERE result!='AC' and access!='deleted' and rid<" . $t["rid"] . " and tid=$tid and pid=$pid");
                if ($t = mysqli_fetch_array($sub)) {
                    $penalty += $t["incorrect"] * (isset($admin["penalty"]) ? ($admin["penalty"] * 60) : 0);
                }
            }
        }

        if ($penalty == 0) {
            $penalty = 2000000000;
        } // just to put someone with at least one AC submission (of 0 points) above others without it
        mysqli_query($link, "UPDATE teams SET score=$score,penalty='$penalty' WHERE tid=$tid");
    }

    $json = "
        <table class='table table-borderless'>
            <thead>
                <tr class='table-primary'>
                    <th colspan='4' class='text-center'>
                        <h4><a class='list-group-item' href='?display=rankings'>Current Rankings</a></h4>
                    </th>
                </tr>
                <tr class='table-info'>
                    <th class='text-center'>Rank</th>
                    <th class='text-center'>Team</th>
                    <th class='text-center'>Solved</th>
                    <th class='text-center'>Score</th>
                </tr>
            </thead><tbody>";
    if (isset($admin["ranklist"]) && $admin["ranklist"] >= 0) {
        $limit = $admin["ranklist"];
    } else {
        $limit = 10;
    }
    $data = mysqli_query($link, "SELECT * FROM teams WHERE status='Normal' ORDER BY score DESC, penalty ASC LIMIT 0,$limit");
    if (mysqli_num_rows($data) === 0) {
        $json .= "<tr><td colspan='4' class='text-center'>Not Available</td></tr>";
    } else {
        for ($rank = 1; $temp = mysqli_fetch_array($data); $rank++) {
            $solvednQuery = mysqli_query($link, "SELECT COUNT(DISTINCT(runs.pid)) AS solved_count FROM runs, problems WHERE runs.tid='$temp[tid]' AND runs.result='AC' AND runs.pid=problems.pid AND problems.status='Active'");
            if (mysqli_num_rows($solvednQuery) == 1) {
                $solvednResult = mysqli_fetch_array($solvednQuery);
                $solvedn = $solvednResult["solved_count"];
            } else {
                $solvedn = 0;
            }

            $json .= "<!--$temp[tid]--><tr><td>$rank</td><td><a href='?display=submissions&tid=$temp[tid]'>$temp[teamname]</td><td>$solvedn</td><td>$temp[score]</td></tr><!--$temp[tid]-->";
        }
    }
    $json .= "</tbody></table>";
    $admin["cache-rankings"] = $json;

    $json = "<table class='table table-borderless'>
        <thead>
            <tr class='table-primary'>
                <th colspan='4' class='text-center'>
                    <h4><a class='list-group-item' href='?display=submissions'>Latest Submissions</a></h4>
                </th>
            </tr>
            <tr class='table-info'>
                <th class='text-center' title='Run ID'>RID</th>
                <th class='text-center'>Team</th>
                <th class='text-center'>Problem</th>
                <th class='text-center'>Result</th>
            </tr>
        </thead>
        <tbody>";
    if (isset($admin["allsublist"]) && $admin["allsublist"] >= 0) {
        $limit = $admin["allsublist"];
    } else {
        $limit = 10;
    }
    $data = mysqli_query($link, "SELECT runs.result as result,runs.rid as rid,runs.tid as tid,runs.pid as pid,teams.teamname as teamname,problems.name as probname,problems.code as probcode FROM runs,problems,teams WHERE runs.access!='deleted' AND runs.pid = problems.pid AND runs.tid = teams.tid AND runs.access!='deleted' AND teams.status='Normal' AND problems.status='Active' ORDER BY rid DESC LIMIT 0," . $limit);
    if (mysqli_num_rows($data) === 0) {
        $json .= "<tr><td colspan='4' class='text-center'>Not Available</td></tr>";
    } else {
        while ($temp = mysqli_fetch_array($data)) {
            $result = $temp["result"];
            if (isset($fullresult[$result])) {
                $result = $fullresult[$result];
            }
            $json .= "<tr class='$temp[result]'><td>$temp[rid]</td><td><a href='?display=submissions&tid=$temp[tid]'>" . substr($temp["teamname"], 0, 100) . (strlen($temp["teamname"]) > 100 ? "..." : "") . "</td><td title=\"$temp[probname]\"><a href='?display=problem&pid=$temp[pid]'>$temp[probcode]</td><td title='$result'>$temp[result]</td></tr>";
        }
    }
    $json .= "</tbody></table>";
    $admin["cache-allsubmit"] = $json;

    $json = "";
    if (($g = mysqli_getdata("SELECT distinct pgroup FROM problems WHERE status='Active' ORDER BY pgroup")) != NULL) {
        $t = array();
        foreach ($g as $gn) {
            $t[] = $gn["pgroup"];
        }
        $g = $t;
        unset($t);
        $json .= "
            <table class='table table-borderless'>";
        if (in_array("", $g)) {
            unset($g[array_search("", $g)]);
            $g[] = "";
        }
        foreach ($g as $gn) {
            $json .= "
            <thead>
                <tr class='table-primary'>
                    <th colspan='2' class='text-center'>
                        <h4>
                            <a class='list-group-item' href='?display=problem'>Problems Index</a>
                        </h4>
                    </th>
                </tr>
                <tr class='table-info'>
                    <th class='text-center' colspan='2'>" . preg_replace("/^#[0-9]+ /", "", ($gn == "" ? "Unclassified" : $gn)) . "</th>
                </tr>
                <tr class='table-info'>
                    <th class='text-center'>
                        <i>Problem Name</i>
                    </th>
                    <th class='text-center'>
                        <i>Points</i>
                    </th>
                </tr>
            </thead>";
            $data = mysqli_query($link, "SELECT * FROM problems WHERE status='Active' AND pgroup='$gn'");
            while ($temp = mysqli_fetch_array($data)) {
                $json .= "
            <tbody>
                <tr>
                    <td class='text-center'>
                        <a class='list-group-item' href='?display=problem&pid=$temp[pid]' ";
                if ($admin["mode"] == "Active" && $_SESSION["status"] != "Admin");
                else $json .= " title=\"" . stripslashes($temp["type"]) . "\"";
                $json .= ">" . stripslashes($temp["name"]) . "
                    </a>
                    </td>
                    <td class='text-center'>$temp[score]</td>
                </tr>
            </tbody>";
            }
        }
        $json .= "
        </table>";
    }
    $admin["cache-problems"] = $json;

    action_clarcache();
}


function action_ajaxrefresh($type): bool|string
{
    global $admin, $fullresult, $ajaxlogout;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($admin["mode"] == "Active" && isset($admin["endtime"])) {
        $json["ajax-contest-time"] = ($admin["endtime"] - time());
    } else {
        $json["ajax-contest-time"] = -1;
    }
    $json["ajax-contest-status"] = $admin["mode"];
    if ($admin["lastjudge"] >= time() - 30) {
        $json["ajax-contest-judgement"] = "<a title='The Execution Protocol is active. Submissions will be judged as soon as they are the next in queue.'>Ongoing</a>";
    } else {
        $json["ajax-contest-judgement"] = "<a title='The Execution Protocol is currently not active. Submissions will be judged once it is initiated.'>Waiting</a>";
    }


    if ($_SESSION["tid"] == 0) {
        $ip = $_SERVER["REMOTE_ADDR"];
        $t = mysqli_query($link, "SELECT tid FROM teams WHERE (ip1='$ip' or ip2='$ip' or ip3='$ip')");
        $json["ajax-mysubmit"] = "<a class='list-group-item' href='?display=register'>New Team? Click here to Register.</a>";
    } else if ($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") {
        $json["ajax-mysubmit"] = "<h3>My Submissions</h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-mysubmit"] = "<table class='table table-borderless'><thead><tr class='table-primary'><th colspan='4'><h4><a class='list-group-item' href='?display=submissions&tid=$_SESSION[tid]'>My Submissions</a></h4></th></tr><tr class='table-info'><th title='Run ID'>RID</th><th>Problem</th><th>Language</th><th>Result</th></tr></thead>";
        if (isset($admin["mysublist"]) && $admin["mysublist"] >= 0) {
            $limit = $admin["mysublist"];
        } else {
            $limit = 5;
        }
        $data = mysqli_query($link, "SELECT * FROM problems,runs WHERE runs.tid='$_SESSION[tid]' AND problems.pid=runs.pid AND runs.access!='deleted' AND problems.status='Active' ORDER BY rid DESC LIMIT 0," . $limit);
        while ($temp = mysqli_fetch_array($data)) {
            if ($_SESSION["status"] == "Admin") {
                $t = mysqli_query($link, "SELECT name,code FROM problems WHERE pid=$temp[pid]");
            } else {
                $t = mysqli_query($link, "SELECT name,code FROM problems WHERE pid=$temp[pid] and status='Active'");
            }
            if (mysqli_num_rows($t) == 1) {
                $row = mysqli_fetch_array($t);
                $probname = $row['name'];
                $probcode = $row['code'];
            } else {
                continue;
            }
            $result = $temp["result"];
            if (isset($fullresult[$result])) {
                $result = $fullresult[$result];
            }

            // Assign Bootstrap classes based on result
            $rowClass = '';
            if ($temp["result"] == "AC") {
                $rowClass = 'table-success'; // Green row for AC
            } elseif ($temp["result"] == "WA") {
                $rowClass = 'table-danger'; // Red row for WA
            } elseif ($temp["result"] == "TLE") {
                $rowClass = 'table-secondary'; // Light blue row for Time Limit Exceeded
            }  elseif ($temp["result"] == "CE") {
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

            $json["ajax-mysubmit"] .= "<tr class='$rowClass'><td><a class='list-group-item' href='?display=code&rid=$temp[rid]' title='Link to Code'>$temp[rid]</a></td><td title=\"Link to Problem : $probname\"><a class='list-group-item' href='?display=problem&pid=$temp[pid]'>$probcode</td><td>$temp[language]</td><td title='$result'>$temp[result]</td></tr>";
        }
        $json["ajax-mysubmit"] .= "</table>";
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-rankings"])) {
        $json["ajax-rankings"] = "<table class='table table-borderless'><tr><h4>Current Rankings</h4></tr><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-rankings"] = $admin["cache-rankings"];
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-allsubmit"])) {
        $json["ajax-allsubmit"] = "<table class='table table-borderless'><tr><h4>All Submissions</h4></tr><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-allsubmit"] = $admin["cache-allsubmit"];
    }

    if (!isset($admin["cache-problems"]) || $admin["cache-problems"] == "") {
        $admin["cache-problems"] = "<table class='table table-borderless'><tr><h4>Problems Index</h4></tr><tr><td>Not Available</td></tr></table>";
    }

    if ($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") {
        $json["ajax-problem"] = "<table class='table table-borderless'><tr><h4>Problems Index</h4></tr><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-problem"] = $admin["cache-problems"];
    }

    if ($_SESSION["tid"] != 0) {

        $tid = $_SESSION["tid"];
        $t = mysqli_query($link, "SELECT teamname,score FROM teams WHERE tid=$tid");
        $row = mysqli_fetch_array($t);
        $solvednQuery = mysqli_query($link, "SELECT count(distinct(runs.pid)) as n FROM runs,problems WHERE runs.tid='$tid' and runs.result='AC' and runs.pid=problems.pid and problems.status='Active'");
        if (mysqli_num_rows($solvednQuery) == 1) {
            $solvednResult = mysqli_fetch_array($solvednQuery);
            $solvedn = $solvednResult["n"];
        } else {
            $solvedn = 0;
        }
        $json["ajax-account"] = "
            <table class='table table-borderless'>
                <tbody>
                <tr><td colspan='2' class='table-primary'><h4>Account</h4></td></tr>
                <tr><td class='table-info'>Team</td><td><a class='list-group-item' href='?display=submissions&tid=$tid'>$row[teamname]</a></td></tr>
                <tr><td class='table-info'>Score</td><td>$row[score]</td></tr>
                <tr><td class='table-info'>Solved</td><td>$solvedn</td></tr>
                <tr><td colspan='2'><a class='btn btn-outline-danger' href='?action=logout'>Logout</a></td></tr>
                </tbody></table>";
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-clarlatest"])) {
        $json["ajax-publicclar"] = "<table class='table table-borderless'><thead><tr class='table-primary'><th><h4><a href='?display=clarifications' title='Link to Clarifications Page'>Public Clarifications</a></h4></th></tr></thead><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-publicclar"] = $admin["cache-clarlatest"];
    }

    if ($_SESSION["tid"] == 0) {
        $json["ajax-privateclar"] = "
            <table class='table table-borderless'>
            <thead>
                <tr class='table-primary'>
                    <th>
                        <h4><a class='list-group-item' href='?display=clarifications'>Private Clarifications</a></h4>
                    </th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>Not Available</td>
            </tr>
            </tbody>
            </table>";
    } else {
        if (isset($admin["clarprivate"]) && $admin["clarprivate"] >= 0) {
            $limit = $admin["clarprivate"];
        } else {
            $limit = 2;
        }
        if (($d = mysqli_getdata("SELECT * FROM clar WHERE tid=$_SESSION[tid] and access='Private' ORDER BY time DESC LIMIT 0,$limit")) != NULL) {
            $json["ajax-privateclar"] = "<table class='table table-borderless'><thead><tr class='table-primary'><th><h4><a class='list-group-item' href='?display=clarifications' title='Link to Clarifications Page'>My Clarifications</a></h4></th></tr></thead>";
            if (count($d) == 0) {
                $json["ajax-privateclar"] .= "<tr><td>Not Available</td></tr>";
            } else {
                foreach ($d as $c) {
                    if ($c["pid"] == 0) $probname = "General";
                    else {
                        $probname = mysqli_getdata("SELECT name FROM problems WHERE status='Active' AND pid=$c[pid]");
                        $probname = "<a href='?display=problem&pid=$c[pid]'>" . $probname[0]["name"] . "</a>";
                    }
                    $json["ajax-privateclar"] .= "<tr><td><b><a href='?display=submissions&tid=$_SESSION[tid]'>$_SESSION[teamname]</a> ($probname)</b> : $c[query]</td></tr>";
                    if (!empty($c["reply"])) {
                        $json["ajax-privateclar"] .= "<tr><td><i><b>Judge's Response</b> : $c[reply]</i></td></tr>";
                    }
                }
            }
            $json["ajax-privateclar"] .= "</table>";
        } else {
            $json["ajax-privateclar"] = "<table class='table table-borderless'><thead><tr class='table-primary'><th><h4><a class='list-group-item' href='?display=clarifications' title='Link to Clarifications Page'>My Clarifications</a></h4></th></tr></thead><tbody><tr><td>Not Available</td></tr></tbody></table>";
        }
    }

    $json["refresh"] = 0;
    if ($_SESSION["tid"] != 0) {
        $temp = mysqli_query($link, "SELECT status FROM teams WHERE tid=$_SESSION[tid]");
        if (mysqli_num_rows($temp) == 1) {
            $temp = mysqli_fetch_array($temp);
            if ($temp["status"] != "Normal" && $temp["status"] != "Admin") {
                if (isset($_GET["action"]) && $_GET["action"] == "ajaxrefresh") {
                    action_logout();
                }
                unset($_SESSION["message"][count($_SESSION["message"]) - 1]);
                $_SESSION["message"][] = "Access Denied : You have been logged out as your account is no longer Active.";
                $json["refresh"] = 1;
            }
        }
    }
    if ($type == 0 && $admin["ajaxrr"] == 0) {
        $json["refresh"] = 1;
    }
    if ($ajaxlogout == 1) {
        $json["refresh"] = 1;
    }

    $json["newclar"] = "";
    $data = mysqli_query($link, "SELECT * FROM clar WHERE (access='Public' or tid=" . $_SESSION["tid"] . ") and access!='Delete' and time>" . $_SESSION["time"]);
    if (mysqli_num_rows($data)) {
        $json["newclar"] = array();
        while ($temp = mysqli_fetch_array($data)) {
            if ($temp["pid"] == 0) {
                $prob = array("name" => "General");
            } else {
                $prob = mysqli_fetch_array(mysqli_query($link, "SELECT name FROM problems WHERE pid=" . $temp["pid"]));
            }
            $team = mysqli_fetch_array(mysqli_query($link, "SELECT teamname FROM teams WHERE tid=" . $temp["tid"]));
            $json["newclar"][] = ($team["teamname"] . " (" . $prob["name"] . ") : " . unfilter($temp["query"]) . (!empty($temp["reply"]) ? "\nJudge`s Response : " . unfilter($temp["reply"]) : ""));
        }
        $json["newclar"] = "Latest Clarification(s)\n\n" . implode("\n\n", $json["newclar"]);
    }

    $json["newclar2"] = "";
    if ($_SESSION["status"] == "Admin") {
        $data = mysqli_query($link, "SELECT * FROM clar WHERE reply='' and time>" . $_SESSION["time"]);
        if (mysqli_num_rows($data)) {
            $json["newclar2"] = array();
            while ($temp = mysqli_fetch_array($data)) {
                if ($temp["pid"] == 0) {
                    $prob = array("name" => "General");
                } else {
                    $prob = mysqli_fetch_array(mysqli_query($link, "SELECT name FROM problems WHERE pid=" . $temp["pid"]));
                }
                $team = mysqli_fetch_array(mysqli_query($link, "SELECT teamname FROM teams WHERE tid=" . $temp["tid"]));
                $json["newclar2"][] = ($team["teamname"] . " (" . $prob["name"] . ") : " . unfilter($temp["query"]));
            }
            $json["newclar2"] = "Clarification Request(s)\n\n" . implode("\n\n", $json["newclar2"]);
        }
    }

    $_SESSION["time"] = time();
    return json_encode($json);
}
