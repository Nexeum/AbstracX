<?php
global $admin;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include("sys/system_init.php");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Nexeum Online Judge</title>
    <meta name="description" content="Nexeum Online Judge" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.7.0/build/styles/default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/browser-detect@0.2.28/dist/browser-detect.umd.min.js"></script>
    <script src="data/select.js" type="text/javascript"></script>
</head>

<body onLoad="init(); <?php echo ($admin['ajaxrr'] == 0) ? 'load();' : 'reload();'; ?> step();">
    <div id='ajaxtimer'></div>
    <div class="container-fluid text-center">
        <nav class="navbar navbar-expand-lg mb-3 rounded justify-content-center">
            <a href="/" class="navbar-brand mr-0">
                <img src="assets/logo.svg" alt="Logo" width="30" height="30" class="d-inline-block align-top">
                Nexeum
            </a>
            <button class="navbar-toggler ml-1" type="button" data-toggle="collapse" data-target="#collapsingNavbar2">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse collapse justify-content-between align-items-center w-100" id="collapsingNavbar2">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item">
                        <a class="nav-link" href="?display=notice">Important Notices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=scoreboard">Scoreboard</a>
                    </li>
                    <?php if ($_SESSION["tid"] != 0) {
                        echo "<li class='nav-item'><a href='?display=account' class='nav-link'>Account Settings</a></li>";
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=problem">Problems</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=clarifications">Clarifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=rankings">Rankings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?display=submissions">Submissions</a>
                    </li>
                    <?php if ($_SESSION["status"] == "Admin") : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Administrador
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?display=adminsettings">Administrator Settings</a></li>
                                <li><a class="dropdown-item" href="?display=adminproblem">Problems Settings</a></li>
                                <li><a class="dropdown-item" href="?display=adminteam">Teams Settings</a></li>
                                <li><a class="dropdown-item" href="?display=admingroup">Group Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="?display=admindata">Data Commitment</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="?display=adminlogs">Logs</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <div class="row">
            <div class="col-lg-2 col-md-4 col-12">
                <div class="row">
                    <div class="col">
                        <div class="border rounded mb-3">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="table-primary">
                                            <h4>Contest Status</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-info">
                                            <strong>Mode:</strong>
                                        </td>
                                        <td>
                                            <div id='ajax-contest-status'></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-info">
                                            <strong>Judgement:</strong>
                                        </td>
                                        <td>
                                            <div id='ajax-contest-judgement'></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-info">
                                            <strong>Timer:</strong>
                                        </td>
                                        <td>
                                            <div id='ajax-contest-time'></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive border rounded mb-3" id='ajax-problem'></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive border rounded mb-3" id='ajax-allsubmit'></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive border rounded mb-3" id='ajax-rankings'></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-4 col-12">
                <div class="row">
                    <div class="col">
                        <?php display_message(); ?>
                        <div class="border rounded mb-3">
                            <?php display_main(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-12">
                <div class="row">
                    <div class="col">
                        <div class="border rounded mb-3">
                            <?php display_statusbox(); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="border rounded mb-3" id='ajax-mysubmit'></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="border rounded mb-3" id='ajax-privateclar'></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="border rounded mb-3" id='ajax-publicclar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a id="bottom"></a>
    <script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.7.0/build/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
    </script>
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
            if (browserDetect().name !== "Firefox") {
                $("input[type=button]").css('padding', '3px');
            }
            $("code").each(function(index) {
                $(this).html("<div class='limit code'>" + $(this).html().replace(/<br>/, '') + "</div>");
                $(this).attr('id', 'select_code_' + index).attr('title', 'Double click to select all code.');
                $(this).dblclick(function() {
                    selectElement($(this).attr('id'));
                });
            });
        }


        function load() {
            let data = eval("(<?php if ($admin["ajaxrr"] == 0) echo addslashes(action_ajaxrefresh(1)); ?>)");
            $.each(data, function(key, value) {
                process(key, value);
            });
        }

        function reload() {
            $('#ajaxtimer').html('Contacting server via Ajax ...');
            $.getJSON("index.php", {
                action: "ajaxrefresh"
            }, function(data) {
                $('#ajaxtimer').html('Updating data ...');
                $.each(data, function(key, value) {
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


        function addslashes(str) {
            return str;
        }

        $(document).ready(function() {
            let $output = $(".output");
            let $actual = $(".actual");
            let scroll_lock = true;

            $output.scroll(function() {
                if (scroll_lock) {
                    $actual.scrollTop($output.scrollTop());
                    $actual.scrollLeft($output.scrollLeft());
                }
            });

            $actual.scroll(function() {
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