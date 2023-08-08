<div>
    <div id="teamlist">
        <?php
            $link = mysqli_connect("localhost", "root", "", "nexeum");

            $totalQuery = mysqli_query($link, "SELECT COUNT(*) AS total FROM teams WHERE status != 'Delete'");
            $totalResult = mysqli_fetch_array($totalQuery);
            $totalCount = $totalResult["total"];
            $limit = $admin["teampage"] ?? 25;
            [$page, $pagenav] = paginate("display=adminteam", $totalCount, $limit);
        ?>
        <table class="table table-borderless">
            <tr class="table-primary">
                <th colspan="7">
                    <h3>Administrator Options: List of Teams</h3>
                </th>
            </tr>
            <tr class="table-info">
                <th>Team ID</th>
                <th>Team Name</th>
                <th>Group Name</th>
                <th>Status</th>
                <th>Members</th>
                <th>IP Addresses &amp; Platform</th>
                <th>Update</th>
            </tr>
            <?php
                $data = mysqli_query($link, "SELECT * FROM teams WHERE status!='Delete' ORDER BY tid DESC LIMIT " . (($page - 1) * $limit) . "," . ($limit));
                while ($t = mysqli_fetch_array($data)) {
                    $script = "$('div#teamlist').slideUp(250); $('div#teamedit').slideDown(250); ";
                    foreach ($t as $key => $value) {
                        if (preg_match("/[^0-9]/i", $key) && $key != "pass" && $key != "penalty" && $key != "platform" && $key != "ip" && $key != "session") {
                            $script .= "document.getElementById('update_$key').value='$value'; ";
                        }
                    }
                    $members = array();
                    for ($i = 1; $i <= 3; $i++) {
                        if (!empty($t["name" . $i])) {
                            $members[] = $t["name" . $i];
                        }
                    }
                    $members = implode(", ", $members);
                    $ip = json_decode(stripslashes($t["ip"]));
                    if (is_array($ip)) {
                        $ip = implode(", ", $ip);
                    }
                    $platform = stripslashes($t["platform"]);
                    if ($t["status"] == "Suspend") {
                        $t["status"] = "Suspended";
                    }
                    $groupname = mysqli_query($link, "SELECT groupname FROM groups WHERE gid=$t[gid];");
                    if (mysqli_num_rows($groupname) == 0) {
                        $groupname = "";
                    } else {
                        $groupname = mysqli_fetch_assoc($groupname);
                        $groupname = $groupname["groupname"];
                    }
                    echo "
                        <tr>
                            <td>$t[tid]</td>
                            <td>
                                <a class='list-group-item' href='?display=submissions&tid=$t[tid]'>$t[teamname]
                            </td>
                            <td>$groupname</td>
                            <td>$t[status]</td>
                            <td>$members</td>
                            <td>$ip $platform</td>
                            <td>
                                <button class='btn btn-info' onClick=\"$script\">Edit</button>
                            </td>
                        </tr>";
                }
            ?>
        </table>
        <div class="mb-3">
            <button class="btn btn-info" onclick="window.location='?display=register'">Add New Team</button>
            <button class="btn btn-success"
                onclick="confirmAction('?action=updatewaiting', 'Are you sure that for all Waiting Teams, you wish to set the status to Normal?');">Set Status to 'Normal' for all 'Waiting' Teams</button>
        </div>
        <?php
        $urlargs = "display=adminteam";
        $totalpages = max(1, ceil($totalCount / $limit));
        $currentPage = $page;

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
        ?>
    </div>

    <script>
        function confirmAction(url, message) {
            if (confirm(message)) {
                window.location = url;
            }
        }

        function toggleSections(showId, hideId) {
            document.getElementById(showId).style.display = "block";
            document.getElementById(hideId).style.display = "none";
        }
    </script>

    <div id="teamedit" style="display:none;">
        <form action="?action=updateteam" method="post">
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="5">
                        <h3>Administrator Options: Update Team Data</h3>
                    </th>
                </tr>
                <tr>
                    <th class="table-info" colspan="2">Team Information (Compulsory)</th>
                    <th></th>
                    <th class="table-info" colspan="2">Team Member 1 (Compulsory)</th>
                </tr>
                <tr>
                    <td class="table-info">Team Name</td>
                    <td>
                        <input class="form-control" id="update_teamname" name="update_teamname">
                    </td>
                    <td></td>
                    <td class="table-info">Full Name</td>
                    <td>
                        <input class="form-control" id="update_name1" name="update_name1">
                    </td>
                </tr>
                <tr>
                    <td class="table-info">Team Name</td>
                    <td><input class="form-control" id="update_teamname2" name="update_teamname2"></td>
                    <td></td>
                    <td class="table-info">Roll Number</td>
                    <td><input class="form-control" id="update_roll1" name="update_roll1"></td>
                </tr>
                <tr>
                    <td class="table-info">Password</td>
                    <td>
                        <input class="form-control" id="update_pass" name="update_pass" placeholder="**********">
                    </td>
                    <td></td>
                    <td class="table-info">Branch</td>
                    <td>
                        <input class="form-control" id="update_branch1" name="update_branch1">
                    </td>
                </tr>
                <tr>
                    <td class="table-info">Score</td>
                    <td>
                        <input class="form-control" id="update_score" name="update_score" disabled="disabled">
                    </td>
                    <td></td>
                    <td class="table-info">Email Address</td>
                    <td>
                        <input class="form-control" id="update_email1" name="update_email1">
                    </td>
                </tr>
                <tr>
                    <td class="table-info">Solved</td>
                    <td>
                        <input class="form-control" id="update_solved" name="update_solved" title="Solved Problem IDs separated by Commas">
                    </td>
                    <td></td>
                    <td class="table-info">Phone Number</td>
                    <td>
                        <input class="form-control" id="update_phone1" name="update_phone1">
                    </td>
                </tr>
                <tr>
                    <td class="table-info">Status</td>
                    <td>
                        <select class="form-select" id="update_status" name="update_status">
                            <option>Waiting</option>
                            <option>Normal</option>
                            <option>Admin</option>
                            <option>Suspend</option>
                            <option>Delete</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="table-info">Group Name</td>
                    <td>
                        <select class="form-select" id="update_gid" name="update_gid">
                            <option value="0">Unknown Group</option>
                            <?php
                            $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx < 3;");
                            while ($row = mysqli_fetch_assoc($data)) {
                                echo "<option value=\"$row[gid]\">$row[groupname]</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th colspan="5"></th>
                </tr>
                <tr>
                    <th class="table-info" colspan="2">Team Member 2 (Optional)</th>
                    <th></th>
                    <th class="table-info" colspan="2">Team Member 3 (Optional)</th>
                </tr>
                <tr>
                    <td class="table-info">Full Name</td>
                    <td><input class="form-control" id="update_name2" name="update_name2"></td>
                    <td></td>
                    <td class="table-info">Full Name</td>
                    <td><input class="form-control" id="update_name3" name="update_name3"></td>
                </tr>
                <tr>
                    <td class="table-info">Roll Number</td>
                    <td><input class="form-control" id="update_roll2" name="update_roll2"></td>
                    <td></td>
                    <td class="table-info">Roll Number</td>
                    <td><input class="form-control" id="update_roll3" name="update_roll3"></td>
                </tr>
                <tr>
                    <td class="table-info">Branch</td>
                    <td><input class="form-control" id="update_branch2" name="update_branch2"></td>
                    <td></td>
                    <td class="table-info">Branch</td>
                    <td><input class="form-control" id="update_branch3" name="update_branch3"></td>
                </tr>
                <tr>
                    <td class="table-info">Email Address</td>
                    <td><input class="form-control" id="update_email2" name="update_email2"></td>
                    <td></td>
                    <td class="table-info">Email Address</td>
                    <td><input class="form-control" id="update_email3" name="update_email3"></td>
                </tr>
                <tr>
                    <td class="table-info">Phone Number</td>
                    <td><input class="form-control" id="update_phone2" name="update_phone2"></td>
                    <td></td>
                    <td class="table-info">Phone Number</td>
                    <td><input class="form-control" id="update_phone3" name="update_phone3"></td>
                </tr>
            </table>
            <div class="mb-3">
                <input type="hidden" id="update_tid" name="update_tid">
                <button class="btn btn-outline-success" type="submit">Update Team Data</button>
                <button class="btn btn-outline-danger" type="button" onclick="toggleSections('teamlist', 'teamedit');">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function toggleSections(showId, hideId) {
            document.getElementById(showId).style.display = "block";
            document.getElementById(hideId).style.display = "none";
        }
    </script>
</div>