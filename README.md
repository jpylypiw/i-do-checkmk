# i-do-checkmk

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/9cb7685f2f504877a39800e656d45c43)](https://www.codacy.com/app/KingJP/i-do-checkmk?utm_source=github.com&utm_medium=referral&utm_content=KingJP/i-do-checkmk&utm_campaign=badger)

This is a interface between Check_MK Monitoring and i-doit IT Documentation tool. 

**Contributors:** [kingjp](https://github.com/KingJP)  
**Requires at least:** PHP5.0  
**Tested up to:** PHP7.0  
**Stable tag:** 0.0.1   
**License:** [GPLv3 or later](https://github.com/KingJP/i-do-checkmk/blob/master/LICENSE)

## Description ##

The first question you might ask is: Why do you combine the two software? Check_MK contains its own inventory of software and hardware components. This information is read from each device.  
So why should'nt we use this information?

i-doit is a powerful software for inventorying and managing hardware and software. The maintenance of the information can be quite complex depending on the extent. For this reason, we have created the possibility to automatically synchronize the information from Check_MK with the information from i-doit.

## Installation ##

You can use i-do-checkmk on Windows an Linux platforms. It is written in PHP and needs at lease PHP Version 5.0. The script is optimized for use in the command line and does not contain a nice interface. We recommend that you use git to keep i-do-checkmk up-to-date. In the installation manual, we will show the use of git and the manual installation.

### Installation with git ###

Please follow these simple steps to install i-do-check_mk.

1. set up php-cli with curl addon on your system
2. `git clone https://github.com/KingJP/i-do-checkmk.git`
3. `cd i-do-checkmk`
4. open config.php and set up configuration
5. `php i-do-checkmk.php`
6. search for dependency errors in output

### Manual installation ###

The manual installation is more complicated, but still simple. Please follow the steps carefully.

1. set up php-cli with curl addon on your system
2. download i-do-checkmk from [GitHub master](https://github.com/KingJP/i-do-checkmk/archive/master.zip).
3. unzip master.zip and upload the content on your server
4. open the folder containing config.php
5. open config.php and set up configuration
6. `php i-do-checkmk.php`
7. search for dependency errors in output

## Updating ##

Updating with git is quite simple. You have to execute `git pull` in i-do-checkmk directory.  

Updating manually installed version is simple too. Save your config.php in another folder. Delete i-do-checkmk folder. Reinstall i-do-checkmk manually and replace config.php. 

## Changelog ##

### 0.0.1 (2017-05-06)

Features:

- Created Class for Check_MK API
- Created Class for checking PHP dependencies
- Added Code of Conduct

## Contributing ##

**Pull requests are welcome.** Have a look in out Wiki before posting an issue. Your question may be answered there. Please review the [code of conduct](https://github.com/KingJP/i-do-checkmk/blob/master/code_of_conduct.md).
