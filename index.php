<?php
global $admin;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include("sys/system_init.php");
?>

    <html lang="en">
    <head><title>Nexeum Online Judge [SourceCode]</title>
        <meta name="description" content="Nexeum Online Judge"/>
        <meta charset="utf-8"/>

        <link rel='shortcut icon' href='data/laptop_black.png'/>
        <link rel='stylesheet' type='text/css' href='data/style.css'/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <script src="data/jquery.js" type="text/javascript"></script>
        <script src="data/browser.js" type="text/javascript"></script>
        <script src="data/select.js" type="text/javascript"></script>

    </head>
    <body onLoad="init(); <?php if ($admin["ajaxrr"] == 0) echo "load();"; else echo "reload();"; ?> step();">
    <div
        style='position: fixed; top: 5px; right: 5px; font-size: 10px; background: rgba(128, 128, 128, 0.2); padding: 2px; border-radius: 5px;'
        id='ajaxtimer'></div>
    <h1 style="text-align: center;">Nexeum Online Judge</h1>
    <table class='main' style="margin: 0 auto;">
        <tr>
            <td class='side' >
                <div class='sidebox'>
                    <h3>Contest Status</h3>
                    <table>
                        <tr>
                            <th>Mode</th>
                            <th>Judgement</th>
                            <th>Timer</th>
                        </tr>
                        <tr>
                            <td>
                                <div id='ajax-contest-status'></div>
                            </td>
                            <td>
                                <div id='ajax-contest-judgement'></div>
                            </td>
                            <td>
                                <div id='ajax-contest-time'></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class='sidebox' id='ajax-problem'></div>
                <div class='sidebox'>
                    <li><a href='?display=notice'>Important Notices</a></li>
                    <li><a href='?display=faq'>Frequently Asked Questions</a></li>
                    <?php if (0) echo "<li><a href='?display=scoreboard'>Main Scoreboard</a></li>"; ?>
                    <?php if ($_SESSION["tid"] != 0) {
                        echo "<li><a href='?display=account'>Account Settings</a></li>";
                    } ?>
                    <li><a href='?display=problem'>Problems Index</a></li>
                    <li><a href='?display=clarifications'>Clarifications</a></li>
                    <li><a href='?display=rankings'>Current Rankings</a></li>
                    <li><a href='?display=submissions'>Submissions Status</a></li>
                    <?php if ($_SESSION["status"] == "Admin") {
                        echo "<br>";
                        echo "<li><a href='?display=adminsettings'>Administrator Settings</a></li>";
                        if (0) echo "<li><a href='?display=admindata'>Data Commitment</a></li>";
                        echo "<li><a href='?display=adminproblem'>Problem Settings</a></li>";
                        echo "<li><a href='?display=adminteam'>Teams Settings</a></li>";
                        echo "<li><a href='?display=admingroup'>Group Settings</a></li>";
                        echo "<li><a href='?display=adminlogs'>Access Logs</a></li>";
                    } ?>
                </div>
                <div class='sidebox' id='ajax-allsubmit'></div>
                <div class='sidebox' id='ajax-rankings'></div>
            </td>
            <td class='center'>
                <?php display_message(); ?>
                <div class='centerbox'><?php display_main(); ?><br><br></div>
            </td>
            <td class='side'>
                <div class='sidebox'><?php display_statusbox(); ?></div>
                <div class='sidebox' id='ajax-mysubmit'></div>
                <div class='sidebox' id='ajax-privateclar'></div>
                <div class='sidebox' id='ajax-publicclar'></div>
                <div style="text-align: center;" class="sidebox">
                    <i>Created by Juan Andres [<a href="https://github.com/Nexeum" target="_blank">Nexeum</a>].</i>
                </div>
                </div>
            </td>
    </table>
    <a id="bottom"></a>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script type="text/javascript">SyntaxHighlighter.all();</script>
    <script type="text/javascript" src="data/syntax-highlighter/shCore.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushCpp.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushCSharp.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushJava.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushJScript.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushPerl.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushPhp.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushPlain.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushPython.js"></script>
    <script type="text/javascript" src="data/syntax-highlighter/shBrushRuby.js"></script>
    <link type="text/css" rel="stylesheet" href="data/syntax-highlighter/shCoreDefault.css"/>

    <script>
        let countdown = -1;

        function step() {
            if (countdown === 0) {
                $("div#ajax-contest-status").html("Disabled");
            }
            if (countdown > 0) {
                $("div#ajax-contest-time").html(parseInt((countdown / 3600).toString()) + "h " + parseInt((countdown / 60).toString()) % 60 + "m " + (countdown % 60) + "s");
            } else {
                $("div#ajax-contest-time").html("NA");
            }
            if (countdown >= 0) {
                countdown--;
            }
            window.setTimeout("step();", 1000);
        }

        function url_get(q) {
            let s = window.location.search;
            let re = new RegExp('&' + q + '=([^&]*)', 'i');
            return (s.replace(/^\?/, '&').match(re)) ? s[1] : '';
        }

        function process(key, value) {
            if (key === "refresh" && value === 1) {
                window.location.reload();
            } else if ((key === "newclar" || key === "newclar2") && value !== "") {
                alert(value);
            } else if (key === "ajax-contest-status") {
                $("div#" + key).html(value === "Active" ? "CQM" : value === "Passive" ? "Practice" : value);
            } else if (key === "ajax-contest-time") {
                countdown = parseInt(value);
            } else {
                $("div#" + key).html(value);
            }
        }

        function init() {
            if (BrowserDetect.browser !== "Firefox") {
                $("input[type=button]").css('padding', '3px');
            }
            $("code").each(function (index) {
                $(this).html("<div class='limit code'>" + $(this).html().replace(/<br>/, '') + "</div>");
                $(this).attr('id', 'select_code_' + index).attr('title', 'Double click to select all code.');
                $(this).dblclick(function () {
                    selectElement($(this).attr('id'));
                });
            });
        }


        function load() {
            let data = eval("(<?php if ($admin["ajaxrr"] == 0) echo addslashes(action_ajaxrefresh(1)); ?>)");
            $.each(data, function (key, value) {
                process(key, value);
            });
        }

        function reload() {
            $('#ajaxtimer').html('Contacting server via Ajax ...');
            $.getJSON("index.php", {action: "ajaxrefresh"}, function (data) {
                $('#ajaxtimer').html('Updating data ...');
                $.each(data, function (key, value) {
                    process(key, value);
                });
                let ajaxtimer = <?php echo $admin["ajaxrr"] ?? 0; ?>;
                for (let i = ajaxtimer; i > 0; i--) {
                    window.setTimeout(() => {
                        $('#ajaxtimer').html('Updating data in ' + i + ' second(s).');
                    }, (ajaxtimer - i) * 1000);
                }
                window.setTimeout(reload, ajaxtimer * 1000);
            });
        }


        function problem_search() {
            let query = $('input#query').attr('value').toLowerCase();
            if (query.length > 0) {
                $('div.probindex div.probheaders1').slideUp(250);
                $('div.probindex div.probheaders2').slideDown(250);
            } else {
                $('div.probindex div.probheaders1').slideDown(250);
                $('div.probindex div.probheaders2').slideUp(250);
            }
            $('div.probindex div.problem').each(function () {
                let match = 0;
                $(this).find('td').each(function () {
                    if ($(this).text().toLowerCase().indexOf(query) !== -1) match++;
                });
                if (match === 0) {
                    $(this).slideUp(250);
                } else {
                    $(this).slideDown(250);
                }
            });
        }


        function addslashes(str) {
            return str;
        }

        $(document).ready(function () {
            let $output = $(".output");
            let $actual = $(".actual");
            let scroll_lock = true;

            $output.scroll(function () {
                if (scroll_lock) {
                    $actual.scrollTop($output.scrollTop());
                    $actual.scrollLeft($output.scrollLeft());
                }
            });

            $actual.scroll(function () {
                if (scroll_lock) {
                    $output.scrollTop($actual.scrollTop());
                    $output.scrollLeft($actual.scrollLeft());
                }
            });
        });

    </script>
    </body>
    </html>
<?php mysqli_terminate(); ?>