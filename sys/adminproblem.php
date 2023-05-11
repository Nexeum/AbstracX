<?php
global $invalidchars_js, $extension, $admin, $execoptions;
$langlist1 = "";
$langlist2 = "";
$langlist3 = "";
foreach ($extension as $lang => $ext) {
    if ($lang == "Brain") {
        $langlist1 .= "<option value='Brain'>Brainf**k</option>";
    } else {
        $langlist1 .= "<option>$lang</option>";
    }
}
foreach ($extension as $lang => $ext) {
    if ($lang == "Brain") {
        $langlist2 .= "<option value='Brain' selected='selected'>Brainf**k</option>";
    } else if ($lang == "Text") {
        $langlist2 .= "<option>Text</option>";
    } else {
        $langlist2 .= "<option selected='selected'>$lang</option>";
    }
}
foreach ($extension as $lang => $ext) {
    if ($lang != "Brain") {
        $langlist3 = ($langlist3 == "" ? $lang : $langlist3 . "," . $lang);
    }
}
$link = mysqli_connect("localhost", "root", "", "nexeum");

?>
<div style="text-align: center;">
    <div id='problist' style='display:block;'>
        <h2>Administrator Options : List of Problems</h2>
        <input type='button' value='Add New Problem'
               onClick="$('div#problist').slideUp(250); $('div#probupdate').slideUp(250); $('div#probadd').slideDown(250);"/>
        <input type='button' value='Make all Inactive'
               onClick="document.location='?action=problem-status&type=Inactive';"/>
        <input type='button' value='Make all Active' onClick="document.location='?action=problem-status&type=Active';"/>
        <?php
        $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM problems WHERE status!='Delete'");
        $totalResult = mysqli_fetch_array($totalQuery);
        $totalCount = $totalResult["total"];
        $limit = $admin["probpage"] ?? 25;
        $x = paginate("display=adminproblem", $totalCount, $limit);
        $page = $x[0];
        $pagenav = $x[1];
        echo "<br><br>" . $pagenav . "<br><br>";
        ?>
        <table>
            <tr>
                <th>Problem ID</th>
                <th>Problem Group</th>
                <th>Problem Name</th>
                <th>Problem Code</th>
                <th>Problem Type</th>
                <th>Time Limit</th>
                <th>Score</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
            <?php
            $data = mysqli_query($link, "SELECT * FROM problems WHERE status!='Delete' ORDER BY pid DESC LIMIT " . (($page - 1) * $limit) . "," . ($limit));
            while ($temp = mysqli_fetch_array($data)) {
                echo "<tr><td>$temp[pid]</td><td>" . ($temp["pgroup"]) . "</td><td><a href='?display=problem&pid=$temp[pid]'>" . stripslashes($temp["name"]) . "</a></td><td><a href='?display=problem&pid=$temp[pid]'>" . stripslashes($temp["code"]) . "</a></td><td>" . stripslashes($temp["type"]) . "</td><td>$temp[timelimit] sec</td><td>$temp[score]</td>";
                if ($temp["status"] == "Active") {
                    echo "<td><input type='button' value='Active' title='Click here to make this problem Inactive.' onClick=\"window.location='?action=makeinactive&pid=$temp[pid]';\"></td>";
                } else {
                    echo "<td><input type='button' value='Inactive' title='Click here to make this problem Active.' onClick=\"window.location='?action=makeactive&pid=$temp[pid]';\"></td>";
                }
                $script = "$('div#problist').slideUp(250); $('div#probupdate').slideDown(250); $('div#probadd').slideUp(250); ";
                $reset = "";
                foreach (array("pid", "name", "code", "type", "pgroup", "timelimit", "score", "status", "languages", "options") as $field) {
                    $reset .= "document.getElementById('update_$field').value='" . ($temp[$field]) . "';";
                }
                $reset .= "e = document.getElementById('update_langselect');";
                foreach (explode(",", $temp["languages"]) as $lang) {
                    $reset .= "for(i=0;i<e.options.length;i++) if(e.options[i].value=='$lang') e.options[i].selected=1; ";
                }
                $reset .= "$('a#download_statement').attr('href','?download=statement&pid=$temp[pid]'); $('a#download_image').attr('href','?image=$temp[pid]'); $('a#download_input').attr('href','?download=input&pid=$temp[pid]'); $('a#download_output').attr('href','?download=output&pid=$temp[pid]');";
                $script .= "$('input#update_reset').attr('onClick','" . addslashes($reset) . "');";
                echo "<td><input type='button' value='Edit' onClick=\"$script $reset\"></td></tr>\n";
            }
            ?>
        </table><?php echo "<br>" . $pagenav; ?></div>

    <div id='probupdate' style='display:none;'>
        <h2>Administrator Options : Update Problem Data</h2>
        <form action='?action=updateproblem' method='post' enctype='multipart/form-data'>
            <table>
                <tr>
                    <th style="width=23%">Problem Name</th>
                    <td style="width=23%"><label for='update_name'></label><input tabindex=1 id='update_name'
                                                                                  name='update_name'></td>
                    <td rowspan=8></td>
                    <th style="width=23%">Problem Status</th>
                    <td style='text-align:left;'>
                        <label for='update_status'></label><select tabindex=7 id='update_status' name='update_status'
                                                                   style='width:100%;'>
                            <option value='Active'>Active</option>
                            <option value='Inactive'>Inactive</option>
                            <option>Delete</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Problem Code</th>
                    <td><label for='update_code'></label><input tabindex=2 id='update_code' name='update_code'></td>
                    <th><a id='download_statement'>Problem Statement</a></th>
                    <td><input tabindex=8 type='file' name='update_file_statement' style='width:100%;'></td>
                </tr>
                <tr>
                    <th>Points</th>
                    <td><label for='update_score'></label><input tabindex=3 id='update_score' name='update_score'></td>
                    <th><a id='download_image'>Problem Image</a></th>
                    <td><input tabindex=9 type='file' name='update_file_image' style='width:100%;'></td>
                </tr>
                <tr>
                    <th>Problem Type</th>
                    <td><label for='update_type'></label><input tabindex=4 id='update_type' name='update_type'></td>
                    <th><a id='download_input'>Problem Input</a></th>
                    <td><input tabindex=10 type='file' name='update_file_input' style='width:100%;'></td>
                </tr>
                <tr>
                    <th>Problem Group</th>
                    <td><label for='update_pgroup'></label><input tabindex=5 id='update_pgroup' name='update_pgroup'>
                    </td>
                    <th><a id='download_output'>Problem Output</a></th>
                    <td><input tabindex=11 type='file' name='update_file_output' style='width:100%;'></td>
                </tr>
                <tr>
                    <th rowspan=3>Languages Allowed</th>
                    <td rowspan=3>
                        <input type='hidden' name='update_languages' value='' id='update_languages'>
                        <select tabindex=6
                                onChange="str=''; for(i=0;i<this.options.length;i++) if(this.options[i].selected) str+=((str!='')?',':'')+this.options[i].value; document.getElementById('update_languages').value=str; "
                                id='update_langselect' multiple='multiple' style='width:100%;'
                                title='Use CTRL or SHIFT to select multiple items.'><?php echo $langlist1; ?></select>
                    </td>
                    <th>Time Limit (sec)</th>
                    <td><label for='update_timelimit'></label><input tabindex=12 id='update_timelimit'
                                                                     name='update_timelimit' style='width:100%;'></td>
                </tr>
                <tr>
                    <th>Special Options</th>
                    <td>
                        <label for='update_options'></label><select name='update_options' id='update_options'
                                                                    tabindex=13>
                            <?php foreach ($execoptions as $key => $value) {
                                echo "<option value='$key'>$value</option>";
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan=5>
                        <span style='font-size:11px'>
		                    In case any of the File Input Fields are left empty, it will cause the original files to be retained. However, the Text Input Fields, if left empty, will actually be set to NULL.
		                </span>
                    </td>
                </tr>
            </table>
            <br><input type='submit' value='Update Problem Data'/>
            <input type='button' value='Reset' id='update_reset'/>
            <input type='button' value='Cancel'
                   onClick="$('div#problist').slideDown(250); $('div#probupdate').slideUp(250); $('div#probadd').slideUp(250);"/>
            <input type='hidden' name='update_pid' id='update_pid'>
        </form>
    </div>

    <script>
        function validate_makeproblem() {
            let str = "";
            if ($("input[name='make_name']").val() === "") {
                str += "Problem name not provided.\n";
            }
            if ($("input[name='make_code']").val() === "") {
                str += "Problem code not provided.\n";
            }
            if ($("input[name='make_score']").val() == "") {
                str += "Problem points not provided.\n";
            }
            if ($("input[name='make_pgroup']").val() == "") {
                str += "Problem group not provided.\n";
            }
            if ($("input[name='make_timelimit']").val() == "") {
                str += "Problem timelimit not provided.\n";
            }
            if ($("input[name='make_languages']").val() == "") {
                str += "No Languages selected for this problem.\n";
            }
            if ($("input[name='make_file_statement']").val() == "") {
                str += "Problem statement file not provided.\n";
            }
            if ($("input[name='make_file_input']").val() == "") {
                str += "Problem input file not provided.\n";
            }
            if ($("input[name='make_file_output']").val() == "") {
                str += "Problem output file not provided.\n";
            }

            if ($("input[name='make_name']").val().match(/<?php echo $invalidchars_js; ?>/)!=null) str+="Problem name contains invalid characters.\n";
                if ($("input[name='make_code']").val().match(/<?php echo $invalidchars_js; ?>/)!=null) str+="Problem code contains invalid characters.\n";
                    if ($("input[name='make_score']").val().match(/[^0-9]/) != null) str += "Problem score contains invalid characters.\n";
            if ($("input[name='make_type']").val().match(/<?php echo $invalidchars_js; ?>/)!=null) str+="Problem type contains invalid characters.\n";
                if ($("input[name='make_timelimit']").val().match(/[^0-9]/) != null) str += "Problem timelimit contains invalid characters.\n";
            if (str === "") return true;
            alert(str);
            return false;
        }
    </script>
    <div id='probadd' style='display:none;'>
        <h2>Administrator Options : Add New Problem</h2>
        <form action='?action=makeproblem' method='post' enctype='multipart/form-data'
              onsubmit="return validate_makeproblem()">
            <table>
                <tr>
                    <th style="width=23%">Problem Name</th>
                    <td style="width=23%"><label>
                            <input tabindex=1 name='make_name'>
                        </label></td>
                    <td rowspan=8></td>
                    <th style="width=23%">Problem Status</th>
                    <td style="width=30%"><label>
                            <input disabled='disabled' value='Inactive' style='width:100%;'>
                        </label></td>
                </tr>
                <tr>
                    <th>Problem Code</th>
                    <td><label>
                            <input tabindex=2 name='make_code'>
                        </label></td>
                    <th>Problem Statement</th>
                    <td><input tabindex=7 type='file' name='make_file_statement' style='width:100%;'/></td>
                </tr>
                <tr>
                    <th>Points</th>
                    <td><label>
                            <input tabindex=3 name='make_score'>
                        </label></td>
                    <th>Problem Image</th>
                    <td><input tabindex=8 type='file' name='make_file_image' style='width:100%;'/></td>
                </tr>
                <tr>
                    <th>Problem Type</th>
                    <td><label>
                            <input tabindex=4 name='make_type'>
                        </label></td>
                    <th>Problem Input</th>
                    <td><input tabindex=9 type='file' name='make_file_input' style='width:100%;'/></td>
                </tr>
                <tr>
                    <th>Problem Group</th>
                    <td><label>
                            <input tabindex=5 name='make_pgroup'>
                        </label></td>
                    <th>Problem Output</th>
                    <td><input tabindex=10 type='file' name='make_file_output' style='width:100%;'/></td>
                </tr>
                <tr>
                    <th rowspan=3>Languages Allowed</th>
                    <td rowspan=3>
                        <input type='hidden' name='make_languages' value='<?php echo $langlist3; ?>'
                               id='make_languages'>
                        <select tabindex=6
                                onChange="str=''; for(i=0;i<this.options.length;i++) if(this.options[i].selected) str+=((str!='')?',':'')+this.options[i].value; document.getElementById('make_languages').value=str;"
                                multiple='multiple' style='width:100%;'
                                title='Use CTRL or SHIFT to select multiple items.'><?php echo $langlist2; ?></select>
                    </td>
                    <th>Time Limit (sec)</th>
                    <td><label>
                            <input tabindex=11 name='make_timelimit' style='width:100%;'>
                        </label></td>
                </tr>
                <tr>
                    <th>Special Options</th>
                    <td><label>
                            <select name='make_options' tabindex=12>
                                <?php foreach ($execoptions as $key => $value) echo "<option value='$key'>$value</option>"; ?>
                            </select>
                        </label></td>
                </tr>
                <tr>
                    <td colspan=2><span style='font-size:11px;'>By default, all languages except for Text are enabled. Use CTRL or SHIFT to select multiple languages.</span>
                    </td>
                </tr>
                <tr>
                    <td colspan=5>
                        <div class="small">
                            The values of all text fields must be a combination of upto 30 characters (single and double
                            quotes are not allowed).
                            <br>Please do not put the name of the problem in the Problem Statement File.
                            <br>You may use any HTML tags in the Problem Statement. The '\n' character will
                            automatically be replaced by '&lt;br&gt;'.
                            <br>If you have uploaded an image (only one jpeg/gif/png, max 3MB, allowed per problem), you
                            must specify its position by inserting the (custom) "&lt;image /&gt;" tag somewhere in your
                            code. It will be replaced by the (proper) &lt;img&gt; tag with the src attribute set
                            appropriately.
                            <br>The Problem Statement, Input and Output Files must be of text format and can have a
                            maximum size of 3MB.
                        </div>
                    </td>
                </tr>
            </table>
            <br><input type='submit' value='Create Problem'/>
            <input type='reset' value='Reset'/>
            <input type='button' value='Cancel'
                   onClick="$('div#problist').slideDown(250); $('div#probupdate').slideUp(250); $('div#probadd').slideUp(250);"/>
        </form>
    </div>

</div>