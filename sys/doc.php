<div><h2>Documentation</h2></div>

<br>
<table class='faq'>
    <tr>
        <th>Instructions : How to start using this software</th>
    </tr>
    <tr>
        <td>
            <ul>
                <li>Extract the contents of the archive into your apache's web site root directory (or a subdirectory
                    thereof). Open the website via any browser and confirm that the pages are at least loading. There
                    will inevitably be an error about the inability of the script to access the database.
                </li>
                <li>Ensure that the permissions of the direction (henceforth referred to as nexeum_root) are 777 in
                    order to allow creation of temporary files that are subsequently read and deleted.
                </li>
                <li>Edit the file nexeum_root/sys/system_config.php, and set the $mysql_hostname, $mysql_username,
                    $mysql_password, $mysql_database, $admin_teamname and $admin_password variables appropriately.
                </li>
                <li>Reload the website via your browser. Now, the database connection error should have disappeared.
                    <br>This means that a proper connection to the database could be established, and given that this
                    was the first time this has happened, an initialization procedure also occured. This involves
                    creating an Administrator account ($admin_teamname & $admin_password as defined in
                    nexeum_root/sys/system_init.php), a sample program (Sqaures), and multiple correct submissions for
                    this program in different languages (for testing purposes).
                </li>
                <li>This software is now ready to use.</li>
            </ul>
        </td>
    </tr>
</table>

<br>
<table class='faq'>
    <tr>
        <th>Instructions : Administration</th>
    </tr>
    <tr>
        <td>
            <ul>
                <li>Login as an Administrator and go to the Contest Settings (from the new options in the index to the
                    left). Here you may set the contest mode (as defined in the FAQ), the Contest End Time (useful if
                    you wish to prepone/postpose the end of a contest), the Incorrect Submission Penalty (used to rank
                    teams with the same score) and Ajax Refresh Rate (the interval at the user's browser send requests
                    for new data; set this based on your expected server load).
                </li>
                <li>Data Commitment creates a backup of the important information pertaining to currently active Teams,
                    Problems & Runs. It is a costly process, and should not be used frivolously. Committed Data that is
                    Active is used to generate the Main Scoreboard.
                </li>
                <li>Problem Settings : Gives you a list of all currently existing problems and the options to Edit them
                    or Add a new one. Problem Statement, Image, Input and Output files for all problems must be less
                    than 2MB. Problems that are marked Inactive are completed hidden from Normal Users.
                </li>
                <li>Team Settings : Here you can find a list of all registered teams, and all the information that has
                    been collected about each. Team details may be edited if required. Newly registered teams are
                    assigned the status "Waiting", and must be authorized by an Administrator (who needs to set their
                    status to "Normal") before they can even login.
                <li>Access Logs : With the exception of those sent by an Administrator, logs of all page requests can be
                    found here.
                </li>
            </ul>
        </td>
    </tr>
</table>

<br>
<table class='faq'>
    <tr>
        <th>Instructions : How to conduct a contest</th>
    </tr>
    <tr>
        <td>
            <ul>
                <li>Login as an Administrator, go to the Contest Settings Page, and set the contest mode to Lockdown.
                    This will forcefully log out all currently logged in non-admin users and shut off access to the
                    Problems, Submission Status and Rankings Pages, thereby hiding anything you do from the users.
                </li>
                <li>Go to Problem Settings and set the status of all problems that arent part of this competition to
                    'Inactive' (instead of deleting them). This will effectively remove them from further considering by
                    the rest of the website until they are made 'Active' again.
                </li>
                <li>Add new problems and be sure to read the instruction given at the bottom of the relevant page. Set
                    the status of the new problems to 'Active'.
                </li>
                <li>On the virtual machines which will judge the solutions, load a reliable snapshot (a saved state
                    which has all the compilers and the nexeum.py script), ensure that the connection settings in the
                    nexeum.py script are accurate, and run it.
                </li>
                <li>You may now submit your 'correct' solutions to the 'Active' problems and test them to ensure that
                    the system is working properly.
                </li>
                <li>You may go to the Team Settings page and set the status of the latest registered teams to 'Normal'
                    so that they may participate in the contest. Please ensure that the details provided by them are
                    accurate.
                </li>
                <li>Go back to the Contest Settings page and set the status to 'Active'. If you do not specify the 'End
                    Time', a default value of 3 hours will be assumed. Normal users can now login, view problems and
                    submit solutions.
                </li>
                <li>When the timer expires, the contest status is automatically set to 'Disabled', and submissions are
                    no longer allowed. It may take a bit longer than that for the judgement of all submissions to take
                    place.
                </li>
                <li>Once all solutions have been judged, go to Data Commitment and enter the name of the contest as the
                    'Recond Name'. Click on 'Commit Data'. This will add the results of the current contest to the main
                    scoreboard, and create a backup of important data regarding the current contest in the database.
                </li>
                <li>If you wish, you may open the submission statistics of all problems and make certain accepted
					solutions 'Public' thereby allowing everyone to see the code. The general format of the links to
					these codes will be "https://[server-address]/[path]/?display=code&rid=[some-number]".
				</li>
            </ul>
        </td>
    </tr>
</table>