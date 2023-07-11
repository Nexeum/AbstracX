import pymysql as sql
import platform, re, os, shutil, sys, _thread, time, urllib

# Ubuntu 22.04
# sudo apt-get update
# sudo apt-get install bf g++ fpc mono-mcs openjdk-11-jdk perl php python3 python3-pymysql rhino ruby


"""
INSERT INTO runs_main (pid,tid,language,name,code,time,result,error,access,submittime,output) SELECT pid,tid,language,name,code,time,result,error,access,submittime,output FROM runs;
INSERT INTO problems (code,name,type,status,pgroup,statement,image,imgext,input,output,timelimit,score,languages,options) SELECT code,name,type,status,'#31 CQM-11 Practice',statement,image,imgext,input,output,timelimit,1,languages,options FROM problems WHERE pid=-1;
"""

if "-judge" not in sys.argv:
    options = [
        "\nNexeum Online Judge : Execution Protocol (Linux Version 1.0)",
        "\nCommand Line Options :",
        "    -judge    : Connect to the server and start judging submissions.",
        "    -unsafe   : Do not skip unsafe programs whose execution may compromise this system.",
        "    -cache    : Use IO Files in Current Directory instead of downloading them."
    ]
    print("\n".join(options))
    sys.exit(0)

timeoffset = 0
# Initialize Database Constants
sql_hostname = 'localhost'
sql_hostport = 3306
sql_username = 'root'
sql_password = ''
sql_database = 'nexeum'
# timeoffset = 19800

# Initialize Language Constants
extension = {
    "Brain": "b",
    "C": "c",
    "C++": "cpp",
    "C#": "cs",
    "Java": "java",
    "JavaScript": "js",
    "Pascal": "pas",
    "Perl": "pl",
    "PHP": "php",
    "Python": "py",
    "Ruby": "rb",
    "Text": "txt"
}
php_prefix = "<?php ini_set('log_errors',1); ini_set('error_log','env/error.txt'); ?>"
ioeredirect = " 0<env/input.txt 1>env/output.txt 2>env/error.txt"


# Define useful variables

running = 0
mypid = int(os.getpid())
timediff = 0
languages = []

# File Read/Write Functions
def file_read(filename):
    if not os.path.exists(filename):
        return ""

    with open(filename, "r") as f:
        d = f.read()

    return d.replace("\r", "")


def file_write(filename, data):
    with open(filename, "w") as f:
        f.write(data.replace("\r", ""))

# Systems Check
def system():
    global languages
    if not os.path.isdir("env"):
        os.mkdir("env")

    languages_to_check = {
        "Brain": "bf",
        "C": "gcc",
        "C++": "g++",
        "Java": "javac",
        "JavaScript": "js",
        "C#": "mcs",
        "Pascal": "fpc",
        "Perl": "perl",
        "PHP": "php",
        "Python": "python3",
        "Ruby": "ruby"
    }

    for language, command in languages_to_check.items():
        if os.popen(f"find /usr/bin/ -name {command}").read() != "":
            languages.append(language)

    print(languages)


# Program Compilation
def create(codefilename, language):
    if language not in ('C', 'C++', 'C#', 'Java', 'Pascal'):
        return

    print("Compiling Code File ...")
    result = None

    compile_commands = {
        "C": f"gcc env/{codefilename}.c -lm -lcrypt -O2 -pipe -ansi -DONLINE_JUDGE -w -o env/{codefilename} {ioeredirect}",
        "C++": f"g++ env/{codefilename}.cpp -lm -lcrypt -O2 -pipe -DONLINE_JUDGE -o env/{codefilename} {ioeredirect}",
        "C#": f"gmcs env/{codefilename}.cs -out:env/{codefilename}.exe {ioeredirect}",
        "Java": f"javac -g:none -Xlint -d env env/{codefilename}.java {ioeredirect}",
        "Pascal": f"fpc env/{codefilename}.pas -oenv/{codefilename} {ioeredirect}"
    }

    if language in compile_commands:
        compile_command = compile_commands[language]
        os.system(compile_command)
        if not os.path.exists(f"env/{codefilename}"):
            result = "CE"

    if result is not None:
        print("Code File Compiled to Executable.")
    else:
        print("Compilation Error")

    return result


# Program Execution
def execute(exename, language):
    global running, timediff
    starttime = time.time()
    running = 1

    command_mapping = {
        "Brain": f"bf env/{exename}.b {ioeredirect}",
        "C": f"env/{exename} {ioeredirect}",
        "C++": f"env/{exename} {ioeredirect}",
        "C#": f"mono env/{exename}.exe {ioeredirect}",
        "Java": f"java -client -classpath env {exename} {ioeredirect}",
        "JavaScript": f"rhino -f env/{exename}.js {ioeredirect}",
        "Pascal": f"env/{exename} {ioeredirect}",
        "Perl": f"perl env/{exename}.pl {ioeredirect}",
        "PHP": f"php -f env/{exename}.php {ioeredirect}",
        "Python": f"python env/{exename}.py {ioeredirect}",
        "Ruby": f"ruby env/{exename}.rb {ioeredirect}"
    }

    if language in command_mapping:
        command = command_mapping[language]
        os.system(command)

    running = 0
    endtime = time.time()
    timediff = endtime - starttime

# Program Termination
def kill(exename, language):
    global mypid

    process_mapping = {
        "Brain": "bf",
        "C": exename,
        "C++": exename,
        "C#": "mono",
        "Java": "java",
        "JavaScript": "rhino",
        "Pascal": exename,
        "Perl": "perl",
        "PHP": "php",
        "Python": "python",
        "Ruby": "ruby"
    }

    if language in process_mapping:
        process = process_mapping[language]
        for process_output in os.popen(f"ps -A | grep {process}").read().split("\n"):
            process_data = process_output.split()
            if len(process_data) > 0:
                pid = int(process_data[0])
            else:
                pid = -1
            if pid == mypid or pid == -1:
                continue
            os.system(f"kill -9 {pid}")


# Perform system checks
if (platform.system() != 'Linux'):
    print
    "Error : This script can only be run on Linux."
    sys.exit(0);

# Print Heading
os.system("clear")
print
"\nNexeum Online Judge : Execution Protocol\n";

# Obtain lock
if (os.path.exists("lock.txt")):
    print
    "Error : Could not obtain lock on Execution Protocol\n"
    print
    "This problem usually occurs if you are trying to run two instances of this script on the same machine at the same time. However, if this is not the case, the solution to this problem would be to shut down all instances of this script, manually delete the 'lock.txt' file (which shall be in the same directory as this) and restart a single instance of it. The latter is usually due to an improper termination the last time this was run, or an error in the script itself.\n";
    sys.exit(1);
else:
    lock = open("lock.txt", "w");
    print
    "Obtained lock on Execution Protocol\n";

# System Check
system()
if len(languages) == 0:
    print
    "Error : No Languages supported on this System."
    sys.exit(1);
else:
    languages.append('Text');
print
"Supported Languages : " + str(languages) + "\n"

# Error Detection
try:
    # Connect to Database
    print
    "Connecting to Server ..."
    link = sql.connect(host=sql_hostname, port=sql_hostport, user=sql_username, passwd=sql_password, db=sql_database);
    cursor = link.cursor(sql.cursors.DictCursor)
    print
    "Connected to Server ..."
    print

    while 1:  # Infinite Loop
        if "-cache" not in sys.argv:
            cursor.execute(
                "SELECT rid,runs.pid as pid,tid,language,runs.name,runs.code as code,error,input,problems.output as output,timelimit,options FROM runs,problems WHERE problems.pid=runs.pid and runs.access!='deleted' and runs.result is NULL and runs.language in " + str(
                    tuple(languages)) + " ORDER BY rid ASC LIMIT 0,1")
        # else: cursor.execute("SELECT rid,runs.pid as pid,tid,language,runs.name,runs.code as code,error,timelimit,options FROM runs x,problems WHERE problems.pid=runs.pid and (tid=1 AND runs.rid = (SELECT max(rid) FROM runs WHERE x.tid=tid and x.pid=pid)) and runs.access!='deleted' and runs.result is NULL and runs.language in "+str(tuple(languages))+" ORDER BY rid ASC LIMIT 0,1")
        else:
            cursor.execute(
                "SELECT rid,runs1.pid as pid,tid,language,runs1.name,runs1.code as code,error,timelimit,options FROM runs AS runs1,problems WHERE problems.pid=runs1.pid and (tid=1 OR runs1.rid = (SELECT max(runs2.rid) FROM runs AS runs2 WHERE runs2.tid=runs1.tid and runs2.pid=runs1.pid)) and runs1.access!='deleted' and runs1.result is NULL and runs1.language in " + str(
                    tuple(languages)) + " ORDER BY rid ASC LIMIT 0,1")
        if cursor.rowcount > 0:
            os.system("clear")
            print
            "\nNexeum Online Judge : Execution Protocol\n";

            # Select an Unjudged Submission
            run = cursor.fetchone()
            cursor.execute("UPDATE runs SET result='...' WHERE rid='%d'" % (run["rid"]));
            print
            "Selected Run ID %d for Evaluation." % (run["rid"]);

            # Clear Environment
            while len(os.listdir("env")) > 0:
                try:
                    for file in os.listdir("env"): os.unlink("env/" + file);
                except:
                    pass
            print
            "Cleared Environment for Program Execution.";

            # Initialize Variables
            result = None;
            timetaken = 0;
            running = 0

            # Check for "#include<CON>" in case of C/C++
            if result == None and (run["language"] == "C" or run["language"] == "C++") and re.match(
                    r"#include\s*['\"<]\s*[cC][oO][nN]\s*['\">]", run["code"]):
                print
                "Language C/C++ : #include<CON> detected."
                file_write("env/error.txt", "Error : Including CON is not allowed.");
                result = "CE";
                timetaken = 0

            if result == None and run["language"] == "C":
                code = run["code"].split("\n");
                newcode = ""
                for line in code:
                    newcode += re.sub(r"//(.*)$", "", line) + "\n"
                run["code"] = newcode

            # Check for malicious codes in Python
            if False and result == None and run["language"] == "Python" and (
                    re.match(r"import os", run["code"]) or
                    re.match(r"hack", run["code"])):
                print
                "Suspicious Code."
                file_write("env/error.txt", "Error : Suspicious code.");
                result = "SC";
                timetaken = 0

            # Write Code & Input File
            if result == None:
                if run["language"] == "Java":
                    codefilename = run["name"]
                elif run["language"] == "Text":
                    codefilename = "output"
                else:
                    codefilename = "code";
                codefile = open("env/" + codefilename + "." + extension[run["language"]], "w")
                if (run["language"] == "PHP"): codefile.write(php_prefix);  # append prefix for PHP
                codefile.write(run["code"].replace("\r", ""));
                codefile.close();
                if "-cache" not in sys.argv:
                    file_write("env/input.txt", run["input"]);
                else:
                    shutil.copyfile("io_cache/Nexeum Online Judge - Problem ID " + str(run["pid"]) + " - Input.txt",
                                    "env/input.txt")
                print
                "Code & Input File Created."

            # Compile, if required
            if result == None:
                result = create(codefilename, run["language"]);  # Compile

            # Increase Time Limit in case of JavaScript & PHP
            if run["language"] in ('JavaScript', 'PHP'):
                run["timelimit"] += 1

            # Run the program through a new thread, and kill it after some time
            if result == None and run["language"] != "Text":
                running = 0
                _thread.start_new_thread(execute, (codefilename, run["language"]))
                while running == 0: pass  # Wait till process begins
                print
                "Spawning process ..."
                for timetaken in range(int(run["timelimit"])):
                    print
                    "Timer : " + str(timetaken + 1) + "/" + str(run["timelimit"])
                    if (running == 0): break
                    time.sleep(1)
                if running == 0 and (run["pid"] != 13 or float(timediff) < 0.5):
                    print
                    "Process Complete."
                    timetaken = timediff
                else:
                    result = "TLE"
                    timetaken = run["timelimit"]
                    kill(codefilename, run["language"])
                    print
                    "Time Limit Exceeded - Process killed."

            # Compare the output
            output = ""
            if result == None and run["language"] != "Text" and file_read("env/error.txt") != "":
                output = file_read("env/output.txt")
                result = "RTE"
            if result == None:
                output = file_read("env/output.txt")
                if "-cache" in sys.argv:
                    run["output"] = file_read(
                        "io_cache/Nexeum Online Judge - Problem ID " + str(run["pid"]) + " - Output.txt")
                correct = run["output"].replace("\r", "")
                file_write("env/correct.txt", run["output"])
                if run["options"] is None: run["options"] = ""
                if (output == correct):
                    result = "AC"
                elif "S" in run["options"] and re.sub(" +", " ",
                                                      re.sub("\n *", "\n", re.sub(" *\n", "\n", output))) == re.sub(
                    " +", " ", re.sub("\n *", "\n", re.sub(" *\n", "\n", correct))):
                    result = "AC"
                elif (re.sub(r"\s", "", output) == re.sub(r"\s", "", correct)):
                    result = "AC" if "P" in run["options"] else "PE"
                else:
                    result = "WA"
            print
            "Output Judgement Complete."

            # Write results to database
            error = file_read("env/error.txt")
            if result == "AC": output = ""
            # if "-cache" in sys.argv: output = "/// Output not saved. ///"
            cursor.execute("UPDATE runs SET time='%.3f',result='%s',error='%s',output='%s' WHERE rid=%d" % (
            float(timetaken), result, re.escape(error), re.escape(output), int(run["rid"])));
            link.commit();
            print
            "Result (%s,%.3f) updated on Server.\n" % (result, timetaken)
            time.sleep(1)
        else:
            os.system("clear")
            print
            "\nNexeum Online Judge : Execution Protocol\n";
            print
            "There are currently no unjudged sumbissions on the server.\n"
            print
            "Press CTRL+C to terminate the Execution Protocol."
            time.sleep(1)
            countdown = 3
            while countdown > 0:
                os.system("clear")
                print
                "\nNexeum Online Judge : Execution Protocol\n";
                print
                "Contacting server in " + str(countdown) + " seconds ...\n"
                print
                "Press CTRL+C to terminate the Execution Protocol."
                time.sleep(1)
                countdown -= 1

        # Update admin.lastjudge time on server
        cursor.execute("SELECT * FROM admin WHERE variable='lastjudge'");
        if cursor.rowcount > 0:
            cursor.execute(
                "UPDATE admin SET value='" + str(int(time.time()) + timeoffset) + "' WHERE variable='lastjudge'");
        else:
            cursor.execute("INSERT INTO admin VALUES ('lastjudge','" + str(int(time.time()) + timeoffset) + "')");

except sql.Error as e:
    print
    "MySQL Error %d : %s\n" % (e.args[0], e.args[1])
except KeyboardInterrupt as e:
    print
    " Keyboard Interrupt Detected.\n"
except Exception as e:
    print
    "Exception : " + str(e) + "\n"

# Disconnect from Server
try:
    cursor.close()
except:
    pass
try:
    link.close()
except:
    pass
print
"Disconnected from Server.\n"

# Release lock
try:
    lock.close();
    os.unlink("lock.txt");
except:
    pass
print
"Released lock on Execution Protocol.\n"

# Terminate
print
"Nexeum Online Judge : Execution Protocol Terminated.\n";
