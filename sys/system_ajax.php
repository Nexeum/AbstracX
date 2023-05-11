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

    $json = "<h3><a href='?display=rankings'>Current Rankings</a></h3>";
    $json .= "<table><th>Rank</th><th>Team</th><th>Solved</th><th>Score</th></tr>";
    if (isset($admin["ranklist"]) && $admin["ranklist"] >= 0) {
        $limit = $admin["ranklist"];
    } else {
        $limit = 10;
    }
    $data = mysqli_query($link, "SELECT * FROM teams WHERE status='Normal' ORDER BY score DESC, penalty ASC LIMIT 0,$limit");
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
    $json .= "</table>";
    $admin["cache-rankings"] = $json;

    $json = "<h3><a href='?display=submissions'>Latest Submissions</a></h3>";
    $json .= "<table><th title='Run ID'>RID</th><th>Team</th><th>Problem</th><th>Result</th></tr>";
    if (isset($admin["allsublist"]) && $admin["allsublist"] >= 0) {
        $limit = $admin["allsublist"];
    } else {
        $limit = 10;
    }
    $data = mysqli_query($link, "SELECT runs.result as result,runs.rid as rid,runs.tid as tid,runs.pid as pid,teams.teamname as teamname,problems.name as probname,problems.code as probcode FROM runs,problems,teams WHERE runs.access!='deleted' AND runs.pid = problems.pid AND runs.tid = teams.tid AND runs.access!='deleted' AND teams.status='Normal' AND problems.status='Active' ORDER BY rid DESC LIMIT 0," . $limit);
    while ($temp = mysqli_fetch_array($data)) {
        $result = $temp["result"];
        if (isset($fullresult[$result])) {
            $result = $fullresult[$result];
        }
        $json .= "<tr class='$temp[result]'><td>$temp[rid]</td><td><a href='?display=submissions&tid=$temp[tid]'>" . substr($temp["teamname"], 0, 100) . (strlen($temp["teamname"]) > 100 ? "..." : "") . "</td><td title=\"$temp[probname]\"><a href='?display=problem&pid=$temp[pid]'>$temp[probcode]</td><td title='$result'>$temp[result]</td></tr>";
    }
    $json .= "</table>";
    $admin["cache-allsubmit"] = $json;

    $json = "";
    if (($g = mysqli_getdata("SELECT distinct pgroup FROM problems WHERE status='Active' ORDER BY pgroup")) != NULL) {
        $t = array();
        foreach ($g as $gn) {
            $t[] = $gn["pgroup"];
        }
        $g = $t;
        unset($t);
        $json .= "<h3><a href='?display=problem'>Problems Index</a></h3><div class='problist'><table>";
        if (in_array("", $g)) {
            unset($g[array_search("", $g)]);
            $g[] = "";
        }
        foreach ($g as $gn) {
            $json .= "<tr><th colspan=2>" . preg_replace("/^#[0-9]+ /", "", ($gn == "" ? "Unclassified" : $gn)) . "</th></tr><tr class='AC'><td><i>Problem Name</i></td><td><i>Points</i></td></tr>";
            $data = mysqli_query($link, "SELECT * FROM problems WHERE status='Active' AND pgroup='$gn'");
            while ($temp = mysqli_fetch_array($data)) {
                $json .= "<tr><td><a href='?display=problem&pid=$temp[pid]' ";
                if ($admin["mode"] == "Active" && $_SESSION["status"] != "Admin") ; else $json .= " title=\"" . stripslashes($temp["type"]) . "\"";
                $json .= ">" . stripslashes($temp["name"]) . "</a></td><td>$temp[score]</td></tr>";
            }
        }
        $json .= "</table></div>";
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
        $json["ajax-mysubmit"] = "<div style='text-align: center;'><a href='?display=register'>New Team? Click here to Register.</a></div>";
    } else if ($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") {
        $json["ajax-mysubmit"] = "<h3>My Submissions</h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-mysubmit"] = "<h3><a href='?display=submissions&tid=$_SESSION[tid]'>My Submissions</a></h3>";
        $json["ajax-mysubmit"] .= "<table><th title='Run ID'>RID</th><th>Problem</th><th>Language</th><th>Result</th></tr>";
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
            if ($temp["language"] == "Brain") {
                $temp["language"] = "Brainf**k";
            }
            $json["ajax-mysubmit"] .= "<tr class='$temp[result]'><td><a href='?display=code&rid=$temp[rid]' title='Link to Code'>$temp[rid]</a></td><td title=\"Link to Problem : $probname\"><a href='?display=problem&pid=$temp[pid]'>$probcode</td><td>$temp[language]</td><td title='$result'>$temp[result]</td></tr>";
        }
        $json["ajax-mysubmit"] .= "</table>";
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-rankings"])) {
        $json["ajax-rankings"] = "<h3>Current Rankings</h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-rankings"] = $admin["cache-rankings"];
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-allsubmit"])) {
        $json["ajax-allsubmit"] = "<h3>All Submissions</h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-allsubmit"] = $admin["cache-allsubmit"];
    }

    if (!isset($admin["cache-problems"]) || $admin["cache-problems"] == "") {
        $admin["cache-problems"] = "<h3>Problems Index</h3><table><tr><td>Not Available</td></tr></table>";
    }

    if ($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") {
        $json["ajax-problem"] = "<h3>Problems Index</h3><table><tr><td>Not Available</td></tr></table>";
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
        $json["ajax-account"] = "<h3>Team Name : <a href='?display=submissions&tid=$tid'>$row[teamname]</a></h3>";
        $json["ajax-account"] .= "<table><tr><th>Score</th><th>Solved</th><th><a href='?display=account'>Account</a></th></tr>";
        $json["ajax-account"] .= "<tr><td>$row[score]</td><td>$solvedn</td><td><a href='?action=logout'>Logout</a></td></table>";
    }

    if (($admin["mode"] == "Lockdown" && $_SESSION["status"] != "Admin") || !isset($admin["cache-clarlatest"])) {
        $json["ajax-publicclar"] = "<h3><a href='?display=clarifications' title='Link to Clarifications Page'>Public Clarifications</a></h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        $json["ajax-publicclar"] = "<h3><a href='?display=clarifications' title='Link to Clarifications Page'>Public Clarifications</a></h3>" . $admin["cache-clarlatest"];
    }

    if ($_SESSION["tid"] == 0) {
        $json["ajax-privateclar"] = "<h3><a href='?display=clarifications'>Private Clarifications</a></h3><table><tr><td>Not Available</td></tr></table>";
    } else {
        if (isset($admin["clarprivate"]) && $admin["clarprivate"] >= 0) {
            $limit = $admin["clarprivate"];
        } else {
            $limit = 2;
        }
        $json["ajax-privateclar"] = "<h3><a href='?display=clarifications' title='Link to Clarifications Page'>My Clarifications</a></h3>";
        if (($d = mysqli_getdata("SELECT * FROM clar WHERE tid=$_SESSION[tid] and access='Private' ORDER BY time DESC LIMIT 0,$limit")) != NULL) {
            $json["ajax-privateclar"] .= "<table>";
            if (count($d) == 0) {
                $json["ajax-privateclar"] .= "<tr><td>Not Available</td></tr>";
            } else {
                foreach ($d as $c) {
                    if ($c["pid"] == 0) $probname = "General"; else {
                        $probname = mysqli_getdata("SELECT name FROM problems WHERE status='Active' AND pid=$c[pid]");
                        $probname = "<a href='?display=problem&pid=$c[pid]'>" . $probname[0]["name"] . "</a>";
                    }
                    $json["ajax-privateclar"] .= "<tr><td style='text-align:left;'><b><a href='?display=submissions&tid=$_SESSION[tid]'>$_SESSION[teamname]</a> ($probname)</b> : $c[query]</td></tr>";
                    if (!empty($c["reply"])) {
                        $json["ajax-privateclar"] .= "<tr><td style='text-align:left;'><i><b>Judge's Response</b> : $c[reply]</i></td></tr>";
                    }
                }
            }
            $json["ajax-privateclar"] .= "</table>";
        } else {
            $json["ajax-privateclar"] .= "<table><tr><td>Not Available</td></tr></table>";
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

?>