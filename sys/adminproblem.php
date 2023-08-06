<?php
global $invalidchars_js, $extension, $admin, $execoptions;
$langlist1 = "";
$langlist2 = "";
$langlist3 = "";
foreach ($extension as $lang => $ext) {
    $langlist1 .= "<option>$lang</option>";
}
foreach ($extension as $lang => $ext) {
    $langlist2 .= "<option selected='selected'>$lang</option>";
}
foreach ($extension as $lang => $ext) {
    $langlist3 = ($langlist3 == "" ? $lang : $langlist3 . "," . $lang);
}
$link = mysqli_connect("localhost", "root", "", "nexeum");

?>

<div>
    <div id='problist'>
        <?php
        $totalQuery = mysqli_query($link, "SELECT count(*) as total FROM problems WHERE status!='Delete'");
        $totalResult = mysqli_fetch_array($totalQuery);
        $totalCount = $totalResult["total"];
        $limit = $admin["probpage"] ?? 25;
        $x = paginate("display=adminproblem", $totalCount, $limit);
        $page = $x[0];
        $pagenav = $x[1];
        ?>
        <table class="table table-borderless">
            <thead>
                <tr class="table-primary">
                    <th colspan='9'>
                        <h3>Administrator Options : List of Problems</h3>
                    </th>
                </tr>
                <tr class="table-info">
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
            </thead>
            <?php
            $data = mysqli_query($link, "SELECT * FROM problems WHERE status!='Delete' ORDER BY pid DESC LIMIT " . (($page - 1) * $limit) . "," . ($limit));
            while ($temp = mysqli_fetch_array($data)) {
                echo "<tr>
                <td>$temp[pid]</td>
                <td>" . ($temp["pgroup"]) . "</td>
                <td><a class='list-group-item' href='?display=problem&pid=$temp[pid]'>" . stripslashes($temp["name"]) . "</a></td>
                <td><a class='list-group-item' href='?display=problem&pid=$temp[pid]'>" . stripslashes($temp["code"]) . "</a></td>
                <td>" . stripslashes($temp["type"]) . "</td>
                <td>$temp[timelimit] sec</td>
                <td>$temp[score]</td>";
                if ($temp["status"] == "Active") {
                    echo "
                    <td>
                        <button class='btn btn-outline-success' title='Click here to make this problem Inactive.' onClick=\"window.location='?action=makeinactive&pid=$temp[pid]';\">Active</button></td>";
                } else {
                    echo "
                    <td>
                        <button class='btn btn-outline-danger' title='Click here to make this problem Active.' onClick=\"window.location='?action=makeactive&pid=$temp[pid]';\">Inactive</button></td>";
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
                echo "<td><button class='btn btn-outline-info' onClick=\"$script $reset\">Edit</button></td></tr>\n";
            }
            ?>
        </table>
        <div class="mb-3">
            <button class="btn btn-outline-info mx-1" onClick="$('div#problist').slideUp(250); $('div#probupdate').slideUp(250); $('div#probadd').slideDown(250);">Add New Problem</button>
            <button class="btn btn-outline-danger mx-1" onClick="document.location='?action=problem-status&type=Inactive';">Make all Inactive</button>
            <button class="btn btn-outline-success mx-1" onClick="document.location='?action=problem-status&type=Active';">Make all Active</button>
        </div>
        <?php
        $urlargs = "display=adminproblem";
        $totalpages = max(1, ceil($totalCount / $limit));
        $currentPage = $x[0];

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

    <div id='probupdate' style='display:none;'>
        <form action='?action=updateproblem' method='post' enctype='multipart/form-data'>
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="5">
                        <h3>Administrator Options: Update Problem Data</h3>
                    </th>
                </tr>
                <tr>
                    <th class="table-info">Problem Name</th>
                    <td><input class="form-control" id='update_name' name='update_name'></td>
                    <th class="table-info">Problem Status</th>
                    <td>
                        <select class="form-select" id='update_status' name='update_status'>
                            <option value='Active'>Active</option>
                            <option value='Inactive'>Inactive</option>
                            <option>Delete</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="table-info">Problem Code</th>
                    <td><input class="form-control" id='update_code' name='update_code'></td>
                    <th class="table-info"><a class='list-group-item' id='download_statement'>Problem Statement</a></th>
                    <td><input class="form-control" type='file' name='update_file_statement'></td>
                </tr>
                <tr>
                    <th class="table-info">Points</th>
                    <td><input class="form-control" id='update_score' name='update_score'></td>
                    <th class="table-info"><a class='list-group-item' id='download_image'>Problem Image</a></th>
                    <td><input class="form-control" type='file' name='update_file_image'></td>
                </tr>
                <tr>
                    <th class="table-info">Problem Type</th>
                    <td><input class="form-control" id='update_type' name='update_type'></td>
                    <th class="table-info"><a class='list-group-item' id='download_input'>Problem Input</a></th>
                    <td><input class="form-control" type='file' name='update_file_input'></td>
                </tr>
                <tr>
                    <th class="table-info">Problem Group</th>
                    <td><input class="form-control" id='update_pgroup' name='update_pgroup'></td>
                    <th class="table-info"><a class='list-group-item' id='download_output'>Problem Output</a></th>
                    <td><input class="form-control" type='file' name='update_file_output'></td>
                </tr>
                <tr>
                    <th rowspan='3'>Languages Allowed</th>
                    <td rowspan=3>
                        <input type='hidden' name='update_languages' value='' id='update_languages'>
                        <select class="form-select" multiple onChange="updateLangSelect(this.options)" id='update_langselect' title='Use CTRL or SHIFT to select multiple items.'>
                            <?php echo $langlist1; ?>
                        </select>
                    </td>
                    <th class="table-info">Time Limit (sec)</th>
                    <td><input class="form-control" id='update_timelimit' name='update_timelimit'></td>
                </tr>
                <tr>
                    <th class="table-info">Special Options</th>
                    <td>
                        <select class="form-select" name='update_options' id='update_options'>
                            <?php
                            foreach ($execoptions as $key => $value) {
                                echo "<option value='$key'>$value</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th colspan="5">
                        In case any of the File Input Fields are left empty, it will cause the original files to be
                        retained. However, the Text Input Fields, if left empty, will actually be set to NULL.
                    </th>
                </tr>
            </table>
            <br>
            <button class="btn btn-outline-success mx-1" type='submit'>Update Problem Data</button>
            <button class="btn btn-outline-warning mx-1" id='update_reset' onclick="resetUpdateForm()">Reset</button>
            <button class="btn btn-outline-danger mx-1" onclick="cancelUpdate()">Cancel</button>
            <input type='hidden' name='update_pid' id='update_pid' />
        </form>
    </div>

    <script>
        function updateLangSelect(options) {
            var str = '';
            for (var i = 0; i < options.length; i++) {
                if (options[i].selected) {
                    str += (str !== '') ? ',' : '';
                    str += options[i].value;
                }
            }
            document.getElementById('update_languages').value = str;
        }

        function resetUpdateForm() {
            var resetFields = [
                'pid', 'name', 'code', 'type', 'pgroup', 'timelimit', 'score', 'status', 'languages', 'options'
            ];

            for (var i = 0; i < resetFields.length; i++) {
                document.getElementById('update_' + resetFields[i]).value = '';
            }

            var langSelect = document.getElementById('update_langselect');
            for (var i = 0; i < langSelect.options.length; i++) {
                langSelect.options[i].selected = false;
            }

            $('a#download_statement').attr('href', '');
            $('a#download_image').attr('href', '');
            $('a#download_input').attr('href', '');
            $('a#download_output').attr('href', '');
        }

        function cancelUpdate() {
            $('div#problist').slideDown(250);
            $('div#probupdate').slideUp(250);
            $('div#probadd').slideUp(250);
        }
    </script>

    <script>
        function validate_makeproblem() {
            const fields = [{
                    name: "make_name",
                    message: "Problem name not provided."
                },
                {
                    name: "make_code",
                    message: "Problem code not provided."
                },
                {
                    name: "make_score",
                    message: "Problem points not provided."
                },
                {
                    name: "make_pgroup",
                    message: "Problem group not provided."
                },
                {
                    name: "make_timelimit",
                    message: "Problem timelimit not provided."
                },
                {
                    name: "make_languages",
                    message: "No Languages selected for this problem."
                },
                {
                    name: "make_file_statement",
                    message: "Problem statement file not provided."
                },
                {
                    name: "make_file_input",
                    message: "Problem input file not provided."
                },
                {
                    name: "make_file_output",
                    message: "Problem output file not provided."
                }
            ];

            let invalidCharsRegex = new RegExp(<?php echo $invalidchars_js; ?>);

            for (let field of fields) {
                let value = $(`input[name='${field.name}']`).val();
                if (value === "") {
                    alert(field.message);
                    return false;
                }
            }

            if (invalidCharsRegex.test($("input[name='make_name']").val())) {
                alert("Problem name contains invalid characters.");
                return false;
            }
            if (invalidCharsRegex.test($("input[name='make_code']").val())) {
                alert("Problem code contains invalid characters.");
                return false;
            }
            if (/[^0-9]/.test($("input[name='make_score']").val())) {
                alert("Problem score contains invalid characters.");
                return false;
            }
            if (invalidCharsRegex.test($("input[name='make_type']").val())) {
                alert("Problem type contains invalid characters.");
                return false;
            }
            if (/[^0-9]/.test($("input[name='make_timelimit']").val())) {
                alert("Problem timelimit contains invalid characters.");
                return false;
            }

            return true;
        }
    </script>

    <div id='probadd' style='display:none;'>
        <form action='?action=makeproblem' method='post' enctype='multipart/form-data' onsubmit="return validate_makeproblem()">
            <table class="table table-borderless">
                <tr class="table-primary">
                    <th colspan="5">
                        <h3>Administrator Options: Add New Problem</h3>
                    </th>
                </tr>
                <tr>
                    <th class="table-info">Problem Name</th>
                    <td><input class="form-control" name='make_name'></td>
                    <th class="table-info">Problem Status</th>
                    <td><input class="form-control" disabled='disabled' value='Inactive'></td>
                </tr>
                <tr>
                    <th class="table-info">Problem Code</th>
                    <td><input class="form-control" name='make_code'></td>
                    <th class="table-info">Problem Statement</th>
                    <td><input class="form-control" type='file' name='make_file_statement'></td>
                </tr>
                <tr>
                    <th class="table-info">Points</th>
                    <td><input class="form-control" name='make_score'></td>
                    <th class="table-info">Problem Image</th>
                    <td><input class="form-control" type='file' name='make_file_image' id="imagen"></td>
                </tr>
                <tr>
                    <th class="table-info">Problem Type</th>
                    <td><input class="form-control" name='make_type'></td>
                    <th class="table-info">Problem Input</th>
                    <td><input class="form-control" type='file' name='make_file_input'></td>
                </tr>
                <tr>
                    <th class="table-info">Problem Group</th>
                    <td><input class="form-control" name='make_pgroup'></td>
                    <th class="table-info">Problem Output</th>
                    <td><input class="form-control" type='file' name='make_file_output'></td>
                </tr>
                <tr>
                    <th class="table-info" rowspan="3">Languages Allowe</th>
                    <td rowspan="3">
                        <input type='hidden' name='make_languages' value='<?php echo $langlist3; ?>' id='make_languages'>
                        <select class="form-select" multiple onchange="updateLanguages()" multiple='multiple' title='Use CTRL or SHIFT to select multiple items.'>
                            <?php echo $langlist2; ?>
                        </select>
                    </td>
                    <th class="table-info">Time Limit (sec)</th>
                    <td><input class="form-control" name='make_timelimit'></td>
                </tr>
                <tr>
                    <th class="table-info">Special Options</th>
                    <td>
                        <select class="form-select" name='make_options'>
                            <?php foreach ($execoptions as $key => $value)
                                echo "<option value='$key'>$value</option>"; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span>By default, all languages except for Text are enabled. Use CTRL or
                            SHIFT to select multiple languages.</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <div class="small">
                            The values of all text fields must be a combination of up to 30 characters (single and
                            double quotes are not allowed).
                            <br>Please do not put the name of the problem in the Problem Statement File.
                            <br>You may use any HTML tags in the Problem Statement. The '\n' character will
                            automatically be replaced by '&lt;br&gt;'.
                            <br>If you have uploaded an image (only one jpeg/gif/png, max 3MB, allowed per problem), you
                            must specify its position by inserting the (custom) "&lt;image /&gt;" tag somewhere in your
                            code. It will be replaced by the (proper) &lt;img&gt; tag with the src attribute set
                            appropriately.
                            <br>The Problem Statement, Input, and Output Files must be of text format and can have a
                            maximum size of 3MB.
                        </div>
                    </td>
                </tr>
            </table>
            <button class="btn btn-outline-success" type="submit">Create Problem</button>
            <input class="btn btn-outline-warning" value='Reset' type='reset'/>
            <button class="btn btn-outline-danger mx-1" onclick="cancelUpdate()">Cancel</button>
        </form>
    </div>
</div>