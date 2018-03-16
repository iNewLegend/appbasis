# AppBasis

#### Server:
  -  if server is non active for long time, the MySQL Connection is gone away.
  -  -  Option #1: make keepAlive method
  -  -  Option #2: something like DBConnectionPool
  
#### Client:
  -  logger should be better, its should better explain about client architecture
  -  handle in nice way situation when server is offline
