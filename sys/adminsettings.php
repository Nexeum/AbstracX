<div style="text-align: center">
    <h2>Administrator Options : Account Settings</h2>
    <div style="display: flex; justify-content: center;">
        <form action="?action=updateaccount" method="post">
            <table style="width: 80%">
                <tr>
                    <th>Multiple Login</th>
                    <td>
                        <select id="contest_multilogin" name="contest_multilogin" style="width:100%;">
                            <option value="0" <?php if ($admin["multilogin"] == 0)
                                echo "selected"; ?>>Not Allowed
                            </option>
                            <option value="1" <?php if ($admin["multilogin"] == 1)
                                echo "selected"; ?>>Allowed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th>Registration Mode</th>
                    <td>
                        <select id="contest_regautoauth" name="contest_regautoauth" style="width:100%;">
                            <option value="0" <?php if ($admin["regautoauth"] == 0)
                                echo "selected"; ?>>Authorization
                                Required</option>
                            <option value="1" <?php if ($admin["regautoauth"] == 1)
                                echo "selected"; ?>>Authorization not
                                Required</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <span style="font-size:11px;">
                            In case you make Authorization necessary, a team will not be able to login (after
                            registration) until an Administrator manually authorizes that account. This is useful in
                            cases when the validity of the data provided during registration is to be confirmed.
                        </span>
                    </td>
                </tr>
            </table>
            <br>
            <script>
                document.getElementById("contest_multilogin").value = <?php echo $admin["multilogin"]; ?>;
                document.getElementById("contest_regautoauth").value = <?php echo $admin["regautoauth"]; ?>;
            </script>
            <input type="submit" value="Update Account Settings">
        </form>
    </div>

    <br>
    <h2>Administrator Options : Contest Settings</h2>
    <div style="display: flex; justify-content: center;">
        <form action="?action=updatecontest" method="post">
            <table style="width: 80%">
                <tr>
                    <th>Contest Mode</th>
                    <td>
                        <select id="contest_mode" name="contest_mode" style="width:100%;">
                            <option <?php if ($admin["mode"] == "Lockdown")
                                echo "selected"; ?>>Lockdown</option>
                            <option <?php if ($admin["mode"] == "Disabled")
                                echo "selected"; ?>>Disabled</option>
                            <option <?php if ($admin["mode"] == "Passive")
                                echo "selected"; ?>>Passive</option>
                            <option <?php if ($admin["mode"] == "Active")
                                echo "selected"; ?>>Active</option>
                        </select>
                        <script>document.getElementById("contest_mode").value = "<?php echo $admin["mode"]; ?>";</script>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th>Contest End Time</th>
                    <td>
                        <?php
                        if ($admin["mode"] == "Active") {
                            $t = ($admin["endtime"] - time()) / 60;
                            echo floor($t / 60) . " hour(s) " . floor($t % 60) . " minute(s) from now";
                        } else {
                            echo "Not Applicable";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Set Contest End Time</th>
                    <td><input style="width:100%;" id="contest_endtime" name="contest_endtime"
                            placeholder="Enter Number of Minutes from Now"></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th>Incorrect Submission Penalty</th>
                    <td>
                        <?php echo $admin["penalty"] ?> minute(s)
                    </td>
                </tr>
                <tr>
                    <th>Set Incorrect Submission Penalty</th>
                    <td><input style="width:100%;" name="contest_penalty" placeholder="Enter Penalty in Minutes"></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th>Ajax Refresh Rate</th>
                    <td>
                        <?php echo $admin["ajaxrr"] ?> second(s)
                    </td>
                </tr>
                <tr>
                    <th>Set Ajax Refresh Rate</th>
                    <td><input style="width:100%;" name="contest_ajaxrr" placeholder="Enter Refresh Rate in Seconds">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><span style="font-size:11px;">Setting a Refresh Rate of Zero (0) will disable Ajax.</span></td>
                </tr>
            </table>
            <br>
            <input type="submit" value="Update Contest Settings">
        </form>
    </div>

    <br>
    <h2>Administrator Options : Style Settings</h2>
    <div style="display: flex; justify-content: center;">
        <form action="?action=updatestyle" method="post">
            <table style="width: 80%">
                <tr title="Number of submissions to be displayed in the 'My Submissions' box.">
                    <th>Personal Submissions (Box)</th>
                    <td><input style="width:100%;" name="contest_mysublist" value="<?php echo $admin["mysublist"]; ?>">
                    </td>
                </tr>
                <tr title="Number of submissions to be displayed in the 'Latest Submissions' box.">
                    <th>Latest Submissions (Box)</th>
                    <td><input style="width:100%;" name="contest_allsublist"
                            value="<?php echo $admin["allsublist"]; ?>"></td>
                </tr>
                <tr title="Number of top teams to be displayed in the 'Current Rankings' box.">
                    <th>Current Rankings (Box)</th>
                    <td><input style="width:100%;" name="contest_ranklist" value="<?php echo $admin["ranklist"]; ?>">
                    </td>
                </tr>
                <tr title="Number of private clarifications to be displayed in the 'My Submissions' box.">
                    <th>Private Clarifications (Box)</th>
                    <td><input style="width:100%;" name="contest_clarprivate"
                            value="<?php echo $admin["clarprivate"]; ?>"></td>
                </tr>
                <tr title="Number of public clarifications to be displayed in the 'Public Submissions' box.">
                    <th>Public Clarifications (Box)</th>
                    <td><input style="width:100%;" name="contest_clarpublic"
                            value="<?php echo $admin["clarpublic"]; ?>"></td>
                </tr>
                <tr title="Number of clarifications to be displayed on the Clarifications page at one page.">
                    <th>Clarifications (Page)</th>
                    <td><input style="width:100%;" name="contest_clarpage" value="<?php echo $admin["clarpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of submissions to be displayed on the Submission Statistics page at one page.">
                    <th>Submission Statistics (Page)</th>
                    <td><input style="width:100%;" name="contest_substatpage"
                            value="<?php echo $admin["substatpage"]; ?>"></td>
                </tr>
                <tr title="Number of teams to be displayed on the Current Rankings page at one page.">
                    <th>Current Rankings (Page)</th>
                    <td><input style="width:100%;" name="contest_rankpage" value="<?php echo $admin["rankpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of teams to be displayed on the Problem Settings page at one time.">
                    <th>Problem Settings (Page)</th>
                    <td><input style="width:100%;" name="contest_probpage" value="<?php echo $admin["probpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of teams to be displayed on the Team Settings page at one time.">
                    <th>Team Settings (Page)</th>
                    <td><input style="width:100%;" name="contest_teampage" value="<?php echo $admin["teampage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of requests to be displayed on the Access Log page at one page.">
                    <th>Access Logs (Page)</th>
                    <td><input style="width:100%;" name="contest_logpage" value="<?php echo $admin["logpage"]; ?>"></td>
                </tr>
            </table>
            <br>
            <input type="submit" value="Update Style Settings">
        </form>
    </div>
</div>