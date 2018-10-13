# Backend
###

[![SERVER](https://i.imgur.com/oEDUVoK.png)](https://i.imgur.com/oEDUVoK.png)

````
$ php appbasis.php

[2018-10-13 10:35:13.640896][DEBUG][0000000000][s\AppBasis][Modules\Logger][initialize]: created new logger with name: `s\AppBasis` caller method: `appbasis.php::enteryPoint`
[2018-10-13 10:35:13.641265][DEBUG][0000000001][s\Core\Auxiliary][Modules\Logger][initialize]: created new logger with name: `s\Core\Auxiliary` caller method: `Core\Auxiliary::boot`
[2018-10-13 10:35:13.641315][INFO][0000000000][s\AppBasis][AppBasis][main]: start with command: `["welcome","index",[]]`
[2018-10-13 10:35:13.641348][INFO][0000000000][s\AppBasis][AppBasis][main]: commands: >>
{
    "[ SYNTAX ]": "php appbasis.php <command> <method> <param1> <param2> [ etc ... ]",
    "[ Command ]": "[ Description ]",
    "0": "-------------------------------------",
    "welcome": "Show this screen",
    "reload": "Reload server vendor",
    "server": "Run server",
    "update": "Update core",
    "backup": "Create self backup",
    "template": {
        "Create new template": "php appbasis.php template index"
    }
}
[2018-10-13 10:35:13.641399][DEBUG][0000000000][s\AppBasis][Modules\Logger][__destruct]: destroying logger with name: `s\AppBasis` instance: `0000000000`
[2018-10-13 10:35:13.641436][DEBUG][0000000001][s\Core\Auxiliary][Modules\Logger][__destruct]: destroying logger with name: `s\Core\Auxiliary` instance: `0000000001`

````

## Folders;
    config - 
    controllers - 
    core - 
    friends - you need a friend to use appbasis
    guards - protect controllers
    library -
    models - 
    modules -
    services -

## Files;

#### [*] file names most be one word and small (modesty)  

### File Headers;
```
/**
 * @file: parentFolder/filename.php
 * @author [freedom] <[real][email]>
 */
 ```
### File Footer;
```
} // EOF parentFolder/filename.php
```
### Code access;
### Core ->
* core: yes
* library: yes
* modules : yes
* services: yes
* controllers: yes
### Modules ->
* core: yes
* library: yes
* modules : yes
* services: no
* controllers: no
### Services ->
* core: yes
* library: yes
* modules: yes
* services: yes
* controllers: no
### Library ->
* core: no
* library: yes
* modules : no
* services: no
* controllers: no



## Ideas (any source of inspiration)
### [-] Database issue with connection gone away read next link https://en.wikipedia.org/wiki/Exponential_backoff
### [-] For auth regeneration tokens read about next link https://www.google.bg/search?q=key+rotations&oq=key+rotations&aqs=chrome..69i57j0.207j0j1&sourceid=chrome&ie=UTF-8
