<div>
    <div class="mb-3">
        <form action="?action=updateaccount" method="post">
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="2">
                        <h3>Administrator Options : Account Settings</h3>
                    </th>
                </tr>
                <tr>
                    <th class="table-info">Multiple Login</th>
                    <td>
                        <select class="form-select" id="contest_multilogin" name="contest_multilogin">
                            <option value="0" <?php if ($admin["multilogin"] == 0)
                                echo "selected"; ?>>Not Allowed
                            </option>
                            <option value="1" <?php if ($admin["multilogin"] == 1)
                                echo "selected"; ?>>Allowed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Registration Mode</th>
                    <td>
                        <select class="form-select" id="contest_regautoauth" name="contest_regautoauth">
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
                    <td colspan="2">
                        <span>
                            In case you make Authorization necessary, a team will not be able to login (after
                            registration) until an Administrator manually authorizes that account. This is useful in
                            cases when the validity of the data provided during registration is to be confirmed.
                        </span>
                    </td>
                </tr>
            </table>
            <script>
                document.getElementById("contest_multilogin").value = <?php echo $admin["multilogin"]; ?>;
                document.getElementById("contest_regautoauth").value = <?php echo $admin["regautoauth"]; ?>;
            </script>
            <button class="btn btn-outline-success" type="submit">Update Account Settings</button>
        </form>
    </div>

    <div class="mb-3">
        <form action="?action=updatecontest" method="post">
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="2">
                        <h3>Administrator Options : Contest Settings</h3>
                    </th>
                </tr>
                <tr>
                    <th class="table-info">Contest Mode</th>
                    <td>
                        <select class="form-select" id="contest_mode" name="contest_mode">
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
                    <th class="table-info">Contest End Time</th>
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
                    <th class="table-info">Set Contest End Time</th>
                    <td>
                        <input class="form-control" id="contest_endtime" name="contest_endtime" placeholder="Enter Number of Minutes from Now">
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Incorrect Submission Penalty</th>
                    <td>
                        <?php echo $admin["penalty"] ?> minute(s)
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Set Incorrect Submission Penalty</th>
                    <td>
                        <input class="form-control" name="contest_penalty" placeholder="Enter Penalty in Minutes">
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Ajax Refresh Rate</th>
                    <td>
                        <?php echo $admin["ajaxrr"] ?> second(s)
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Set Ajax Refresh Rate</th>
                    <td>
                        <input class="form-control" name="contest_ajaxrr" placeholder="Enter Refresh Rate in Seconds">
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span>Setting a Refresh Rate of Zero (0) will disable Ajax.</span>
                    </td>
                </tr>
            </table>
            <button class="btn btn-outline-success" type="submit">Update Contest Settings</button>
        </form>
    </div>

    <div class="mb-3">
        <form action="?action=updatestyle" method="post">
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="2">
                        <h3>Administrator Options : Style Settings</h3>
                    </th>
                </tr>
                <tr title="Number of submissions to be displayed in the 'My Submissions' box.">
                    <th class="table-info">Personal Submissions (Box)</th>
                    <td>
                        <input class="form-control" name="contest_mysublist" value="<?php echo $admin["mysublist"]; ?>">
                    </td>
                </tr>
                <tr title="Number of submissions to be displayed in the 'Latest Submissions' box.">
                    <th class="table-info">Latest Submissions (Box)</th>
                    <td>
                        <input class="form-control" name="contest_allsublist" value="<?php echo $admin["allsublist"]; ?>">
                    </td>
                </tr>
                <tr title="Number of top teams to be displayed in the 'Current Rankings' box.">
                    <th class="table-info">Current Rankings (Box)</th>
                    <td>
                        <input class="form-control" name="contest_ranklist" value="<?php echo $admin["ranklist"]; ?>">
                    </td>
                </tr>
                <tr title="Number of private clarifications to be displayed in the 'My Submissions' box.">
                    <th class="table-info">Private Clarifications (Box)</th>
                    <td>
                        <input class="form-control" name="contest_clarprivate" value="<?php echo $admin["clarprivate"]; ?>">
                    </td>
                </tr>
                <tr title="Number of public clarifications to be displayed in the 'Public Submissions' box.">
                    <th class="table-info">Public Clarifications (Box)</th>
                    <td><input class="form-control" name="contest_clarpublic"
                            value="<?php echo $admin["clarpublic"]; ?>"></td>
                </tr>
                <tr title="Number of clarifications to be displayed on the Clarifications page at one page.">
                    <th class="table-info">Clarifications (Page)</th>
                    <td>
                        <input class="form-control" name="contest_clarpage" value="<?php echo $admin["clarpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of submissions to be displayed on the Submission Statistics page at one page.">
                    <th class="table-info">Submission Statistics (Page)</th>
                    <td>
                        <input class="form-control" name="contest_substatpage" value="<?php echo $admin["substatpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of teams to be displayed on the Current Rankings page at one page.">
                    <th class="table-info">Current Rankings (Page)</th>
                    <td>
                        <input class="form-control" name="contest_rankpage" value="<?php echo $admin["rankpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of teams to be displayed on the Problem Settings page at one time.">
                    <th class="table-info">Problem Settings (Page)</th>
                    <td>
                        <input class="form-control" class="form-control" name="contest_probpage" value="<?php echo $admin["probpage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of teams to be displayed on the Team Settings page at one time.">
                    <th class="table-info">Team Settings (Page)</th>
                    <td>
                        <input class="form-control" class="form-control" class="form-control" name="contest_teampage" value="<?php echo $admin["teampage"]; ?>">
                    </td>
                </tr>
                <tr title="Number of requests to be displayed on the Access Log page at one page.">
                    <th class="table-info">Access Logs (Page)</th>
                    <td>
                        <input class="form-control" name="contest_logpage" value="<?php echo $admin["logpage"]; ?>">
                    </td>
                </tr>
            </table>
            <button class="btn btn-outline-success" type="submit">Update Style Settings</button>
        </form>
    </div>
</div>