# AppBasis
  - Base for Angular client and PHP server.

## TODO
#### Server:
  -  if server is non active for long time, the MySQL Connection is gone away.
  -  -  Option #1: make keepAlive method
  -  -  Option #2: something like DBConnectionPool
  -  add log to file with rotate.
  
#### Client:
  -  logger should be better, its should better explain about client architecture
  -  handle in nice way situation when server is offline
  -  in api clients add socket.ts for live connection

#### Both:
  -  add switch for debug\production mode
  -  add mechanism of regeneration auth tokens 