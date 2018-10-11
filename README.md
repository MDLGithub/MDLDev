#Introduction 
TODO: Give a short introduction of your project. Let this section explain the objectives or the motivation behind this project. 

#Getting Started
TODO: Guide users through getting your code up and running on their own system. In this section you can talk about:
1.	Installation process
2.	Software dependencies
3.	Latest releases
4.	API references

#Build and Test
TODO: Describe and show how to build your code and run the tests. 

#Contribute
TODO: Explain how other users and developers can contribute to make your code better. 

If you want to learn more about creating good readme files then refer the following [guidelines](https://www.visualstudio.com/en-us/docs/git/create-a-readme). You can also seek inspiration from the below readme files:
- [ASP.NET Core](https://github.com/aspnet/Home)
- [Visual Studio Code](https://github.com/Microsoft/vscode)
- [Chakra Core](https://github.com/Microsoft/ChakraCore)


#Updated files list on 06-Oct-2018
- /login/dashboard2.php
- /login/eventschedule.php
- /login/eventupdate.php
- /login/assets/css/custom-styles.css
- /login/assets/css/calendar.css

#Table structure for Comments
Table Name - tblcomments
Columns:
- id : INT(11), Primary key, Auto-increment
- comments : LONGTEXT
- user_id : INT(11)
- eventid : INT(11)
- created_date: DATETIME
- updated_date: DATETIME

#Updated file lists on 08-Oct-2018
- /login/dashboard2.php
- /login/eventschedule.php
- /login/assets/css/calendar.css

#Added new file on 08-Oct-2018
- /login/ajaxHandlerEvents.php

#Alter table tbluser on 08-Oct-2018
Since need to show comments with Names instead of emailID, but there was no fields existed related to Username or Name in tbluser. There are names existed only for Salesrep (in tblsalesrep), but for remaining users it is null.
For this reason, I have altered tbluser (Added 2 fields):
- firstname varchar(255)
- lastname varchar(255)


#Updated files list on 11-10-2018

- /login/ajaxHandlerEvents.php
- /login/eventschedule.php
- /login/functions_event.php
- /login/dashboard2.php
- /login/topgenetic.php
- /login/assets/css/calendar.css
- /login/assets/css/custom-styles.css

