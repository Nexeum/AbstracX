<div>
    <div id="teamlist">
        <h2>Administrator Options: List of Teams</h2>
        <div>
            <input class="btn btn-info" type="button" value="Add New Team" onclick="window.location='?display=register'" />
            <input class="btn btn-success" type="button" value="Set Status to 'Normal' for all 'Waiting' Teams"
                onclick="confirmAction('?action=updatewaiting', 'Are you sure that for all Waiting Teams, you wish to set the status to Normal?');" />
        </div>
        <hr>
        <?php
            $link = mysqli_connect("localhost", "root", "", "nexeum");

            $totalQuery = mysqli_query($link, "SELECT COUNT(*) AS total FROM teams WHERE status != 'Delete'");
            $totalResult = mysqli_fetch_array($totalQuery);
            $totalCount = $totalResult["total"];
            $limit = $admin["teampage"] ?? 25;
            [$page, $pagenav] = paginate("display=adminteam", $totalCount, $limit);
            echo $pagenav . "<hr>";
        ?>
        <table class="adminteam">
            <tr>
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
                                <a href='?display=submissions&tid=$t[tid]'>$t[teamname]
                            </td>
                            <td>$groupname</td>
                            <td>$t[status]</td>
                            <td>$members</td>
                            <td>$ip $platform</td>
                            <td>
                                <input class='btn btn-info' type='button' value='Edit' onClick=\"$script\" />
                            </td>
                        </tr>";
                }
            ?>
        </table>
        <hr>
        <?php echo $pagenav; ?>
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
        <h2>Administrator Options: Update Team Data</h2>
        <form action="?action=updateteam" method="post">
            <table>
                <tr>
                    <th colspan="2">Team Information (Compulsory)</th>
                    <td class="vdiv" rowspan="8"></td>
                    <th colspan="2">Team Member 1 (Compulsory)</th>
                </tr>
                <tr>
                    <td>Team Name</td>
                    <td>
                        <input tabindex="1" id="update_teamname" name="update_teamname">
                    </td>
                    <td>Full Name</td>
                    <td>
                        <input tabindex="7" id="update_name1" name="update_name1">
                    </td>
                </tr>
                <tr>
                    <td>Team Name</td>
                    <td><input tabindex="2" id="update_teamname2" name="update_teamname2"></td>
                    <td>Roll Number</td>
                    <td><input tabindex="8" id="update_roll1" name="update_roll1"></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input tabindex="3" id="update_pass" name="update_pass" placeholder="**********">
                    </td>
                    <td>Branch</td>
                    <td>
                        <input tabindex="9" id="update_branch1" name="update_branch1">
                    </td>
                </tr>
                <tr>
                    <td>Score</td>
                    <td>
                        <input tabindex="4" id="update_score" name="update_score" disabled="disabled">
                    </td>
                    <td>Email Address</td>
                    <td>
                        <input tabindex="10" id="update_email1" name="update_email1">
                    </td>
                </tr>
                <tr>
                    <td>Solved</td>
                    <td>
                        <input tabindex="5" id="update_solved" name="update_solved" title="Solved Problem IDs separated by Commas">
                    </td>
                    <td>Phone Number</td>
                    <td>
                        <input tabindex="11" id="update_phone1" name="update_phone1">
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select tabindex="6" id="update_status" name="update_status">
                            <option>Waiting</option>
                            <option>Normal</option>
                            <option>Admin</option>
                            <option>Suspend</option>
                            <option>Delete</option>
                        </select>
                    </td>
                    <td rowspan="2" colspan="2"></td>
                </tr>
                <tr>
                    <td>Group Name</td>
                    <td>
                        <select id="update_gid" name="update_gid">
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
                    <th colspan="2">Team Member 2 (Optional)</th>
                    <td class="vdiv" rowspan="6"></td>
                    <th colspan="2">Team Member 3 (Optional)</th>
                </tr>
                <tr>
                    <td>Full Name</td>
                    <td><input tabindex="12" id="update_name2" name="update_name2"></td>
                    <td>Full Name</td>
                    <td><input tabindex="17" id="update_name3" name="update_name3"></td>
                </tr>
                <tr>
                    <td>Roll Number</td>
                    <td><input tabindex="13" id="update_roll2" name="update_roll2"></td>
                    <td>Roll Number</td>
                    <td><input tabindex="18" id="update_roll3" name="update_roll3"></td>
                </tr>
                <tr>
                    <td>Branch</td>
                    <td><input tabindex="14" id="update_branch2" name="update_branch2"></td>
                    <td>Branch</td>
                    <td><input tabindex="19" id="update_branch3" name="update_branch3"></td>
                </tr>
                <tr>
                    <td>Email Address</td>
                    <td><input tabindex="15" id="update_email2" name="update_email2"></td>
                    <td>Email Address</td>
                    <td><input tabindex="20" id="update_email3" name="update_email3"></td>
                </tr>
                <tr>
                    <td>Phone Number</td>
                    <td><input tabindex="16" id="update_phone2" name="update_phone2"></td>
                    <td>Phone Number</td>
                    <td><input tabindex="21" id="update_phone3" name="update_phone3"></td>
                </tr>
            </table>
            <hr>
            <div>
                <input type="hidden" id="update_tid" name="update_tid">
                <input type="submit" value="Update Team Data">
                <input type="button" value="Cancel" onclick="toggleSections('teamlist', 'teamedit');" />
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