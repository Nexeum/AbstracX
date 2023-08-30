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


// sudo apt-get install libmysqlcppconn-dev
using namespace std;

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

std::string fileRead(const string &filename) {
    ifstream file(filename);
    stringstream buffer;
    buffer << file.rdbuf();
    return buffer.str();
}

void fileWrite(const string &filename, const string &data) {
    ofstream file(filename);
    file << data;
}

void create(const string &codefilename, const string &language) {
    std::map<string, string> compileCommands = {
        {"C", "gcc env/" + codefilename + ".c -lm -lcrypt -O2 -pipe -ansi -DONLINE_JUDGE -w -o env/" + codefilename + " " + ioeredirect},
        {"C++", "g++ env/" + codefilename + ".cpp -lm -lcrypt -O2 -pipe -DONLINE_JUDGE -o env/" + codefilename + " " + ioeredirect},
        {"C#", "mcs env/" + codefilename + ".cs -out:env/" + codefilename + ".exe " + ioeredirect},
        {"Java", "javac -g:none -Xlint -d env env/" + codefilename + ".java " + ioeredirect}
    };

    if (compileCommands.find(language) != compileCommands.end()) {
        string compileCommand = compileCommands[language];
        system(compileCommand.c_str());
    }
}

void execute(const string &exename, const string &language) {
    std::map<string, string> commandMapping = {
        {"C", "env/" + exename + " " + ioeredirect},
        {"C++", "env/" + exename + " " + ioeredirect},
        {"C#", "mono env/" + exename + ".exe " + ioeredirect},
        {"Java", "java -client -classpath env " + exename + " " + ioeredirect},
        {"Python", "python env/" + exename + " " + ioeredirect},
        {"Ruby", "ruby env/" + exename + ".rb " + ioeredirect}
    };

    if (commandMapping.find(language) != commandMapping.end()) {
        string command = commandMapping[language];
        system(command.c_str());
    }
}

int main() {
    driver = sql::mysql::get_mysql_driver_instance();
    connection = driver->connect("tcp://localhost:3306", "root", "");    
    
    try {
        connection->setSchema("nexeum");
        statement = connection->createStatement();
        
        while (true) {
            result = statement->executeQuery(
                "SELECT rid, runs.pid as pid, tid, language, runs.name, "
                "runs.code as code, error, input, problems.output as output, "
                "timelimit, options FROM runs, problems WHERE problems.pid=runs.pid "
                "AND runs.access!='deleted' AND runs.result IS NULL AND "
                "runs.language IN " + languages + " ORDER BY rid ASC LIMIT 0, 1"
            );
            
            if (result->rowsCount() > 0) {
                std::string code = fileRead("env/code." + extension[result["language"]]);
                fileWrite("env/" + result["name"] + "." + extension[result["language"]], code);
                
                create(result["name"], result["language"]);
                execute(result["name"], result["language"]);
                
                statement->execute(
                    "UPDATE runs SET time=" + std::to_string(timetaken) + ", "
                    "result='" + result + "', error='" + error + "', "
                    "output='" + output + "' WHERE rid=" + result["rid"]
                );
            } else {
                std::cout << "No hay presentaciones para juzgar. Esperando..." << std::endl;
                sleep(5); 
            }
        }
    } catch (sql::SQLException &e) {
        std::cerr << "MySQL Error: " << e.what() << std::endl;
    }
    
    statement->close();
    connection->close();
    delete statement;
    delete connection;
    
    return 0;
}
