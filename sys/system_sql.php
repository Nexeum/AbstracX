<?php
function _md5($str)
{
    return $str;
}
function mysql_initiate()
{
    global $mysql_database, $admin, $ajaxlogout, $sessionid, $admin_teamname, $admin_password;

    $link = mysqli_connect("localhost", "root", "","nexeum");
    if (!$link) {
        $_SESSION["message"][] = "SQL Error : Could Not Establish Connection.";
        return;
    }

    $query = "SHOW TABLES";
    $data = mysqli_query($link, $query);

    $table = array();
    if ($data) {
        while ($row = mysqli_fetch_row($data)) {
            $table[] = $row[0];
        }
    }
    if (!in_array("teams", $table)) {
        mysqli_query($link, "CREATE TABLE teams (tid int not null primary key auto_increment,teamname tinytext,teamname2 tinytext,pass tinytext,status tinytext,score int,penalty bigint,name1 tinytext,roll1 tinytext,branch1 tinytext,email1 tinytext,phone1 tinytext,name2 tinytext,roll2 tinytext,branch2 tinytext,email2 tinytext,phone2 tinytext,name3 tinytext,roll3 tinytext,branch3 tinytext,email3 tinytext,phone3 tinytext,platform text,ip text,session tinytext,gid int not null)");
    }
    if (!in_array("problems", $table)) {
        mysqli_query($link, "CREATE TABLE problems (pid int not null primary key auto_increment,code tinytext,name tinytext,type tinytext,status tinytext,pgroup tinytext,statement longtext,image blob,imgext tinytext,input longtext,output longtext,timelimit int,score int,languages tinytext,options tinytext)");
    }
    if (!in_array("runs", $table)) {
        mysqli_query($link, "CREATE TABLE runs (rid int not null primary key auto_increment,pid int,tid int,language tinytext,name tinytext,code longtext,time tinytext,result tinytext,error text,access tinytext,submittime int,output longtext)");
    }
    if (!in_array("admin", $table)) {
        mysqli_query($link, "CREATE TABLE admin (variable tinytext,value longtext)");
    }
    if (!in_array("logs", $table)) {
        mysqli_query($link, "CREATE TABLE logs (time DECIMAL(20,6) not null primary key,ip tinytext,tid int,request tinytext)");
    }
    if (!in_array("clar", $table)) {
        mysqli_query($link, "CREATE TABLE clar (time int not null primary key,tid int,pid int,query text,reply text,access tinytext,createtime int)");
    }
    if (!in_array("groups", $table)) {
        mysqli_query($link, "CREATE TABLE groups (gid int not null primary key auto_increment, groupname tinytext, statusx int)");
    }

    $temp = mysqli_query($link, "SELECT * FROM teams");
    if ($temp) {
        if (mysqli_num_rows($temp) == 0) {
            mysqli_query($link, "INSERT INTO teams (teamname, pass, status, score, gid, name1, roll1, branch1, email1, phone1) VALUES ('" . ($admin_teamname) . "','" . _md5($admin_password) . "','Admin', 0, 1, 'Kaustubh Karkare', '', '', 'kaustubh.karkare@gmail.com', '')");
            mysqli_query($link, "INSERT INTO teams (teamname, pass, status, score, gid, name1, roll1, branch1, email1, phone1) VALUES ('" . ($admin_teamname) . "','" . _md5($admin_password) . "','Admin', 0, 1, 'Kaustubh Karkare', '', '', 'kaustubh.karkare@gmail.com', '')");
        }
    } else {
        echo "Error en la consulta: " . mysqli_error($link);
    }

    $temp = mysqli_query($link, "SELECT * FROM problems");
    if (mysqli_num_rows($temp) == 0) {
        mysqli_query($link, "INSERT INTO problems (pid,code,name,type,status,pgroup,statement,input,output,timelimit,score,languages) VALUES (1,'TEST','Squares','Ad-Hoc','Active','#00 Test','" . addslashes(file_get('data/example/problem.txt')) . "','" . addslashes(file_get('data/example/input.txt')) . "','" . addslashes(file_get('data/example/output.txt')) . "',1,0,'Brain,C,C++,C#,Java,JavaScript,Pascal,Perl,PHP,Python,Ruby,Text')");
    }
    $temp = mysqli_query($link, "SELECT * FROM runs");
    if (mysqli_num_rows($temp) == 0) {
        mysqli_query($link,"INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (1,1,1,'C','code','" . (addslashes(file_get('data/example/code.c'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (2,1,1,'C++','code','" . (addslashes(file_get('data/example/code.cpp'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (3,1,1,'C#','code','" . (addslashes(file_get('data/example/code.cs'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (4,1,1,'Java','code','" . (addslashes(file_get('data/example/code.java'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (5,1,1,'JavaScript','code','" . (addslashes(file_get('data/example/code.js'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (6,1,1,'Pascal','code','" . (addslashes(file_get('data/example/code.pas'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (7,1,1,'Perl','code','" . (addslashes(file_get('data/example/code.pl'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (8,1,1,'PHP','code','" . (addslashes(file_get('data/example/code.php'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (9,1,1,'Python','code','" . (addslashes(file_get('data/example/code.py'))) . "',NULL,NULL,'public')");
        mysqli_query($link, "INSERT INTO runs (rid,pid,tid,language,name,code,time,result,access) VALUES (10,1,1,'Ruby','code','" . (addslashes(file_get('data/example/code.rb'))) . "',NULL,NULL,'public')");
    }
    $temp = mysqli_query($link, "SELECT * FROM admin");
    if (mysqli_num_rows($temp) == 0) {
        mysqli_query($link, "INSERT INTO admin VALUES ('mode','Passive');");
        mysqli_query($link, "INSERT INTO admin VALUES ('lastjudge','0');");
        mysqli_query($link, "INSERT INTO admin VALUES ('ajaxrr','0');");
        mysqli_query($link, "INSERT INTO admin VALUES ('mode','Passive');");
        mysqli_query($link, "INSERT INTO admin VALUES ('penalty','20');");
        mysqli_query($link, "INSERT INTO admin VALUES ('mysublist','5');");
        mysqli_query($link, "INSERT INTO admin VALUES ('allsublist','10');");
        mysqli_query($link, "INSERT INTO admin VALUES ('ranklist','10');");
        mysqli_query($link, "INSERT INTO admin VALUES ('clarpublic','2');");
        mysqli_query($link, "INSERT INTO admin VALUES ('clarprivate','2');");
        mysqli_query($link, "INSERT INTO admin VALUES ('regautoauth','1');");
        mysqli_query($link, "INSERT INTO admin VALUES ('multilogin','0');");
        mysqli_query($link, "INSERT INTO admin VALUES ('clarpage','10');");
        mysqli_query($link, "INSERT INTO admin VALUES ('substatpage','25');");
        mysqli_query($link, "INSERT INTO admin VALUES ('probpage','25');");
        mysqli_query($link, "INSERT INTO admin VALUES ('teampage','25');");
        mysqli_query($link, "INSERT INTO admin VALUES ('rankpage','25');");
        mysqli_query($link, "INSERT INTO admin VALUES ('logpage','100');");
        mysqli_query($link, "INSERT INTO admin VALUES ('notice','Announcements Welcome to the Nexeum Online Judge.');");
    }

    $data = mysqli_query($link, "SELECT * FROM admin");
    while ($temp = mysqli_fetch_array($data)) {
        if ($temp["variable"] != "scoreboard") {
            $admin[$temp["variable"]] = $temp["value"];
        }
    }
    if ((isset($admin["mode"]) && $admin["mode"] == "Active") && time() >= $admin["endtime"]) {
        $admin["mode"] = "Disabled";
    }
    if ((isset($admin["mode"]) && $admin["mode"] == "Lockdown") && $_SESSION["tid"] != 0 && $_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Access Denied : You have been logged out as the contest has been locked down. Please try again again.";
        action_logout();
        $ajaxlogout = 1;
    }
    if (isset($admin["multilogin"]) && !$admin["multilogin"] && $_SESSION["tid"] && $_SESSION["status"] != "Admin") {
        $sess = mysqli_query($link, "SELECT session FROM teams WHERE tid=" . $_SESSION["tid"]);
        $sess = mysqli_fetch_array($sess);
        $sess = $sess["session"];
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