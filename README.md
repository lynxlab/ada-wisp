WISP
==========
WISP stands for __Web Integrated Services Provider__. It is a request/support management system that allows registered users to ask for help or consultancy, to manage these requests and to activate support sessions via chat or videoconference.

CONTENTS OF THIS FILE
---------------
- Requirements
- How to install
- Customize the layout
- Localization and languages
- Extra configuration
- System setup
- Add one provider
- user preconfigured

REQUIREMENTS
--------------
- Apache 2.2.21>
- MySql 5.1.6 >
- PHP 5.3.*> compiled with support for MySql, XML, GD, PDO
- PEAR::XML_Parser 1.3.x>
- PEAR 1.9.x> 
- PEAR::XML_Util 1.2.x>

HOW TO INSTALL
--------------
1. copy all files and directory tree in root directory

2. create at least two DB (each courses' provider has his own DB. if you have 2 providers, you need 3 DB.
  One common DB and 2 providers DB.
  + ada_common --> DB common.
  + ada_provider0 --> DB of the provider 0. 
  + ada_provider1 --> DB of the provider 1. 

3. import ada_common.sql in ada_common

4. import ada_provider0.sql in ada_provider0

5. import ada_provider_empty.sql in ada_provider1 (if you need one more provider)

6. copy config_path_DEFAULT.inc.php in config_path.inc.php

7. modify config_path.inc.php writing the correct root path
   ex. if you have copied the files and directory in /var/www/wisp you have to modify it in the follow way:
  + define('ROOT_DIR','/var/www/html/wisp');

8. copy config/config_install_DEFAULT.inc.php in config/config_install.inc.php

9. modify config/config_install.inc.php
  + change the * WISP Common database section
  + change the * WISP default provider
  + change the * Default admin mail address
  + change the URL define. Pay attention to not remove the trailing // *js_import*
    define('HTTP_ROOT_DIR','http://ada.lynxlab.com');
  + optionally change the Default template family (only if you want to use a different layout family)
  + optionally change define('PORTAL_NAME','ADA - e-learning ');

10. copy the directory clients_DEFAULT in clients

11. modify clients/client0/client_config.inc.php (each provider has own directory. es.: provider 1 has client1)
    + change the DB access parameter
    + change the timezone of the provider if you need

12. if you have more providers you have to create more client_config.inc.php
    es.: 2 providers. You must have clients/client0/client_config.inc.php and clients/client1/client_config.inc.php.
    in client1/client_config.inc.php the constant name must be CLIENT1_DB_NAME, CLIENT1_DB_USER, CLIENT1_DB_PASS, CLIENT1_DB_HOST, CLIENT1_TIMEZONE

13. change the permission of the directory services/media/ the web server must be able to write in it

14. change the permission of the directory upload_file/ the web server must be able to write in it

15. change the permission of the directory docs/ the web server must be able to write in it

16. change the informations of the news editing the file: browsing/news_language (ex.: news_en is the news in english) 
    or using edit_news.php after logon as admin

17. configure the widgets loaded in home page
    copy widgets/main/index_DEFAULT.XML in widgets/main/index.xml
    see widgets/main/index.xml in order to know how to configure


CUSTOMIZE THE LAYOUT
--------------
customize the layout in the directory templates and css.

**The file layout/layout_family/header.tpl contains the header of all pages. 
You can change the logo and the header modifying the file layout/layout_family/header.tpl**

The structure of the directories that contain the layout is:
- layout/layout_family/css/module_name
- layout/layout_family/templates/module_name
- layout/layout_family/img/

- js
	/module_name/

LOCALIZATION AND LANGUAGES
--------------
At the moment WISP is translated in the following languages:
- english
- italian
- spanish
- hungarian

Each translation is stored in a table contained in the DB common.
the name of the table is messaggi_language (ex.: english messaggi_en)

### Translation of messages and GUI ###
  The system translates at real time all the interface (buttons, links, labels) and all the messages that are to be sent to the user (welcome message excluded, see below).
  After logging into WISP as Switcher or Admin, go to this address:
  http://your_domain_of_wisp/switcher/translation.php

  You'll see a small form to search the sentences or part of them.
  * Write in this form the sentence you want to translate (or modify)
  * The system will show a list of the sentences similar to the one you wrote in the form, if any.
  * click on modify in the line in which you can read the sentence you wish to translate.
  * replace the missing or wrong sentence with the correct one
  * click on "update"
  * Go back

### Note and suggestions. ###
  In order to have the experience of WISP use and to check the correct translation in context,
  we suggest you to open two different web browsers (NOT two windows of the same browser),
  say A. Firefox
  and B. Google Chrome.

  - In browser "A" you can login as user or pratitioner or switcher and use the WISP platform normally.
  - In browser "B" you have to login as switcher and go to the translation module
  - When, navigating in browser "A", you may find a sentence not translated in your language, or with a wrong translation,
  - in browser "B" you can search for that sentence and change its translation
  Note that after the translation of each sentence you have to reload the page in the browser "A" in order to see the newly translated sentence

### How to add a language. ###
  - You have to add a record to the table "lingue" (which means language in italian) contained in the db common using a tool like phpmyadmin.
    ex.: to add french language you have to add a record like this id: 7, name language: français, code: fr, table identifier: fr,
  - You can copy the table messaggi_en contained in the db common to messaggi_language using a tool like phpmyadmin (ex.: french messaggi_fr).
  - you can use the translation module (see Translation of messagges and GUI section above)
    or in alternative you can export the content of the table, translate all and reimport the table using a tool like phpmyadmin.

EXTRA CONFIGURATION
-----------------

### Welcome message mail ###
  You can change the text of the welcome message sended to the user just registered:
  /docs/welcome_language.txt (ex.: /docs/welcome_en.txt english message)

### Help ###
  the directory docs contains also the help for the user. You can change the help by editing each single file.

### Public course ###
You can set **one public course for provider 0** (in case MULTIPROVIDER is set to true, otherwise each provider has his own public course defined in clientX.inc.php by the value of the constant PUBLIC_COURSE_ID_FOR_NEWS).   
**The default public id course of provider 0 is definied in config/config_install.inc.php by the value of the constant PUBLIC_COURSE_ID_FOR_NEWS**

SYSTEM SETUP
-------------
1. **change the news in home page**.
   You can change the news from inside the platform, logging in with administer user and clicking the voice "edit news" in act (or do) menù
   You can also modify it, changing the files docs/news/news_language.txt (es.: english news_en.txt)
   You can open it and change using any text editor. It is possibile to use HTML tags.

2. **Create the users of type switcher (coordinator) and Tutors.**
   How to do:
   - log on with the platform Admin,
   - click on Do menù,
   - click on add user,
   - choose the provider to which associate the user.

3. **Arrangement of services provided**

   How the WISP platform works
   + In the WISP platform (DB common) there is the list of potential services
   + Each provider holds its own instance of service (they are saved in DBs whose name is found in the clientX/client_conf.inc.php file)
   + Each service created by the provider is (automatically) linked to the platform  (saved in DB common).
   + Each provider has to create at least one instance of the service in order to allow the studentes to subscribe the instance

   How to do:
   + log on with the switcher account,
   + click on Do menù,
   + click on add a service
   + click on add instance, near the service

### Notes ###
   Users have to register in the platform in order to ask for a service. (users registered in WISP receive an email to confirm the registration)

Add one provider
--------------
to add a provider, you need to do the following tasks:
- create a new DB
- import the sql ada_provider_empty.sql into the new DB
- follow the steps 11 and 12 
- login as admin 
- create a new provider (from menù actions)
  take care to write in the field "Puntatore al database" the same name of the directory contained in clients (ex.: client1)
- create the new coordinator user for the provider just created

predefined USERS are:
--------------
- adminAda --> Super Admin
- autoreAda0 --> author of first provider
- tutorAda0 --> tutor of first provider 
- switcherAda0 --> Admin of first provider
- studenteAda0 --> Student of first provider

passwords are the same of the username

