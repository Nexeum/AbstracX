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
        if ($("input[name='reg_teamname']").val().match(/<?php echo preg_replace("\n", "\\n", $invalidchars); ?>/i) != null) {
            str += "Team Name contains invalid characters.\n";
        }
        if ($("input[name='reg_pass1']").val() == "") {
            str += "Password-1 not provided.\n";
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



        if ($("input[name='reg_name1']").val().match(/<?php echo preg_replace("\n", "\\n", $invalidchars); ?>/i) != null) str += "Name-1 contains invalid characters.\n";
        if ($("input[name='reg_roll1']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Roll-Number-1 contains invalid characters.\n";
        if ($("input[name='reg_branch1']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Branch-1 contains invalid characters.\n";
        if ($("input[name='reg_email1']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "EMail-Address-1 contains invalid characters.\n";
        if ($("input[name='reg_phone1']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Phone-1 contains invalid characters.\n";

        if ($("input[name='reg_name2']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Name-2 contains invalid characters.\n";
        if ($("input[name='reg_roll2']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Roll-Number-2 contains invalid characters.\n";
        if ($("input[name='reg_branch2']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Branch-2 contains invalid characters.\n";
        if ($("input[name='reg_email2']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "EMail-Address-2 contains invalid characters.\n";
        if ($("input[name='reg_phone2']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Phone-2 contains invalid characters.\n";

        if ($("input[name='reg_name3']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Name-3 contains invalid characters.\n";
        if ($("input[name='reg_roll3']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Roll-Number-3 contains invalid characters.\n";
        if ($("input[name='reg_branch3']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Branch-3 contains invalid characters.\n";
        if ($("input[name='reg_email3']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "EMail-Address-3 contains invalid characters.\n";
        if ($("input[name='reg_phone3']").val().match(/<?php echo preg_replace("\n","\\n",$invalidchars); ?>/) != null) str += "Phone-3 contains invalid characters.\n";
        if (str == "") {
            return true;
        }
        alert(str);
        return false;
    }
</script>
<center>
    <form action='?action=register' method='post' onsubmit="return validate_register()">
        <h2>Team Registeration</h2>
        <table style="border=0">
            <tr>
                <th colspan=2>Team Information (Compulsary)</th>
                <td class='vdiv' rowspan=6></td>
                <th colspan=2>Team Member 1 (Compulsary)</th>
            </tr>
            <tr>
                <td style="width=25%">Team Name</td>
                <td style="width=25%"><input tabindex=1 name='reg_teamname'></td>
                <td style="width=25%">Full Name</td>
                <td style="width=25%"><input tabindex=4 name='reg_name1'></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input tabindex=2 name='reg_pass1' type='password'></td>
                <td>Roll Number</td>
                <td><input tabindex=5 name='reg_roll1'></td>
            </tr>
            <tr>
                <td>Retype Password</td>
                <td><input tabindex=3 name='reg_pass2' type='password'></td>
                <td>Branch</td>
                <td><input tabindex=6 name='reg_branch1'></td>
            </tr>
            <tr>
                <td colspan=2 rowspan=1><span style='font-size:10px;'>All values (except for the Password) must be a combination of upto 30 characters characters (single and double quotes are not allowed).</span>
                </td>
                <td>Email Address</td>
                <td><input tabindex=7 name='reg_email1'></td>
            </tr>
            <tr>
                <td>Team Group</td>
                <td>
                    <select name='reg_gid' style='width:100%;'>
                        <?php
	                        $data = mysqli_query($link,"SELECT * FROM groups WHERE statusx<3;");
	                        while($row = mysqli_fetch_array($data)) {
                                echo "<option value=$row[gid]>$row[groupname]</option>";
                            }
                    ?>
                    </select>
                </td>
                <td>Phone Number</td>
                <td><input tabindex=8 name='reg_phone1'></td>
            </tr>
            <tr>
                <td class='hdiv' colspan=5></td>
            </tr>
            <tr>
                <th colspan=2>Team Member 2 (Optional)</th>
                <td class='vdiv' rowspan=6></td>
                <th colspan=2>Team Member 3 (Optional)</th>
            </tr>
            <tr>
                <td>Full Name</td>
                <td><input tabindex=10 name='reg_name2'></td>
                <td>Full Name</td>
                <td><input tabindex=16 name='reg_name3'></td>
            </tr>
            <tr>
                <td>Roll Number</td>
                <td><input tabindex=11 name='reg_roll2'></td>
                <td>Roll Number</td>
                <td><input tabindex=17 name='reg_roll3'></td>
            </tr>
            <tr>
                <td>Branch</td>
                <td><input tabindex=12 name='reg_branch2'></td>
                <td>Branch</td>
                <td><input tabindex=18 name='reg_branch3'></td>
            </tr>
            <tr>
                <td>EMail Address</td>
                <td><input tabindex=13 name='reg_email2'></td>
                <td>EMail Address</td>
                <td><input tabindex=19 name='reg_email3'></td>
            </tr>
            <tr>
                <td>Phone Number</td>
                <td><input tabindex=14 name='reg_phone2'></td>
                <td>Phone Number</td>
                <td><input tabindex=20 name='reg_phone3'></td>
            </tr>
            <tr>
                <td colspan=5></td>
            </tr>
        </table>
        <br>
        <input type='submit' value='Submit Data'></form>
</center>