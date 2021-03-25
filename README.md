# WWWW SERVER - Web Server
[![GitHub status](https://github.com/X0xx-1110/WWWW-Server/actions/workflows/codacy-analysis.yml/badge.svg)](https://github.com/X0xx-1110/WWWW-Server)
[![Documentation Status](https://readthedocs.org/projects/ansicolortags/badge/?version=latest)](http://ansicolortags.readthedocs.io/?badge=latest)
[![GitHub license](https://img.shields.io/github/license/X0xx-1110/WWWW-Server.svg)](https://github.com/X0xx-1110/WWWW-Server/blob/master/LICENSE)
<<<<<<< HEAD
=======
[![GitHub release](https://img.shields.io/github/security/X0xx-1110/WWWW-Server.svg)](https://GitHub.com/X0xx-1110/WWWW-Server/blob/master/SECURITY.md)
>>>>>>> 26227d9addb61beeb454e50431577f9cde08f9fc
[![GitHub forks](https://img.shields.io/github/forks/X0xx-1110/WWWW-Server.svg?style=social&label=Fork&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/network/)
[![GitHub stars](https://img.shields.io/github/stars/X0xx-1110/WWWW-Server.svg?style=social&label=Star&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/stargazers/)
[![GitHub watchers](https://img.shields.io/github/watchers/X0xx-1110/WWWW-Server.svg?style=social&label=Watch&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/watchers/)
[![GitHub followers](https://img.shields.io/github/followers/X0xx-1110.svg?style=social&label=Follow&maxAge=2592000)](https://github.com/X0xx-1110?tab=followers)

WwwwServer is high speed, secure and stable web server. Includes inside html, htm, txt, xml, php5, php7.0, php7.4, php8.0 rending of all. There are possible communication with x_GET variable about more capabilities. Includes capabilities about fast optimization and testing of all aspects on web developing.
More stable than ever.

## Getting Started [![Documentation Status](https://readthedocs.org/projects/ansicolortags/badge/?version=latest)](http://ansicolortags.readthedocs.io/?badge=latest)

1. When you are ready about starting new web server you must to specify number of the port, that will.
2. Then must  to specify and directory about your web files. Where are they?
3. These things you could done inside file web_server.php at the end.
4. Be careful within directory, you could browse in files only if there are in.

## SECURITY
### About first secure property -  (array) $securityArray .
These characters are excluded about the url and there get console error.
### About second secure property -  (array) $securityFilesWeb
These strings of file names are possible variants about started file into url,
else there are console error.


## Starting
```php
$server = new WwwwServer();
$server->httpServer(8282, "/home/XXXX/Desktop/www1/" );
```

## Running the tests

```bash
sudo php8.0 web_server.php
```
Then we get the web server on (http://127.0.0.1:8282/)



## Example: [![Version](https://badge.fury.io/gh/tterb%2FHyde.svg)](https://badge.fury.io/gh/tterb%2FHyde)
Inside your browser (http://127.0.0.1:8282/index.php)


## Example of dynamically variables about GET protocol:
### Step1: Configuration.
```php
private $dynamicallyVars = true;
```

### Step 2: Inside browser.
```uri
http://127.0.0.1:8282/index.php?var1=78787823&var2=3874837&var3=news
```

### Step 3: Inside your code.
```php
echo $GETvar1;
echo "<br />";
echo $GETvar2;
echo "<br />";
echo $GETvar3;
```

## Example of standart variables about GET protocol:
### Step1: Configuration.
```php
private $dynamicallyVars = false;
```

### Step 2: Inside browser.
```uri
http://127.0.0.1:8282/index.php?var1=78787823&var2=3874837&var3=news
```

### Step 3: Inside your code.
```php
echo $_GET["var1"];
echo "<br />";
echo $_GET["var2"];
echo "<br />";
echo $_GET["var3"];
```


## Feature
  * Further there are variants about GET, POST and HEAD, else obviously and other variants are needs at today.
  * Further over all maybe there are variant and for parallel port usage.
  * Obviously the quality of code is more strong than else.
  * There could be the effectively common generated dynamically variables about POST,GET,HEAD.
  * The variables could be $POSTtextOfAll, $POSTtextCrapped, $GETpageNotFound, $HEADcheck, $HEADcheckAtNow.
  * There needs about dynamically port change and further work on it.
  * We get needs and variants about rendering, else with no case.
  * Least dynamically create and use encodings of a page may be are big, of course.
  * Possible about of course and SSL.

## Authors [![Code of Conduct](https://img.shields.io/badge/code%20of-conduct-ff69b4.svg?style=flat)](https://github.com/X0xx-1110/WWWW-Server/blob/main/CODE_OF_CONDUCT.md)

* **Kaloyan Hristov** - (https://github.com/X0xx-1110)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License [![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/X0xx-1110/WWWW-Server/blob/main/LICENSE)

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* Inspiration about it all.
* Inspiration at all.
* About Inspiration
