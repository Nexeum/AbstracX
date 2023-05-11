<?php

function action_requestclar()
{
    global $invalidchars;
    $link = mysqli_connect("localhost", "root", "", "nexeum");


    if (empty($_POST["query"]) || !isset($_POST["problem"])) {
        $_SESSION["message"][] = "Clarification Request Error : Insufficient Data";
        return;
    }
    if ($_SESSION["tid"] == 0) {
        $_SESSION["message"][] = "Clarification Request Error : You must be logged in to post your queries here.";
        return;
    }
    if (preg_match($invalidchars, $_POST["query"])) {
        $_SESSION["message"][] = "Clarification Request Error : Invalid characters in Query.";
        return;
    }
    mysqli_query($link, "INSERT INTO clar VALUES (" . time() . "," . $_SESSION["tid"] . "," . $_POST["problem"] . ",\"" . filter($_POST["query"]) . "\",'','Private'," . time() . ");");
    action_clarcache();
}

function action_clarcache()
{
    global $admin;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    $team = array();
    $data = mysqli_query($link, "SELECT tid,teamname FROM teams");
    while ($temp = mysqli_fetch_array($data)) {
        $team[$temp["tid"]] = filter($temp["teamname"]);
    }
    $prob = array(0 => "General");
    $data = mysqli_query($link, "SELECT pid,name FROM problems WHERE status='Active'");
    while ($temp = mysqli_fetch_array($data)) $prob[$temp["pid"]] = filter($temp["name"]);
    if (isset($admin["clarpublic"]) && $admin["clarpublic"] >= 0) {
        $limit = $admin["clarpublic"];
    } else {
        $limit = 2;
    }
    $data = mysqli_query($link, "SELECT * FROM (SELECT * FROM clar WHERE clar.access='Public' and (clar.pid=0 or (SELECT status FROM problems WHERE problems.pid=clar.pid)='Active') ORDER BY time DESC LIMIT 0,$limit) as latest ORDER BY time ASC ");
    $filedata = "";
    if (mysqli_num_rows($data) > 0) {
        while ($temp = mysqli_fetch_array($data)) {
            $filedata .= "<table><tr><td style='text-align:left;'><b><a href='?display=submissions&tid=$temp[tid]'>" . $team[$temp["tid"]] . "</a> (";
            $filedata .= ($temp["pid"] == 0) ? "General" : "<a href='?display=problem&pid=$temp[pid]'>" . $prob[$temp["pid"]] . "</a>";
            $filedata .= ")</b> : " . ($temp["query"]) . "";
            if (!empty($temp["reply"])) {
                $filedata .= "</td></tr><tr><td style='text-align:left;'><i><b>Response</b> : " . ($temp["reply"]) . "</i>";
            }
            $filedata .= "</td></tr></table>";
        }
    } else {
        $filedata .= "<table><tr><td>Not Available</td></tr></table>";
    }
    $admin["cache-clarlatest"] = $filedata;
}

function action_updateclar()
{
    global $invalidchars;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    if ($_SESSION["status"] != "Admin" && $_SESSION["tid"] != 0) { // special condition for normal users to delete their clarifications
        if (empty($_POST["field"])) {
            $_SESSION["message"][] = "Clarification Update Error : Insufficient Data.";
            return;
        }
        action_clarcache();
        if ($_POST["field"] == "Status") {
            if (empty($_POST["time"]) || empty($_POST["value"])) {
                $_SESSION["message"][] = "Clarification Update Error : Insufficient Data.";
                return;
            }
            if (!in_array($_POST["value"], array("Delete"))) {
                $_SESSION["message"][] = "Clarification Update Error : Invalid Data.";
                return;
            }
            mysqli_query($link, "UPDATE clar SET access='Delete',time=" . time() . " WHERE tid=" . $_SESSION["tid"] . " AND reply='' AND time=" . $_POST["time"]);
            $_SESSION["message"][] = "Clarification Deletion Successful.";
        }
        action_clarcache();
        return;
    }
    if (empty($_POST["field"])) {
        $_SESSION["message"][] = "Clarification Update Error : Insufficient Data.";
        return;
    }
    if ($_SESSION["status"] != "Admin") {
        $_SESSION["message"][] = "Clarification Update Error : You need to be an Administrator to perform this action.";
        return;
    }
    if ($_POST["field"] == "Reply") {
        if (empty($_POST["time"])) {
            $_SESSION["message"][] = "Clarification Update Error : Insufficient Data.";
            return;
        }
        if (preg_match($invalidchars, $_POST["value"])) {
            $_SESSION["message"][] = "Clarification Update Error : Invalid characters in Reply.";
            return;
        }
        if (!empty($_POST["value"])) {
            mysqli_query($link, "UPDATE clar SET time=" . time() . ",reply='" . addslashes(filter($_POST["value"])) . "' WHERE time=" . $_POST["time"]);
        } else {
            mysqli_query($link, "UPDATE clar SET reply='' WHERE time=" . $_POST["time"]);
        }
    }
    if ($_POST["field"] == "Status") {
        if (empty($_POST["time"]) || empty($_POST["value"])) {
            $_SESSION["message"][] = "Clarification Update Error : Insufficient Data.";
            return;
        }
        if (!in_array($_POST["value"], array("Public", "Private", "Delete"))) {
            $_SESSION["message"][] = "Clarification Update Error : Invalid Data.";
            return;
        }
        mysqli_query($link, "UPDATE clar SET access='" . $_POST["value"] . "',time=" . time() . " WHERE time=" . $_POST["time"]);
    }
    if ($_POST["field"] == "Clear") {
        mysqli_query($link, "UPDATE clar SET access='Delete'");
    }
    action_clarcache();
}

function display_clarifications()
{
    global $admin, $invalidchars;
    $link = mysqli_connect("localhost", "root", "", "nexeum");

    echo "<center>";

    if (isset($_GET["type"]) and in_array($_GET["type"], array("public", "private"))) {
        $type = $_GET["type"];
    } else {
        $type = "all";
    }
    if (isset($_GET["reply"]) and in_array($_GET["reply"], array("yes", "no"))) {
        $reply = $_GET["reply"];
    } else {
        $reply = "all";
    }


    echo "<div class='filter'><b>Filter(s)</b> : Access [ ";
    foreach (array("public", "private", "all") as $t) {
        if ($type != $t) {
            echo "<a href='?display=clarifications&type=" . $t . "&reply=" . $reply . "'>" . ucfirst($t) . "</a>";
        } else {
            echo "<b>" . ucfirst($t) . "</b>";
        }
        if ($t != "all") {
            echo " / ";
        }
    }
    echo " ], Reply [ ";
    foreach (array("yes", "no", "all") as $r) {
        if ($reply != $r) {
            echo "<a href='?display=clarifications&type=" . $type . "&reply=" . $r . "'>" . ucfirst($r) . "</a>";
        } else {
            echo "<b>" . ucfirst($r) . "</b>";
        }
        if ($r != "all") {
            echo " / ";
        }
    }
    echo " ]</div>";

    echo "<h2>Clarifications</h2>";

    if ($_SESSION["status"] == "Admin") {
        $total = mysqli_query($link, "SELECT count(*) as total FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private'" : "access!='Delete'")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")));
    } else {
        $total = mysqli_query($link, "SELECT count(*) as total FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private' AND tid=" . $_SESSION["tid"] : "access!='Delete' AND (clar.access='Public' or tid=" . $_SESSION["tid"] . ")")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")));
    }
    $totalResult = mysqli_fetch_array($total);
    $totalCount = $totalResult["total"];
    if (isset($admin["clarpage"])) {
        $limit = $admin["clarpage"];
    } else {
        $limit = 10;
    }
    $url = "display=clarifications&type=" . $type . "&reply=" . $reply;
    $_GET["page"] = max(1, ceil($totalCount / $limit));
    $x = paginate($url, $total, $limit);
    $page = $x[0];
    $pagenav = $x[1];
    echo $pagenav . "<br><br>";

    $team = array();
    $data = mysqli_query($link, "SELECT tid,teamname,status FROM teams");
    while ($temp = mysqli_fetch_array($data)) {
        $team[$temp["tid"]] = array("name" => filter($temp["teamname"]), "status" => $temp["status"]);
    }
    $prob = array(0 => "General");
    $data = mysqli_query($link, "SELECT pid,name FROM problems WHERE status='Active'");
    while ($temp = mysqli_fetch_array($data)) {
        $prob[$temp["pid"]] = filter($temp["name"]);
    }
    echo "<table width=100%><tr><th>Query / Response</th><th width='120px'>Options</th></tr>";
    if ($_SESSION["status"] == "Admin") {
        $data = mysqli_query($link, "SELECT * FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private'" : "access!='Delete'")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")) . " ORDER BY time ASC LIMIT " . (($page - 1) * $limit) . ",$limit");
    } else {
        $data = mysqli_query($link, "SELECT * FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private' AND tid=" . $_SESSION["tid"] : "access!='Delete' AND (clar.access='Public' or tid=" . $_SESSION["tid"] . ")")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")) . " ORDER BY time ASC LIMIT " . (($page - 1) * $limit) . ",$limit");
    }
    while ($temp = mysqli_fetch_array($data)) {
        if (!isset($temp["tid"])) {
            continue;
        }
        if (!isset($temp["pid"])) {
            continue;
        }
        if ($_SESSION["status"] == "Admin") {
            $highlight = (($temp["reply"] == "" && $team[$temp["tid"]]["status"] != "Admin") ? " class='highlight' " : "");
        } else {
            $highlight = (($temp["tid"] == $_SESSION["tid"]) ? " class='highlight' " : "");
        }
        echo "<tr><td style='text-align:left;' " . ($highlight) . "><b><a href='?display=submissions&tid=$temp[tid]'>" . $team[$temp["tid"]]["name"] . "</a> (" . ($temp["pid"] == 0 ? "General" : "<a href='?display=problem&pid=$temp[pid]'>" . $prob[$temp["pid"]] . "</a>") . ")</b> : " . $temp["query"] . "</td>";
        if ($_SESSION["status"] == "Admin") {
            echo "<td " . ($highlight) . " rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "><input type='button' value='Reply' style='padding:0px;' onClick=\"reply=prompt('Enter response (previous response will be overwritten) : ','" . $temp["reply"] . "'); if(reply.match(/" . preg_replace("/\n/", "\\n", $invalidchars) . "/) != null){ alert('Reply contains invalid characters.'); } else if(reply != null){ f=document.forms['updateclar']; f.field.value='Reply'; f.time.value=$temp[time]; f.value.value=reply; f.submit(); }\"> ";
            echo "<select onChange=\"if(confirm('Are you sure you wish to perform this operation?')){ f=document.forms['updateclar']; f.field.value='Status'; f.time.value=$temp[time]; f.value.value=this.value; f.submit(); }\">";
            echo($temp["access"] == "Public" ? "<option selected='selected'>Public</option><option>Private</option>" : "<option>Public</option><option selected='selected'>Private</option>");
            echo "<option>Delete</option></select></td>";
        } else if ($_SESSION["tid"] == $temp["tid"] && empty($temp["reply"])) {
            echo "<td " . ($highlight) . " rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "><input type='button' value='Delete' style='padding:0px;' onClick=\"if(confirm('Are you sure you want to delete this clarification?')){ f=document.forms['updateclar']; f.field.value='Status'; f.time.value=$temp[time]; f.value.value='Delete'; f.submit(); } \"></td>";
        } else {
            echo "<td rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "></td></tr>";
        }
        echo "</tr>";
        if (!empty($temp["reply"])) {
            echo "<tr><td style='text-align:left;'><i>" . ($temp["reply"] != "" ? "<b>Judge's Response</b> : " : "") . $temp["reply"] . "</i></td></tr>";
        }
        echo "<tr><td colspan=2></td></tr>";
    }
    echo "<form name='updateclar' action='?action=updateclar' method='post'><input type='hidden' name='field'><input type='hidden' name='time'><input type='hidden' name='value'></form>";
    echo "</table><br>$pagenav<br><br>";

    if ($_SESSION["status"] == "Admin") {
        echo "<input type='button' value='Delete All Clarification Requests' onClick=\"if(confirm('Are you sure you wish to Delete All Clarification Requests?')){ f=document.forms['updateclar']; f.field.value='Clear'; f.submit(); }\"><br><br>";
    }
    if ($_SESSION["tid"]) {
        echo "<script>
                function validate_clar(){ 
                    var str=\"\";
                    if($(\"textarea[name='query']\").val().match(/" . preg_replace("/\n/", "\\n", $invalidchars) . "/) != null) {
                        str += \"Query contains invalid characters.\\n\";
                    }
                    if (str == \"\") {
                        return true;
                    }
                    alert(str);
                    return false;
                }
            </script>";
        echo "<form action='?action=requestclar' method='post' onSubmit='return validate_clar();'>";
        echo "<table><tr><th>Team Name</th><td style='text-align:left;'>" . $_SESSION["teamname"] . "</td></tr>";
        echo "<tr><th>Select Problem</th><td><select name='problem' style='width:300px;'><option value=0>General (No Specific Problem)</option>";
        $data = mysqli_query($link, "SELECT * FROM problems WHERE status='Active' ORDER BY pid");
        while ($problem = mysqli_fetch_array($data)) {
            echo "<option value='" . $problem["pid"] . "'>" . filter($problem["name"]) . "</option>";
        }
        echo "</select></td></tr>";
        echo "<tr><th>Query</th><td><textarea name='query' placeholder=\"Type your query here\" style='width:300px; min-width:300px; max-width:300px; height:100px; min-height:100px;'></textarea></td></tr>";
        echo "<tr><th></th><td><input type='submit' value='Submit' style='width:100%;'></td></tr></table></form>";
    }
    echo "<div class='small'>This feature exists only to provide contestants a way to communicate with the judges in case of any ambiguity regarding problems or the contest itself.
		<br>The Query Text cannot contain single or double quotes.
		<br>Please refrain from using this feature unless absolutely necessary. Ensure that your problem has not already been answered in the <a href='?display=faq'>FAQ Section</a>.
		</div>";
    echo "</center>";
}

?>