<?php

function action_commitdata(): void
{
    global $currentmessage, $mysql_database;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform that operation.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }

    if (empty($_POST["recordname"])) {
        $_SESSION["message"][] = "Data Commitment Error : Insufficient Data";
        return;
    }
    $tablename = "backup_" . time();
    $temp = mysqli_query($link,"SHOW TABLES LIKE '$tablename';");
    if (mysqli_num_rows($temp) > 0) {
        $_SESSION["message"][] = "Data Commitment Error : A table with that name already exists.";
        return;
    }

    action_adminwork();

    $problems = mysqli_query($link,"SELECT * FROM problems WHERE status='Active'");
    $_SESSION["message"][] = "Data Commitment Error : Failure 001.";
    return;
    $nprob = mysqli_num_rows($problems);
    $teams = mysqli_query($link,"SELECT * FROM teams WHERE status='Normal'");
    $_SESSION["message"][] = "Data Commitment Error : Failure 002.";
    return;

    $columns = "info,id,name,score";
    for ($i = 1; $i <= $nprob; $i++) {
        for ($j = 1; $j <= 6; $j++) {
            $columns .= ",data_" . $i . "_" . $j;
        }
    }

    $query = "CREATE TABLE $tablename (bid int not null primary key auto_increment,info tinytext,id tinytext,name tinytext,score tinytext";
    for ($i = 1; $i <= max(1, $nprob); $i++) {
        for ($j = 1; $j <= 6; $j++) {
            $query .= ", data_" . $i . "_" . $j . " longtext";
        }
    }
    $query .= ");";
    mysqli_query($link,$query);

    $_POST["recordname"] = preg_replace("/\"/", "'", $_POST["recordname"]);
    mysqli_query($link,"INSERT INTO $tablename (info,id,name,score) VALUES ('system-fields','Timestamp','Commit Name','Status');");
    mysqli_query($link,"INSERT INTO $tablename (info,id,name,score) VALUES ('system','" . time() . "','" . addslashes($_POST["recordname"]) . "','Active');");

    mysqli_query($link,"INSERT INTO $tablename (info,id,name,score,data_1_1,data_1_2,data_1_3,data_1_4,data_1_5,data_1_6) VALUES ('problem-fields','Problem ID','Problem Name','Problem Score','Problem Statement','Problem Input','Problem Output','Problem Time Limit','Problem Type','Problem Statistics');");

    $pids = array();
    while ($problem = mysqli_fetch_array($problems)) {
        $stat1 = mysqil_query($link,"SELECT count( DISTINCT tid ) FROM runs WHERE result='AC' AND pid=" . $problem["pid"]);
        if (!mysqli_num_rows($stat1)) {
            echo mysqli_error();
        }
        $stat1 = mysqli_fetch_array($stat1);
        $stat2 = mysqli_query($link,"SELECT count( DISTINCT tid ) FROM runs WHERE pid=" . $problem["pid"]);
        if (!mysqli_num_rows($stat2)) {
            echo mysqli_error();
        }
        $stat2 = mysqli_fetch_array($stat2);
        $statistics = $stat1[0] . "/" . $stat2[0];
        $pids[] = $problem["pid"];
        mysqli_query($link,"INSERT INTO $tablename (info,id,name,score,data_1_1,data_1_2,data_1_3,data_1_4,data_1_5,data_1_6) VALUES ('problem','" . addslashes($problem["pid"]) . "','" . addslashes($problem["name"]) . "','" . addslashes($problem["score"]) . "','" . addslashes($problem["statement"]) . "','" . addslashes($problem["input"]) . "','" . addslashes($problem["output"]) . "','" . addslashes($problem["timelimit"]) . "','" . addslashes($problem["type"]) . "','$statistics');");
    }


    $query = "INSERT INTO $tablename ($columns) VALUES ('team-fields','Team ID','Team Name','Team Score'";
    foreach ($pids as $pid) {
        $query .= ",'Problem-$pid Attempts','Problem-$pid Language','Problem-$pid Code','Problem-$pid Result','Problem-$pid Time','Problem-$pid Error'";
    }
    $query .= ");";
    mysqli_query($link,$query);

    while ($team = mysqli_fetch_array($teams)) {
        $query = "INSERT INTO $tablename ($columns) VALUES ('team','" . addslashes($team["tid"]) . "','" . addslashes($team["teamname"]) . "','" . addslashes($team["score"]) . ":" . addslashes($team["penalty"]) . "'";
        foreach ($pids as $pid) {
            $ac3 = mysqli_query($link,"SELECT * FROM runs WHERE tid='$team[tid]' AND pid='$pid' AND result='AC' ORDER BY rid DESC LIMIT 0,1");
            if (mysqli_num_rows($ac3) == 1) {
                $ac3 = mysqli_fetch_array($ac3);
            } else {
                $ac3 = NULL;
            }
            if ($ac3 != NULL) {
                $ac2 = mysqli_query($link,"SELECT * FROM runs WHERE tid='$team[tid]' AND pid='$pid' AND result='AC' AND access!='deleted' ORDER BY rid ASC  LIMIT 0,1");
                if (mysqli_num_rows($ac2) == 1) {
                    $ac2 = mysqli_fetch_array($ac2);
                } else {
                    $ac2 = NULL;
                }
                $ac1 = mysqli_query($link,"SELECT count(*) FROM runs WHERE tid='$team[tid]' AND pid='$pid' AND result!='AC' AND access!='deleted' AND rid<=" . $ac2["rid"]) + 1;
                if (mysqli_num_rows($ac1) == 1) {
                    $ac1 = mysqli_fetch_array($ac1);
                } else {
                    $ac1 = NULL;
                }
                $query .= ",'$ac1[0]','$ac2[language]','$ac2[code]','$ac2[result]','$ac2[time]','$ac2[error]'";
            } else {
                $ac1 = mysqli_query($link,"SELECT count(*) FROM runs WHERE tid='$team[tid]' AND pid='$pid' AND result!='AC' AND access!='deleted'");
                if (mysqli_num_rows($ac1) == 1) {
                    $ac1 = mysqli_fetch_array($ac1);
                } else {
                    $ac1 = NULL;
                }
                $ac0 = mysqli_query($link,"SELECT * FROM runs WHERE tid='$team[tid]' AND pid='$pid' AND access!='deleted' ORDER BY rid DESC LIMIT 0,1");
                if (mysqli_num_rows($ac0) == 1) {
                    $ac0 = mysqli_fetch_array($ac0);
                } else {
                    $ac0 = NULL;
                }
                if ($ac0 == NULL) {
                    $query .= ",'0','','','','',''";
                } else {
                    $query .= ",'$ac1[0]','$ac0[language]','$ac0[code]','$ac0[result]','$ac0[time]','$ac0[error]'";
                }
            }
        }
        $query .= ");";
        mysqli_query($link,$query);
    }

    $_SESSION["message"][] = "Data Commitment Successful";
    action_scoreboard();
}


function action_commitupdate(): void
{
    global $currentmessage, $mysql_database;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform that operation.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
    if (empty($_POST["tablename"]) || empty($_POST["recordname"]) || empty($_POST["status"])) {
        $_SESSION["message"][] = "Commited Record Updation : Insufficient Data";
        return;
    }
    $_POST["recordname"] = preg_replace("/\"/", "'", $_POST["recordname"]);
    mysqli_query($link,"UPDATE " . $_POST["tablename"] . " SET name='" . addslashes($_POST["recordname"]) . "' WHERE info='system'");
    if ($_POST["status"] == "Delete") {
        mysqli_query($link,"DROP TABLE " . $_POST["tablename"]);
    } else if ($_POST["status"] == "Inactive") {
        mysqli_query($link,"UPDATE " . $_POST["tablename"] . " SET score='Inactive' WHERE info='system'");
    } else if ($_POST["status"] == "Active") {
        mysqli_query($link,"UPDATE " . $_POST["tablename"] . " SET score='Active' WHERE info='system'");
    }

    if ($_POST["status"] != "Delete") {
        $_SESSION["message"][] = "Commited Record Updation Successful";
    } else {
        $_SESSION["message"][] = "Commited Record Deletion Successful";
    }
    action_scoreboard();
}


function action_scoreboard(): void
{
    global $currentmessage, $mysql_database;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to perform that operation.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }

    $total = array();
    $penalty = array();
    $score = array();
    $tids = array();
    $teamname = array();
    $data = mysqli_query($link,"SELECT tid,teamname FROM teams WHERE status='Normal'");
    if (mysqli_num_rows($data)) {
        while ($temp = mysqli_fetch_array($data)) {
            $score[$temp["tid"]] = array("tid" => $temp["tid"], "total" => 0);
            $total[$temp["tid"]] = 0;
            $penalty[$temp["tid"]] = 0;
            $tids[] = $temp["tid"];
            $teamname[$temp["tid"]] = $temp["teamname"];
        }
    }

    $names = array();
    $data = mysqli_list_tables($mysql_database);
    $tables = array();
    while ($temp = mysqli_fetch_row($data)) {
        $tables[] = $temp[0];
    }
    foreach ($tables as $table) {
        if (preg_match("/^backup_/i", $table)) {
            $system = mysqli_query($link,"SELECT * FROM $table WHERE info='system' and score='Active'");
            if (mysqli_num_rows($system) == 0) {
                continue;
            }
            $system = mysqli_fetch_array($system);
            $names[] = $system["name"];
            foreach ($tids as $tid) {
                $data = mysqli_query($link,"SELECT score FROM $table WHERE info='team' AND id='$tid'");
                if (mysqli_num_rows($data) == 0) continue;
                $data = mysqli_fetch_array($data);
                $data["score"] = explode(":", $data["score"]);
                $score[$tid]["~" . $system["name"]] = $data["score"][0];
                $score[$tid]["total"] += $data["score"][0];
                $total[$tid] += $data["score"][0];
                $penalty[$tid] += $data["score"][1];
            }
        }
    }
    array_multisort($total, SORT_NUMERIC, SORT_DESC, $penalty, SORT_NUMERIC, SORT_ASC, $score);
    $filedata = "<table><tr><th>Rank</th><th>Team ID</th><th>Team Name</th>";
    foreach ($names as $name) {
        $filedata .= "<th>" . stripslashes($name) . "</th>";
    }
    $filedata .= "<th>Total</th></tr>";
    foreach ($score as $i => $team) {
        $tid = $team["tid"];
        $filedata .= "<tr><td>" . ($i + 1) . "</td><td><a href='?display=submissions&tid=$tid'>$tid</a></td><td><a href='?display=submissions&tid=$tid'>" . $teamname[$tid] . "</a></td>";
        foreach ($names as $name) {
            if (isset($team["~" . $name])) {
                $filedata .= "<td>" . stripslashes($team["~" . $name]) . "</td>";
            } else {
                $filedata .= "<td>0</td>";
            }
        }
        $filedata .= "<th>" . $team["total"] . "</tr>";
    }
    $filedata .= "</table>";
    $temp = mysqli_query($link,"SELECT * FROM admin WHERE variable='scoreboard'");
    if (mysqli_num_rows($temp) > 0) {
        mysqli_query($link,"UPDATE admin SET value='" . addslashes($filedata) . "' WHERE variable='scoreboard'");
    } else {
        mysqli_query($link,"INSERT INTO admin VALUES ('scoreboard','" . addslashes($filedata) . "')");
    }
}

?>