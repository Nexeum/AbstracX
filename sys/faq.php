<div class="mb3">
    <h4>Frequently Asked Questions</h4>
</div>
<div class="accordion accordion-flush mb-3" id="accordionFlush">
    <!-- Pregunta 1 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                What is the Nexeum Online Judge?
            </button>
        </h2>
        <div id="flush-collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The Nexeum Online Judge is a Programming Contest Control System. It acts as an interface between the
                    judges and the participants of a Computer Programming Contest.</p>
                <p>A Computer Programming Contest is a competition where teams submit (computer program) solutions to
                    judges. The teams are given a set of computer problems to solve in a limited amount of time (for example
                    3 hours). The judges then give a pass/fail judgement to the submitted solution which is sent back to the
                    teams. The team rankings are computed based on the solutions, when the solutions were submitted and how
                    many attempts were made to solve the problem. The judges testing is a Black box testing where the teams
                    do not have access to the judges' test data.</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 2 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                How does this website actually work?
            </button>
        </h2>
        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The Nexeum Online Judge System has three main parts : the SQL Database (which stores all information),
                    the User Interface (the website that you are currently using) and the Execution Protocol (the scripts
                    that actually run the programs you submit). The website essentially just takes information from the
                    Database, formats it to make it look nice, add options to manipulate it, and presents it to the
                    user.</p>
                <p>The data displayed on both sides of the webpage is refreshed a few times per minute (using Ajax) in order
                    to provide you with the latest information conveniently. The User Account system is implemented by
                    Cookies (which are used to save information about whether you are currently logged in, and if so,
                    more details about your team).</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 3 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                What exactly is the Execution Protocol?
            </button>
        </h2>
        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>Execution Protocol, as mentioned before, is a script that actually runs submitted programs and judges
                    their correctness. The basic functioning of the Execution Protocol can be described as follows :</p>
                <ul>
                    <li>Select a solution submitted from the database that has not yet been evalutated, and for which a
                        compiler/interpreter is available.
                    </li>
                    <li>Compile the source code, if required. If compilation fails, return "Compilation Error".</li>
                    <li>Run the program, connecting the Stardard Input, Output and Error Streams to appropriate files.</li>
                    <li>Wait for an appropriate amount of time (as specified by the time limit of the problem).</li>
                    <li>If the program has not already terminated, kill it and return "Time Limit Exceeded".</li>
                    <li>If the STDERR stream (directed to a temporary file) is not empty, return "Run Time Error".</li>
                    <li>Now that the program has terminated with the time limit, compare the output with the correct output
                        associated with the problem. If there is a total match, return "Accepted".
                    </li>
                    <li>Remove all whitespace characters in the program output and correct output and compare again. If
                        there is a match, return "Presentation Error", or else return "Wrong Answer".
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Pregunta 4 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                How do I participate here?
            </button>
        </h2>
        <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The first thing you need to do it register your team using form given on the <a target='new' href='?display=register'>Registration
                        Page</a>. Once you choose a unique team name and give details about the (1-3) members of your team,
                    you need to wait until an Administrator authorizes your account (after verifying its authenticity).
                    Details (with the exception being your Password) provided during registration cannot be changed unless
                    you request an Administrator to do it for you (which means you'll need a good reason). Once that is
                    done, you may log in.</p>
                <p>Once logged in, you may the view information you provided during registeration and change your password
                    from the <a target='new' href='?display=account'>Account Settings</a> page. You can access and search
                    through the list of all currently available problems from the <a href='?display=problem' target=new>Problems
                        Index</a> (solved problems will automatically be marked green). If the contest is in Active or
                    Passive Mode, you may also submit solutions to problems by selecting a file to upload or by copy-pasting
                    your code in the area provided, provided the language you have used is supported and allowed for that
                    particular problem. Your code must read from the Stanard Input and print to the Standard Output, and
                    must be efficient (fast) enough to finish within the time limit of the problem. You can see the results
                    of the program run on the <a href='?display=submissions' target=new>Submission Status</a> Page.</p>
                <p>If you have any questions that haven't already been answered here, or any ambiguity regarding the problems
                    themselves during the contests, you may use the <a target='new' href='?display=clarifications'>Clarifications</a>
                    Feature to ask Administrators or other teams your question. Usually, clarifications can only been seen
                    by the Administrators and team that requested it. However, if an Administrator thinks it is appropriate,
                    he may make your question and his reply 'Public', thus allowing all teams to see it.</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 5 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                What type of platform shall my codes be run on?
            </button>
        </h2>
        <div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>To prevent malicious codes from harming the Execution Environment or the Server itself, submitted
                    programs are executed on Virtual Machines. The configuration of the Virtual Machine being used right now
                    is given below :</p>
                <ul>
                    <li>Operating System : Ubuntu 10.10 (Maverick); Harddisk : 20GB ; RAM : 512MB</li>
                    <li>C Compiler : gcc 4.4.5</li>
                    <li>C++ Compiler : g++ 4.4.5</li>
                    <li>C# Compiler : Mono Compiler Version 2.6.7 (gmcs)</li>
                    <li>Java Compiler : javac 1.6.0_20, java 1.6.0_20</li>
                    <li>JavaScript Interpreter : rhino 1.7</li>
                    <li>Pascal Interpreter : gpc version 20070904</li>
                    <li>Perl Interpreter : perl v5.10.1</li>
                    <li>PHP Interpreter : PHP 5.3.3</li>
                    <li>Python Interpreter : python 2.6.6</li>
                    <li>Ruby Interpreter : ruby 1.8.7</li>
                </ul>
                <p>Please contact an Administrator to request support for additional languages.</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 6 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSix" aria-expanded="false" aria-controls="flush-collapseSix">
                Can you give an example of kind of the programs we can submit here?
            </button>
        </h2>
        <div id="flush-collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>Please refer to and use the <a target='new' href='?display=problem&pid=1'>Squares</a> Problem to test
                    your choice of programming language. Accepted solutions to this problem have also been made Public for
                    educational reasons, and are available in the following languages : <a target='new' href='?display=code&rid=1'>C</a>,
                    <a target='new' href='?display=code&rid=2'>C++</a>, <a target='new' href='?display=code&rid=3'>C#</a>,
                    <a target='new' href='?display=code&rid=4'>Java</a>, <a target='new' href='?display=code&rid=5'>JavaScript</a>,
                    <a target='new' href='?display=code&rid=6'>Pascal</a>, <a target='new' href='?display=code&rid=7'>Perl</a>, <a target='new' href='?display=code&rid=8'>PHP</a>, <a target='new' href='?display=code&rid=9'>Python</a> and <a target='new' href='?display=code&rid=10'>Ruby</a>. Please remember that there is a 100KB limit on
                    the size of the code you can submit.
                </p>
                <p>Please do bother not submitting malicious programs that might harm the Execution Environment or the
                    Server itself. As the execution takes place on Virtual Machines, this will only result in a minor
                    inconvenience to the Administrators and the suspension of your account. Also, programs that try to
                    communicate with machines other than this server (in an attempt to send information like the input given
                    to the program) will not work, given that the Virtual Machines are on a small isolated private network.
                    Sumbission of programs that do anything other than try to solve problems will result in severe
                    consequences.</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 7 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSeven" aria-expanded="false" aria-controls="flush-collapseSeven">
                Why is my program not being Accepted?
            </button>
        </h2>
        <div id="flush-collapseSeven" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The programs are judged by the Execution Protocol as described above. However, there exist cases that
                    haven't been dealt with, and some of which are mentioned below along with some common errors :</p>
                <ul>
                    <li>No provision has been made to detect Run Time Errors in case of languages which need to be compiled.
                        Consequently, if one occurs, it may cause the process to hang (returning TLE, Time Limit Exceeded)
                        or to abort (returning WA, Wrong Answer).
                    </li>
                    <li>Java code files must have the same name as the class which contains the main function. If you are
                        uploading *.java files, this should not be a concern, but in case you are submitting text, please
                        ensure that you specify the class name correctly when asked for it.
                    </li>
                    <li>Ensure that your program is not printing anything other that what is asked. Ensure that the print
                        operations that you used for debugging your code are removed or commented out. Also ensure that your
                        program is reading from the Standard Input only, and not a file as during debugging.
                    </li>
                </ul>
                <p>If you are sure that none of the reasons described above are applicable in your case, please reconsider
                    the virtual impossibity that logic of your program is flawed, and reexamine your code. If you are
                    absolutely sure that your program is correct in every way, but is still not being Accepted, you may
                    contact an Administrator (via the Clarifications feature) to rejudge or manually run your program (if it
                    does come to that, please quote the Run ID). Note that a particular clarification can only be deleted by
                    the team that requested them provided it not been replied to by an Administrator.</p>
            </div>
        </div>
    </div>

    <!-- Pregunta 8 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseEight" aria-expanded="false" aria-controls="flush-collapseEight">
                How is the ranking done here?
            </button>
        </h2>
        <div id="flush-collapseEight" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The primary basis for ranking teams is their score. In case the score of two teams are equal, then the
                    team whose solution got accepted first is ranked higher. Note that every incorrect submission (submitted
                    before the first correct solution) results in a <?php global $admin;
                                                                    echo $admin["penalty"]; ?> minute penalty on the time of your submission. Therefore, please avoid
                    submitting programs unless you are reasonably sure they will work.</p>
                <!--
                <p>An important point that must be explained is that there are two separate ranklists available on this site. The <a href='?display=rankings' target='new'>Current Rankings</a> are updated every 10 seconds and reflect the current ranks of the various teams, independent of their past performance. In contrast, the <a href='?display=scoreboard' target='new'>Main Scoreboard</a> (updated far more infrequently) contains the results of the various competitions conducted till now, and uses them to generate long term rankings.</p>
            -->
            </div>
        </div>
    </div>

    <!-- Pregunta 9 -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseNine" aria-expanded="false" aria-controls="flush-collapseNine">
                What are the different Contest modes you mentioned before?
            </button>
        </h2>
        <div id="flush-collapseNine" class="accordion-collapse collapse" data-bs-parent="#accordionFlush">
            <div class="accordion-body">
                <p>The different Contest Modes mentioned earlier are described below :</p>
                <ul>
                    <li>Active Mode : Submissions are allowed, problem types are hidden, and the Timer is On.</li>
                    <li>Passive Mode : Submissions are allowed, problem types are visible, and the Timer is Off.</li>
                    <li>Disabled Mode : Submissions are not allowed, problem types are visible, and the Timer is Off.</li>
                    <li>Lockdown Mode : All features (except FAQ, Main Scoreboard & Clarifications) are disabled for normal
                        users.
                    </li>
                </ul>
                <p>The Lockdown Mode is used immediately prior to (Active Mode) contests, during which Administrators (who
                    aren't affected by the Lockdown Mode once they log in) are uploading and testing new problems.</p>
            </div>
        </div>
    </div>
</div>