<div style="display: flex; justify-content: center;">
    <h2>Administrator Options : Data Commitment</h2>
    <table class='warn'>
        <tr>
            <td>
                The Data Commitment procedure is designed to be used after contests to obtain a more permanent record of
                the important data that is scattered in the various tables of the Database (A new table is created in
                the Database for the Purpose). Only 'Active' Problems and 'Normal' Teams will be considered in the
                process.
            </td>
        </tr>
        <tr>
            <td>
                <b>WARNING: </b>
                Data Commitment can be a costly and slow process, and should be performed only when it
                is known that network traffic is minimal, thereby allowing the full resources of the server to be
                dedicated to this task.
            </td>
        </tr>
    </table>
    <br>
    <form action='?action=commitdata' method='post'
        onSubmit="return confirm('Are you sure you wish to perform a Data Commitment operation?');">
        <label>
            <input name='recordname' placeholder='Enter Record Name Here'>
        </label>
        <input type='submit' value='Commit Data'>
    </form>
    <br><br>
    <h2>Administrator Options : Committed Data</h2>

    <table>
        <tr>
            <th>Record Name</th>
            <th>Date & Time</th>
            <th>Status</th>
            <th>Options</th>
        </tr>
        <?php
        $link = mysqli_connect("localhost", "root", "", "nexeum");
        $data = mysqli_query($link, "SHOW TABLES");
        $tables = [];

        while ($temp = mysqli_fetch_row($data)) {
            $tables[] = $temp[0];
        }

        $confirmMessage = "Are you sure you wish to perform this action?";

        foreach ($tables as $table) {
            if (str_starts_with($table, 'backup_')) {
                $system = mysqli_query($link, "SELECT * FROM $table WHERE info='system'");
                $system = mysqli_fetch_array($system);

                $name = stripslashes($system["name"]);
                $id = date("d M Y, H:i:s", $system["id"]);
                $score = $system["score"];

                $isActive = $score == "Active";
                $isInactive = $score == "Inactive";

                echo "
            <form action='?action=commitupdate' method='post' onsubmit=\"return confirm('$confirmMessage');\">
                <input type='hidden' name='tablename' value='$table'>
                <tr>
                    <td><input title='Modify to Rename' name='recordname' value=\"$name\"></td>
                    <td>$id</td>
                    <td>
                        <select name='status'>
                            <option " . ($isActive ? "selected='selected'" : "") . ">Active</option>
                            <option " . ($isInactive ? "selected='selected'" : "") . ">Inactive</option>
                            <option>Delete</option>
                        </select>
                    </td>
                    <td><input type='submit' value='Update'></td>
                </tr>
            </form>
            ";
            }
        }
        ?>
    </table>
</div>