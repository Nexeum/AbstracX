<?php
function _md5($str)
{
    return $str;
}
function mysql_initiate()
{
    global $admin, $ajaxlogout, $sessionid;

    $link = mysqli_connect("localhost", "root", "", "nexeum");
    if (!$link) {
        $_SESSION["message"][] = "SQL Error: Could Not Establish Connection.";
        return;
    }

    $tableQueries = [
        "CREATE TABLE IF NOT EXISTS teams (
            tid int not null primary key auto_increment,
            teamname tinytext,
            teamname2 tinytext,
            pass tinytext,
            status tinytext,
            score int,
            penalty bigint,
            name1 tinytext,
            roll1 tinytext,
            branch1 tinytext,
            email1 tinytext,
            phone1 tinytext,
            name2 tinytext,
            roll2 tinytext,
            branch2 tinytext,
            email2 tinytext,
            phone2 tinytext,
            name3 tinytext,
            roll3 tinytext,
            branch3 tinytext,
            email3 tinytext,
            phone3 tinytext,
            platform text,
            ip text,
            session tinytext,
            gid int not null
        )",
        "CREATE TABLE IF NOT EXISTS problems (
            pid int not null primary key auto_increment,
            code tinytext,
            name tinytext,
            type tinytext,
            status tinytext,
            pgroup tinytext,
            statement longtext,
            image blob,
            imgext tinytext,
            input longtext,
            output longtext,
            timelimit int,
            score int,
            languages tinytext,
            options tinytext
        )",
        "CREATE TABLE IF NOT EXISTS runs (
            rid int not null primary key auto_increment,
            pid int,
            tid int,
            language tinytext,
            name tinytext,
            code longtext,
            time tinytext,
            result tinytext,
            error text,
            access tinytext,
            submittime int,
            output longtext
        )",
        "CREATE TABLE IF NOT EXISTS admin (
            variable tinytext,
            value longtext
        )",
        "CREATE TABLE IF NOT EXISTS logs (
            time DECIMAL(20,6) not null primary key,
            ip tinytext,
            tid int,
            request tinytext
        )",
        "CREATE TABLE IF NOT EXISTS clar (
            time int not null primary key,
            tid int,
            pid int,
            query text,
            reply text,
            access tinytext,
            createtime int
        )",
        "CREATE TABLE IF NOT EXISTS groups (
            gid int not null primary key auto_increment,
            groupname tinytext,
            statusx int
        )"
    ];

    foreach ($tableQueries as $query) {
        mysqli_query($link, $query);
    }

    $teamQuery = mysqli_query($link, "SELECT * FROM teams");
    if ($teamQuery) {
        if (mysqli_num_rows($teamQuery) == 0) {
            mysqli_query($link, "INSERT INTO teams (teamname, pass, status, gid) VALUES ('admin', 'admin', 'Admin', 1)");
        }
    } else {
        echo "Error en la consulta: " . mysqli_error($link);
    }

    $problemQuery = mysqli_query($link, "SELECT * FROM problems");
    if (mysqli_num_rows($problemQuery) == 0) {
        $problemData = [
            'pid' => 1,
            'code' => 'TEST',
            'name' => 'Squares',
            'type' => 'Ad-Hoc',
            'status' => 'Active',
            'pgroup' => '#00 Test',
            'statement' => addslashes(file_get('data/example/problem.txt')),
            'input' => addslashes(file_get('data/example/input.txt')),
            'output' => addslashes(file_get('data/example/output.txt')),
            'timelimit' => 1,
            'score' => 0,
            'languages' => 'Brain,C,C++,C#,Java,JavaScript,Pascal,Perl,PHP,Python,Ruby'
        ];
        $problemDataQuery = "INSERT INTO problems (" . implode(", ", array_keys($problemData)) . ") VALUES ('" . implode("', '", $problemData) . "')";
        mysqli_query($link, $problemDataQuery);
    }

    $runQuery = mysqli_query($link, "SELECT * FROM runs");
    if (mysqli_num_rows($runQuery) == 0) {
        $runData = [
            ['rid' => 1, 'pid' => 1, 'tid' => 1, 'language' => 'C', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.c')), 'access' => 'public'],
            ['rid' => 2, 'pid' => 1, 'tid' => 1, 'language' => 'C++', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.cpp')), 'access' => 'public'],
            ['rid' => 3, 'pid' => 1, 'tid' => 1, 'language' => 'C#', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.cs')), 'access' => 'public'],
            ['rid' => 4, 'pid' => 1, 'tid' => 1, 'language' => 'Java', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.java')), 'access' => 'public'],
            ['rid' => 5, 'pid' => 1, 'tid' => 1, 'language' => 'JavaScript', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.js')), 'access' => 'public'],
            ['rid' => 6, 'pid' => 1, 'tid' => 1, 'language' => 'Pascal', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.pas')), 'access' => 'public'],
            ['rid' => 7, 'pid' => 1, 'tid' => 1, 'language' => 'Perl', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.pl')), 'access' => 'public'],
            ['rid' => 8, 'pid' => 1, 'tid' => 1, 'language' => 'PHP', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.php')), 'access' => 'public'],
            ['rid' => 9, 'pid' => 1, 'tid' => 1, 'language' => 'Python', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.py')), 'access' => 'public'],
            ['rid' => 10, 'pid' => 1, 'tid' => 1, 'language' => 'Ruby', 'name' => 'code', 'code' => addslashes(file_get('data/example/code.rb')), 'access' => 'public']
        ];
        foreach ($runData as $run) {
            $runDataQuery = "INSERT INTO runs (" . implode(", ", array_keys($run)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                return "'" . $value . "'";
            }, $run)) . ")";
            mysqli_query($link, $runDataQuery);
        }
    }

    $adminQueries = [
        "INSERT INTO admin VALUES ('mode', 'Passive')",
        "INSERT INTO admin VALUES ('lastjudge', '0')",
        "INSERT INTO admin VALUES ('ajaxrr', '0')",
        "INSERT INTO admin VALUES ('mode', 'Passive')",
        "INSERT INTO admin VALUES ('penalty', '20')",
        "INSERT INTO admin VALUES ('mysublist', '5')",
        "INSERT INTO admin VALUES ('allsublist', '10')",
        "INSERT INTO admin VALUES ('ranklist', '10')",
        "INSERT INTO admin VALUES ('clarpublic', '2')",
        "INSERT INTO admin VALUES ('clarprivate', '2')",
        "INSERT INTO admin VALUES ('regautoauth', '1')",
        "INSERT INTO admin VALUES ('multilogin', '0')",
        "INSERT INTO admin VALUES ('clarpage', '10')",
        "INSERT INTO admin VALUES ('substatpage', '25')",
        "INSERT INTO admin VALUES ('probpage', '25')",
        "INSERT INTO admin VALUES ('teampage', '25')",
        "INSERT INTO admin VALUES ('rankpage', '25')",
        "INSERT INTO admin VALUES ('logpage', '100')",
        "INSERT INTO admin VALUES ('notice', 'Announcements Welcome to the Nexeum Online Judge.')"
    ];

    $adminQuery = mysqli_query($link, "SELECT * FROM admin");
    if (mysqli_num_rows($adminQuery) == 0) {
        foreach ($adminQueries as $query) {
            mysqli_query($link, $query);
        }
    }

    $admin = [];
    $adminDataQuery = mysqli_query($link, "SELECT * FROM admin");
    while ($temp = mysqli_fetch_array($adminDataQuery)) {
        if ($temp["variable"] != "scoreboard") {
            $admin[$temp["variable"]] = $temp["value"];
        }
    }
    if ((isset($admin["mode"]) && $admin["mode"] == "Active") && time() >= $admin["endtime"]) {
        $admin["mode"] = "Disabled";
    }
    if ((isset($admin["mode"]) && $admin["mode"] == "Lockdown") && $_SESSION["tid"] != 0 && $_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied: You have been logged out as the contest has been locked down. Please try again.";
        action_logout();
        $ajaxlogout = 1;
    }
    if (isset($admin["multilogin"]) && !$admin["multilogin"] && $_SESSION["tid"] && $_SESSION["status"] != "Admin") {
        $sessQuery = mysqli_query($link, "SELECT session FROM teams WHERE tid=" . $_SESSION["tid"]);
        $sess = mysqli_fetch_array($sessQuery)["session"];
        if ($sess != $sessionid) {
            $_SESSION["message"][] = "Multiple Login Not Allowed.";
            action_logout();
            $ajaxlogout = 1;
        }
    }
    if (1 || !isset($admin["adminwork"]) || $admin["adminwork"] < time()) {
        action_adminwork();
        $admin["adminwork"] = time() + 10;
    }
    return 0;
}


function mysqli_terminate()
{
    global $admin;

    $link = mysqli_connect("localhost", "root", "", "nexeum");

    foreach ($admin as $key => $value) {
        $temp = mysqli_query($link, "SELECT * FROM admin WHERE variable='" . addslashes($key) . "'");
        if (!$temp) {
            die("Error en la consulta: " . mysqli_error($link));
        }

        if (mysqli_num_rows($temp) > 0) {
            mysqli_query($link, "UPDATE admin SET value='" . addslashes($value) . "' WHERE variable='" . addslashes($key) . "'");
        } else {
            mysqli_query($link, "INSERT INTO admin VALUES ('" . addslashes($key) . "','" . addslashes($value) . "')");
        }
    }

    $_SESSION["time"] = time();
    mysqli_close($link);
}



function mysqli_getdata($query)
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    $result = mysqli_query($link, $query);

    $data = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $data[] = $row;
    }

    mysqli_free_result($result);
    mysqli_close($link);

    return $data;
}
?>