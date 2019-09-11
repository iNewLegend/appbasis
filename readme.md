# AppBasis
  - backend - ReactPHP Modular server base
  	  - configs, controllers, guards, models, modules, library, friends
      - core -> main functionality
      - configs -> config
      - controllers -> controller logic
      - guards -> secure controllers when they need extra conditioning
      - models -> handle database queries
      - library -> extra functions
      - services -> .
      - friends -> server engine
      - ext -> plugins
  
  - frontend(s)
    - Simple Angular, React base.
    - Nice Api Structure
    - WebSocket Support
    
# Status
  Backend: `Development`
  
  Angular Frontend: `Development`
  
  React Frontend: `Not working yet.`

## Road Map
  - todo

## Goals
  - Full async.
  - Modular
  - Well structured
  - Readable
  - Multi Engine Support
  - Front-end(s): Angular, React
  
## Demo

http://138.201.155.5/leo123/dist

#### Video:

[![YouTube](https://i.ytimg.com/vi/PaGjC5L8tz8/0.jpg)](https://youtu.be/PaGjC5L8tz8)

#### Backend:
[![SERVER](https://i.imgur.com/oEDUVoK.png)](https://github.com/iNewLegend/AppBasis/tree/master/doc/backend.md)

#### Frontend:
[![CLIENT](https://i.imgur.com/oxoqz23.png)](https://github.com/iNewLegend/AppBasis/tree/master/doc/frontend.md)

# Install
```sh
$ git clone https://github.com/iNewLegend/AppBasis.git
```
### Backend
```sh
$ mysql
$ > create database appbasis
$ > quit
$ mysql < appbasis.sql
$ cd appbasis
$ composer update
$ mysql 
$ php appbasis.php server
```
### Frontend
```sh
$ cd frontend
$ ng serve
```

## TODO
#### Backend:
  -  add: log to file with rotate.
  -  avoid: all core classes should not have direct creation of classes, you `auxiliary` instead.
  -  think: add base class for all core classes 
  -  check: captcha should be async.
  -  avoid: try to avoid as much as possible try and catch. 
  -  add: unique email on database tbl: users
  -  issue: security when u have successfully login you delete all `bad` attempts 
  -  check-add: check at `controllers/welcome` method `updates` 
    - add method for async remote requests
    - add cache (check-if-good: https://github.com/reactphp/cache)    
  -  re-construct: `core/container` add methods
  -  optimize: logger
  -  change: Config Service logs are not understand able.
  -  check: search for all `mixed` word in project, and be smart.
  -  add: in database, to handle created_at and updated_at :
    - https://medium.com/@bengarvey/use-an-updated-at-column-in-your-mysql-table-and-make-it-update-automatically-6bf010873e6a
  -  add: security `wrk -t4 -c500 -d10s http://localhost:51190/authorization/login/czf.leo123@gmail.com/badpass`
  -  change: at chat controller function hook is called by Core\Handler it should be an implantation
  -  add: document appbasis.php commands.
  -  add: database anti injection, using query parameters

#### Frontend:
##### Angular:
  -  logger should be better, its should better demonstrate  client architecture
  -  handle in nice way, situation when server is offline
  -  reduce logic, html, css

##### React:


#### Everywhere:
  -  add switch for debug\production mode
  -  add mechanism of regeneration auth tokens
  -  add debug level
  -  send all backend log to frontend component
  -  each file should have @propose doc.
  -  add user access privileges 
        admin panel for user editing
        blog editing / posting for editor 
  - add protocol documenation, usage exmaple
  - add OAuth2 as plugin
