<?php

// group status : 0 = normal, 1 = restricted, 2 = suspended, 3 = deleted

function action_group_create()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    // Verificar la conexión
    if (!$link) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    echo "Conexión exitosa a la base de datos.";


    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "You need to be an Administrator to perform this action.";
        return;
    }
    if (empty($_POST["groupname"])) {
        $_SESSION["message"][] = "Group Error : Missing/empty Group Name.";
        return;
    }
    if (preg_match("/[^A-Za-z0-9\.\_\-]/i", $_POST["groupname"])) {
        $_SESSION["message"][] = "Group Error : Invalid characters in Group Name.";
        return;
    }
    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<3 AND groupname='" . mysqli_real_escape_string($link, $_POST["groupname"]) . "';");
    if (mysqli_num_rows($data) > 0) {
        $_SESSION["message"][] = "Group Error: This Group Name has already been taken.";
        return;
    }

    mysqli_query($link, "INSERT INTO groups (groupname,statusx) VALUES ('" . mysqli_real_escape_string($link,$_POST["groupname"]) . "',0);");
    $_SESSION["message"][] = "Group created successfully.";
}

function action_group_modify()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "You need to be an Administrator to perform this action.";
        return;
    }
    if (!isset($_GET["gid"]) or !is_numeric($_GET["gid"])) {
        $_SESSION["message"][] = "Group Error : Missing/invalid Group ID.";
        return;
    }
    if (empty($_GET["groupname"])) {
        $_SESSION["message"][] = "Group Error : Missing/empty Group Name.";
        return;
    }
    if (preg_match("/[^A-Za-z0-9\.\_\-]/i", $_GET["groupname"])) {
        $_SESSION["message"][] = "Group Error : Invalid characters in Group Name.";
        return;
    }
    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<3 AND groupname='" . mysqli_real_escape_string($link,$_POST["groupname"]) . "';");
    if (mysqli_num_rows($data) > 0) {
        $_SESSION["message"][] = "Group Error : This Group Name has already been taken.";
        return;
    }
    mysqli_query($link, "UPDATE groups SET groupname='$_GET[groupname] WHERE gid=$_GET[gid];");
    $_SESSION["message"][] = "Group created successfully.";
}

function action_group_status()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "You need to be an Administrator to perform this action.";
        return;
    }
    if (!isset($_GET["gid"]) or !is_numeric($_GET["gid"])) {
        $_SESSION["message"][] = "Group Error : Missing/invalid Group ID.";
        return;
    }
    if (!isset($_GET["status"]) or !is_numeric($_GET["status"]) or $_GET["status"] < 0 or $_GET["status"] > 3) {
        $_SESSION["message"][] = "Group Error : Missing/invalid Group Status.";
        return;
    }
    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<3 AND gid=$_GET[gid];");
    if (mysqli_num_rows($data) == 0) {
        $_SESSION["message"][] = "Group Error : Could not select Group.";
        return;
    }
    mysqli_query($link, "UPDATE groups SET statusx=$_GET[status] WHERE gid=$_GET[gid];");
    $_SESSION["message"][] = "Group Status updated successfully.";
}

function action_group_add()
{
}

function action_group_remove()
{
}

function display_admingroup()
{
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin") {
        global $currentmessage;
        $_SESSION["message"] = $currentmessage;
        $_SESSION["message"][] = "Access Denied : You need to be an Administrator to access that page.";
        echo "<script>window.location='?display=faq';</script>";
        return;
    }
    echo "<center><h2>Group Settings</h2>";
    echo "<form action='?action=group-create' method='post'><table><tr><th>New Group Name</th><td><input type='text' name='groupname'></td><td><input type='submit' value='Create New Group'></td></tr></table></form><br>";
    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<3;");

    echo "<script>function group_status(gid,action){ document.location='?action=group-status&gid='+gid+'&status='+action; }</script>";
    echo "<table><tr><th>Group ID</th><th>Group Name</th><th>Status</th><th>Options</th></tr>";
    $gids = array();
    $status = array();
    while ($row = mysqli_fetch_array($data)) {
        $gids[] = $row["gid"];
        $status[] = $row["statusx"];
        echo "<tr><td>$row[gid]</td><td>$row[groupname]</td><td><select name='group-status-$row[gid]' onChange='if(confirm(\"Are you sure you wish to perform this operation?\")) group_status($row[gid],this.value); else this.value=$row[statusx];'><option value=0>Normal</option><option value=1>Restricted</option><option value=2>Suspended</option><option value=3>Delete</option></select></td><td><input type='button' onClick=\"gname = prompt('Enter New Group Name (only alphanumeric, underscore, dot and dash characters allowed):'); if(gname) document.location='?action=group-modify&gid=$row[gid]&groupname='+gname; \" value='Rename'></td></tr>";
    }
    echo "</table>";
    echo "<script>gids = [" . implode(",", $gids) . "]; status = [" . implode(",", $status) . "]; for(var i=0;i<gids.length;++i) $('select[name=\"group-status-'+gids[i]+'\"]').val(status[i]);</script>";
}

?>