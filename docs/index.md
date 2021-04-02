# wwwwServer - Web Server 
[ https://github.com/X0xx-1110/wwwwServer ]

[![GitHub status](https://github.com/X0xx-1110/WWWW-Server/actions/workflows/codacy-analysis.yml/badge.svg)](https://github.com/X0xx-1110/WWWW-Server)
[![Documentation Status](https://readthedocs.org/projects/ansicolortags/badge/?version=latest)](http://ansicolortags.readthedocs.io/?badge=latest)
[![GitHub license](https://img.shields.io/github/license/X0xx-1110/WWWW-Server.svg)](https://github.com/X0xx-1110/WWWW-Server/blob/master/LICENSE)
[![GitHub forks](https://img.shields.io/github/forks/X0xx-1110/WWWW-Server.svg?style=social&label=Fork&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/network/)
[![GitHub stars](https://img.shields.io/github/stars/X0xx-1110/WWWW-Server.svg?style=social&label=Star&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/stargazers/)
[![GitHub watchers](https://img.shields.io/github/watchers/X0xx-1110/WWWW-Server.svg?style=social&label=Watch&maxAge=2592000)](https://GitHub.com/X0xx-1110/WWWW-Server/watchers/)
[![GitHub followers](https://img.shields.io/github/followers/X0xx-1110.svg?style=social&label=Follow&maxAge=2592000)](https://github.com/X0xx-1110?tab=followers)

WwwwServer is high speed, secure and stable web server. Includes inside meny mime types of all. 
There are possible examination of GET, POST variable about more capabilities. Includes about fast optimization and testing of all aspects on web developing.
Stable than ever and secure, of course.

## Open Source 

It's only open source.  [![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/X0xx-1110/WWWW-Server/blob/main/LICENSE)


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


### *** And Of course if you made changes over web_server.php you need to restart it into terminal again.



## Running the tests

```bash
sudo php8.0 web_server.php
```

Then we get the web server on (http://127.0.0.1:8282/)


## Example: [![Version](https://badge.fury.io/gh/tterb%2FHyde.svg)](https://badge.fury.io/gh/tterb%2FHyde)
Inside your browser (http://127.0.0.1:8282/index.php)


## Example 2: If you want close security checks then:
```php
private bool $_securityArrayStatuses = false;
private bool $_securityFilesWebStataStuses = false;
```

## or 


## Example 3: If you want to open files pdf, txt, jpeg or html you must to add exactly file to property:


```php
private array $_securityFilesWeb = [ "", "index.php", "index.html", "index.htm" , "aaaaaaAAA.jpeg", "BaBash.txt"];
```


### *** And Of course if you made changes over web_server.php you need to restart it into terminal again.



## Example 4; How could add a content-type:
Just need to add file extension and content-type to your mime.json file.



## Latest 

### [ GET ] ! 
### [ POST ] ! 
### [ GZIP and Deflate] ! 
### Cache 
### Cookie 
### PUT 
### [ MIME ]
### [ LOG ]
### [ HEAD ]
### [ PING ]



## Features

  * Further over all maybe there are variant and for parallel port usage.
  * Obviously the quality of code is more strong than else.
  * There needs about dynamically port change and further work on it.
  * Get needs and variants about rendering, else with no case.
  * Least dynamically create and use encodings of a page may be are big, of course.
  * Possible about of course and SSL.
  * more usefull.
  * most secure.
  * more stable.


## Ideas and Target

* Main goal is to create something workfull about our developing needs.
* In second case I will try to give more about what could be.
* Open source ideas - there get chance to give more, create more and develop more.
* Creating ideas, create open source, creating, developing.
* There could many functionality will increase.

## Authors [![Code of Conduct](https://img.shields.io/badge/code%20of-conduct-ff69b4.svg?style=flat)](https://github.com/X0xx-1110/WWWW-Server/blob/main/CODE_OF_CONDUCT.md)

* **Kaloyan Hristov** - (https://github.com/X0xx-1110)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.



## License [![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/X0xx-1110/WWWW-Server/blob/main/LICENSE)

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details



## Feedback 

### "Ask Me all over"  [![Ask Me all over net!](https://img.shields.io/badge/Ask%20me%20all%20over-1abc9c.svg)](https://github.com/X0xx-1110/wwwwServer)

if there are something missing, or something more, or something else.
Ask me anything!



## Acknowledgments

* About inspiration and motivation all happans with these skills.
* Inspiration at all, but over that we will be better with good ideas.
* About Inspiration, motivation, ideas of all creative dreams araound.
* And so on.
