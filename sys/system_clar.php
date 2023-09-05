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
            $filedata .= "<table class='table table-borderless'><tr class='table-primary'><th><h4>Public Clarifications</h4></th></tr><tr><td><b><a class='list-group-item' href='?display=submissions&tid=$temp[tid]'>" . $team[$temp["tid"]] . "</a> (";
            $filedata .= ($temp["pid"] == 0) ? "General" : "<a href='?display=problem&pid=$temp[pid]'>" . $prob[$temp["pid"]] . "</a>";
            $filedata .= ")</b> : " . ($temp["query"]) . "";
            if (!empty($temp["reply"])) {
                $filedata .= "</td></tr><tr><td><i><b>Response</b> : " . ($temp["reply"]) . "</i>";
            }
            $filedata .= "</td></tr></table>";
        }
    } else {
        $filedata .= "<table class='table table-borderless'><thead><tr class='table-primary'><th><h4><a class='list-group-item' href='?display=clarifications' title='Link to Clarifications Page'>Public Clarifications</a></h4></th></tr></thead><tbody><tr><td>Not Available</td></tr></tbody></table>";
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
    $totalpages = max(1, ceil($totalCount / $limit));
    $x = paginate($url, $totalCount, $limit);
    $currentPage = $x[0];
    $pagenav = $x[1];

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
        echo "<div class='mb-3'><form action='?action=requestclar' method='post' onSubmit='return validate_clar();'>";
        echo "<table class='table table-borderless'><thead><tr class='table-primary'><th colspan='2'><h3>Clarification Submissions</h3></th></tr><tr><th class='table-info'>Team Name</th><td>" . $_SESSION["teamname"] . "</td></tr></thead>";
        echo "<tr><th class='table-info'>Select Problem</th><td><select class='form-select' name='problem'><option value=0>General (No Specific Problem)</option>";
        $data = mysqli_query($link, "SELECT * FROM problems WHERE status='Active' ORDER BY pid");
        while ($problem = mysqli_fetch_array($data)) {
            echo "<option value='" . $problem["pid"] . "'>" . filter($problem["name"]) . "</option>";
        }
        echo "</select></td></tr>";
        echo "<tr><th class='table-info'>Query</th><td><textarea class='form-control' name='query' placeholder=\"Type your query here\"></textarea></td></tr>";
        echo "<tr><td colspan='2'><button class='btn btn-outline-success' type='submit'>Submit</button></td></tr></table></form></div>";
    }
    echo "<div class='mb-3'><table class='table table-borderless'>";
    echo "<thead><tr class='table-primary'><th colspan='2'><h4>Clarifications</h4></th></tr><tr class='table-info'><th>Query / Response</th><th>Options</th></tr></thead><tbody>";
    if ($_SESSION["status"] == "Admin") {
        $data = mysqli_query($link, "SELECT * FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private'" : "access!='Delete'")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")) . " ORDER BY time ASC LIMIT " . (($currentPage - 1) * $limit) . ",$limit");
    } else {
        $data = mysqli_query($link, "SELECT * FROM clar WHERE " . ($type == "public" ? "access='Public'" : ($type == "private" ? "access='Private' AND tid=" . $_SESSION["tid"] : "access!='Delete' AND (clar.access='Public' or tid=" . $_SESSION["tid"] . ")")) . " " . ($reply == "no" ? " AND reply='' " : ($reply == "yes" ? " AND reply!='' " : "")) . " ORDER BY time ASC LIMIT " . (($currentPage - 1) * $limit) . ",$limit");
    }

    if(mysqli_num_rows($data)>0){
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
            echo "<tr><td " . ($highlight) . "><b><a href='?display=submissions&tid=$temp[tid]'>" . $team[$temp["tid"]]["name"] . "</a> (" . ($temp["pid"] == 0 ? "General" : "<a href='?display=problem&pid=$temp[pid]'>" . $prob[$temp["pid"]] . "</a>") . ")</b> : " . $temp["query"] . "</td>";
            if ($_SESSION["status"] == "Admin") {
                echo "<td " . ($highlight) . " rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "><div class='d-flex mb-3 justify-content-center'>";
                echo "<select class='form-select' onChange=\"if(confirm('Are you sure you wish to perform this operation?')){ f=document.forms['updateclar']; f.field.value='Status'; f.time.value=$temp[time]; f.value.value=this.value; f.submit(); }\">";
                echo($temp["access"] == "Public" ? "<option selected='selected'>Public</option><option>Private</option>" : "<option>Public</option><option selected='selected'>Private</option>");
                echo "<option>Delete</option></select><button class='btn btn-info mx-1' onClick=\"reply=prompt('Enter response (previous response will be overwritten) : ','" . $temp["reply"] . "'); if(reply.match(/" . preg_replace("/\n/", "\\n", $invalidchars) . "/) != null){ alert('Reply contains invalid characters.'); } else if(reply != null){ f=document.forms['updateclar']; f.field.value='Reply'; f.time.value=$temp[time]; f.value.value=reply; f.submit(); }\">Reply</button></div></td>";
            } else if ($_SESSION["tid"] == $temp["tid"] && empty($temp["reply"])) {
                echo "<td " . ($highlight) . " rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "><button class='btn btn-outline-danger mx-1' onClick=\"if(confirm('Are you sure you want to delete this clarification?')){ f=document.forms['updateclar']; f.field.value='Status'; f.time.value=$temp[time]; f.value.value='Delete'; f.submit(); } \">Delete</button></td>";
            } else {
                echo "<td rowspan=" . (empty($temp["reply"]) ? 1 : 2) . "></td></tr>";
            }
            echo "</tr>";
            if (!empty($temp["reply"])) {
                echo "<tr><td><i>" . ($temp["reply"] != "" ? "<b>Judge's Response</b> : " : "") . $temp["reply"] . "</i></td></tr>";
            }
            echo "<tr><td colspan=2></td></tr>";
        }
    }else{
        echo "<tr><td colspan='2'>No data available.</td></tr>";
    }
    
    echo "<form name='updateclar' action='?action=updateclar' method='post'><input type='hidden' name='field'><input type='hidden' name='time'><input type='hidden' name='value'></form>";
    echo "<tr><th colspan='2'><b>Filter(s)</b> : Access [ ";
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
    echo " ] Reply [ ";
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
    echo " ]</th></tr>";
    echo "</tbody></table></div>";
    if ($_SESSION["status"] == "Admin") {
        echo "<div class='mb-3'><button class='btn btn-outline-danger' type='button' onclick=\"if(confirm('¿Estás seguro de que deseas eliminar todas las solicitudes de aclaración?')) { f = document.forms['updateclar']; f.field.value = 'Clear'; f.submit(); }\">Delete All Clarification Requests</button></div>";
    }
    echo "
    <div class='mb-3 d-flex justify-content-center'>
        <nav aria-label='Page navigation example'>
            <ul class='pagination'>
                <li class='page-item" . ($currentPage == 1 ? " disabled" : "") . "'>
                    <a class='page-link' href='?$url&" . ($currentPage == 1 ? "" : "page=" . ($currentPage - 1)) . "' aria-label='Previous'>
                    <span aria-hidden='true'>&laquo;</span>
                    </a>
                </li>";

    for ($page = max(1, $currentPage - 2); $page <= min($currentPage + 2, $totalpages); $page++) {
    echo "<li class='page-item" . ($currentPage == $page ? " active" : "") . "'><a class='page-link' href='?$url&page=$page'>$page</a></li>";
    }

    echo "
                <li class='page-item" . ($currentPage == $totalpages ? " disabled" : "") . "'>
                    <a class='page-link' href='?$url&" . ($currentPage == $totalpages ? "" : "page=" . ($currentPage + 1)) . "' aria-label='Next'>
                    <span aria-hidden='true'>&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>";

    echo "<div class='small'>This feature exists only to provide contestants a way to communicate with the judges in case of any ambiguity regarding problems or the contest itself.
		<br>The Query Text cannot contain single or double quotes.
		<br>Please refrain from using this feature unless absolutely necessary. Ensure that your problem has not already been answered in the <a href='?display=faq'>FAQ Section</a>.
		</div>";
}

?>