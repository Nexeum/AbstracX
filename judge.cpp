#include <iostream>
#include <fstream>
#include <sstream>
#include <map>
#include <cstdlib>
#include <unistd.h>
#include <mysql_driver.h>
#include "mysql_connection.h"
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>

using namespace std;

// sudo apt-get install libmysqlcppconn-dev

string languages = "('C', 'C++', 'Java', 'Python')";

map<string, string> extension = {
    {"C", "c"},
    {"C++", "cpp"},
    {"C#", "cs"},
    {"Java", "java"},
    {"Python", "py"},
    {"Ruby", "rb"}
};

string ioeredirect = " 0<env/input.txt 1>env/output.txt 2>env/error.txt";

sql::mysql::MySQL_Driver *driver;
sql::Connection *connection;
sql::Statement *statement;
sql::ResultSet *result;

string fileRead(const string &filename) {
    string code;
    ifstream file(filename);
    stringstream buffer;
    buffer << file.rdbuf();
    code = buffer.str();
    return code;
}

void fileWrite(const string &filename, const string &data) {
    ofstream file(filename);
    file << data;
}

void create(const string &name, const string &language) {
    const string codefilename = name + "." + extension[language];
    string compileCommand = extension[language] + " env/" + codefilename + " -o env/" + codefilename + " " + ioeredirect;
    cout << "Compilation Command: " << compileCommand << endl;
    system(compileCommand.c_str());
}

void execute(const string &name, const string &language) {
    const string exename = name;
    string command = extension[language] + " env/" + exename + " " + ioeredirect;
    cout << "Execution Command: " << command << endl;
    system(command.c_str());
}

int main() {
    driver = sql::mysql::get_mysql_driver_instance();
    connection = driver->connect("tcp://localhost:3306", "root", "");    
    
    try {
        connection->setSchema("nexeum");
        statement = connection->createStatement();
        
        while (true) {
            result = statement->executeQuery(
                "SELECT rid, runs.pid as pid, tid, language, runs.name as name, "
                "runs.code as code, error, input, problems.output as output, "
                "timelimit, options FROM runs, problems WHERE problems.pid=runs.pid "
                "AND runs.access!='deleted' AND runs.result IS NULL AND "
                "runs.language IN " + languages + " ORDER BY rid ASC LIMIT 0, 1"
            );
            
            if (result->next()) {
                cout << "Found unjudged submission." << endl;
                
                string language = result->getString("language");
                cout << "Language: " << language << endl;

                string code = fileRead("env/code." + extension[language]);

                string filename = "env/" + result->getString("name") + "." + extension[language];
                fileWrite(filename, code);
                cout << "Code written to file: " << filename << endl;

                create(result->getString("name"), language);
                cout << "Code compiled." << endl;

                execute(result->getString("name"), language);
                cout << "Code executed." << endl;

                int timetaken = system("cat env/output.txt | wc -l");
                cout << "Time taken: " << timetaken << " seconds" << endl;

                string error = "No error";
                ifstream errorfile("env/error.txt");
                if (errorfile.is_open()) {
                    stringstream buffer;
                    buffer << errorfile.rdbuf();
                    error = buffer.str();
                }
                cout << "Error message: " << error << endl;

                string output = "No output";
                ifstream outputfile("env/output.txt");
                if (outputfile.is_open()) {
                    stringstream buffer;
                    buffer << outputfile.rdbuf();
                    output = buffer.str();
                }
                cout << "Program output: " << output << endl;

                statement->execute(
                    "UPDATE runs SET time=" + to_string(timetaken) + ", "
                    "result='" + language + "', error='" + error + "', "
                    "output='" + output + "' WHERE rid=" + result->getString("rid")
                );
                cout << "Submission result updated in the database." << endl;
            } else {
                cout << "No submissions to judge. Waiting..." << endl;
                sleep(5); 
            }
        }
    } catch (sql::SQLException &e) {
        cerr << "MySQL Error: " << e.what() << endl;
    }    
    
    statement->close();
    connection->close();
    delete statement;
    delete connection;
    
    return 0;
}
