<?php
global $invalidchars;
$link = mysqli_connect("localhost", "root", "", "nexeum");

?>
<script>
    function validate_register() {
        let str = "";
        if ($("input[name='reg_teamname']").val() == "") {
            str += "Team Name not provided.\n";
        }
        if ($("input[name='reg_teamname']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/i) != null) {
            str += "Team Name contains invalid characters.\n";
        }
        if ($("input[name='reg_pass1']").val() == "") {
            str += "Password-1 not provided.\n";
        }
        if ($("input[name='reg_pass2']").val() == "") {
            str += "Password-2 not provided.\n";
        }
        if ($("input[name='reg_name1']").val() == "") {
            str += "Name-1 not provided.\n";
        }
        if ($("input[name='reg_roll1']").val() == "") {
            str += "Roll-Number-1 not provided.\n";
        }
        if ($("input[name='reg_branch1']").val() == "") {
            str += "Branch-1 not provided.\n";
        }
        if ($("input[name='reg_email1']").val() == "") {
            str += "EMail-Address-1 not provided.\n";
        }
        if ($("input[name='reg_phone1']").val() == "") {
            str += "Phone-Number-1 not provided.\n";
        }

        if ($("input[name='reg_name1']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/i) != null) {
            str += "Name-1 contains invalid characters.\n";
        }
        if ($("input[name='reg_roll1']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Roll-Number-1 contains invalid characters.\n";
        }
        if ($("input[name='reg_branch1']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Branch-1 contains invalid characters.\n";
        }
        if ($("input[name='reg_email1']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "EMail-Address-1 contains invalid characters.\n";
        }
        if ($("input[name='reg_phone1']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Phone-1 contains invalid characters.\n";
        }
        if ($("input[name='reg_name2']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Name-2 contains invalid characters.\n";
        }
        if ($("input[name='reg_roll2']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Roll-Number-2 contains invalid characters.\n";
        }
        if ($("input[name='reg_branch2']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Branch-2 contains invalid characters.\n";
        }
        if ($("input[name='reg_email2']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "EMail-Address-2 contains invalid characters.\n";
        }
        if ($("input[name='reg_phone2']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Phone-2 contains invalid characters.\n";
        }
        if ($("input[name='reg_name3']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Name-3 contains invalid characters.\n";
        }
        if ($("input[name='reg_roll3']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Roll-Number-3 contains invalid characters.\n";
        }
        if ($("input[name='reg_branch3']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Branch-3 contains invalid characters.\n";
        }
        if ($("input[name='reg_email3']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "EMail-Address-3 contains invalid characters.\n";
        }
        if ($("input[name='reg_phone3']").val().match(/<?php echo preg_replace("/\n/", "\\n", $invalidchars); ?>/) != null) {
            str += "Phone-3 contains invalid characters.\n";
        }
        if (str == "") {
            return true;
        }
        alert(str);
        return false;
    }
</script>
<form action='?action=register' method='post' onsubmit="return validate_register()">
    <table class="table table-borderless">
        <tr class="table-primary">
            <th colspan="4">
                <h3>Team Registeration</h3>
            </th>
        </tr>
        <tr>
            <th class="table-info">Team Information (Compulsary)</th>
            <td class='vdiv'></td>
            <th class="table-info">Team Member 1 (Compulsary)</th>
        </tr>
        <tr>
            <td class="table-info">Team Name</td>
            <td>
                <input class="form-control" name='reg_teamname'>
            </td>
            <td class="table-info">Full Name</td>
            <td><input class="form-control"  name='reg_name1'></td>
        </tr>
        <tr>
            <td class="table-info">Password</td>
            <td><input class="form-control"  name='reg_pass1' type='password'></td>
            <td class="table-info">Roll Number</td>
            <td><input class="form-control"  name='reg_roll1'></td>
        </tr>
        <tr>
            <td class="table-info">Retype Password</td>
            <td><input class="form-control" class="form-control"  name='reg_pass2' type='password'></td>
            <td class="table-info">Branch</td>
            <td><input class="form-control"  name='reg_branch1'></td>
        </tr>
        <tr>
            <td class="table-info">Team Group</td>
            <td>
                <select class="form-select" name='reg_gid'>
                    <?php
                    $data = mysqli_query($link, "SELECT * FROM groups WHERE statusx<3;");
                    while ($row = mysqli_fetch_array($data)) {
                        echo "<option value=$row[gid]>$row[groupname]</option>";
                    }
                    ?>
                </select>
            </td>
            <td class="table-info">Email Address</td>
            <td><input class="form-control" name='reg_email1'></td>
        </tr>
        <tr>
            <td colspan="2"><span>All values (except for the Password) must be a combination of upto 30 characters characters (single and double quotes are not allowed).</span>
            </td>
            <td class="table-info">Phone Number</td>
            <td><input class="form-control"  name='reg_phone1'></td>
        </tr>
        <tr>
            <td class='hdiv'></td>
        </tr>
        <tr>
            <th class="table-info">Team Member 2 (Optional)</th>
            <td class='vdiv'></td>
            <th class="table-info">Team Member 3 (Optional)</th>
        </tr>
        <tr>
            <td class="table-info">Full Name</td>
            <td><input class="form-control" name='reg_name2'></td>
            <td class="table-info">Full Name</td>
            <td><input class="form-control" name='reg_name3'></td>
        </tr>
        <tr>
            <td class="table-info">Roll Number</td>
            <td><input class="form-control" name='reg_roll2'></td>
            <td class="table-info">Roll Number</td>
            <td><input class="form-control" name='reg_roll3'></td>
        </tr>
        <tr>
            <td class="table-info">Branch</td>
            <td><input class="form-control" name='reg_branch2'></td>
            <td class="table-info">Branch</td>
            <td><input class="form-control" name='reg_branch3'></td>
        </tr>
        <tr>
            <td class="table-info">Email Address</td>
            <td><input class="form-control" name='reg_email2'></td>
            <td class="table-info">Email Address</td>
            <td><input class="form-control" name='reg_email3'></td>
        </tr>
        <tr>
            <td class="table-info">Phone Number</td>
            <td><input class="form-control" name='reg_phone2'></td>
            <td class="table-info">Phone Number</td>
            <td><input class="form-control" name='reg_phone3'></td>
        </tr>
    </table>
    <br>
    <button class="btn btn-outline-info" type='submit'>Submit Data</button>
</form>