# Backend
###

[![SERVER](https://i.imgur.com/oEDUVoK.png)](https://i.imgur.com/oEDUVoK.png)

````
inewlegend@leonid-server:~/Share/AppBasis$ php appbasis.php
[2019-06-16 16:47:15.571371][DEBUG][0000000000][s\AppBasis][Modules\Logger][initialize]: created new logger with name: `s\AppBasis` caller method: `appbasis.php::entryPoint`
[2019-06-16 16:47:15.571979][DEBUG][0000000001][s\Core\Auxiliary][Modules\Logger][initialize]: created new logger with name: `s\Core\Auxiliary` caller method: `Core\Auxiliary::boot`
[2019-06-16 16:47:15.572031][NOTICE][0000000000][s\AppBasis][AppBasis][main]: start with command: `["welcome","index",[]]`
[2019-06-16 16:47:15.572069][INFO][0000000000][s\AppBasis][AppBasis][main]: commands: >>
{
    "[ SYNTAX ]": "php appbasis.php <command> <method> <param1> <param2> [ etc ... ]",
    "[ Command ]": "[ Description ]",
    "0": "-------------------------------------",
    "welcome": "Show this screen",
    "reload": "Reload server vendor",
    "server": "Run server",
    "update": "Update core",
    "backup": "Create self backup",
    "release": "Create release",
    "template": {
        "php appbasis.php template index": "Create new template"
    },
    "plugin": {
        "php appbasis.php plugin <plugin1> <plugin2> [ etc ... ]": "Run Plugin"
    }
}
[2019-06-16 16:47:15.572120][DEBUG][0000000000][s\AppBasis][Modules\Logger][__destruct]: destroying logger with name: `s\AppBasis` instance: `0000000000`
[2019-06-16 16:47:15.572152][DEBUG][0000000001][s\Core\Auxiliary][Modules\Logger][__destruct]: destroying logger with name: `s\Core\Auxiliary` instance: `0000000001`
````

## Benchmark;
````
inewlegend@leonid-server:~$ ab -n10000 -c100 -k http://127.0.0.1:51194/
This is ApacheBench, Version 2.3 <$Revision: 1807734 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 127.0.0.1 (be patient)
Completed 1000 requests
Completed 2000 requests
Completed 3000 requests
Completed 4000 requests
Completed 5000 requests
Completed 6000 requests
Completed 7000 requests
Completed 8000 requests
Completed 9000 requests
Completed 10000 requests
Finished 10000 requests


Server Software:        
Server Hostname:        127.0.0.1
Server Port:            51194

Document Path:          /
Document Length:        62 bytes

Concurrency Level:      100
Time taken for tests:   18.297 seconds
Complete requests:      10000
Failed requests:        0
Keep-Alive requests:    0
Total transferred:      3130000 bytes
HTML transferred:       620000 bytes
Requests per second:    546.53 [#/sec] (mean)
Time per request:       182.971 [ms] (mean)
Time per request:       1.830 [ms] (mean, across all concurrent requests)
Transfer rate:          167.06 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.4      0       6
Processing:    45  182  11.8    181     207
Waiting:       45  182  11.8    181     206
Total:         51  182  11.5    182     207

Percentage of the requests served within a certain time (ms)
  50%    182
  66%    183
  75%    184
  80%    185
  90%    193
  95%    196
  98%    198
  99%    199
 100%    207 (longest request)
inewlegend@leonid-server:~$ lscpu
Architecture:        x86_64
CPU op-mode(s):      32-bit, 64-bit
Byte Order:          Little Endian
CPU(s):              4
On-line CPU(s) list: 0-3
Thread(s) per core:  1
Core(s) per socket:  4
Socket(s):           1
NUMA node(s):        1
Vendor ID:           GenuineIntel
CPU family:          6
Model:               42
Model name:          Intel(R) Core(TM) i5-2400 CPU @ 3.10GHz
Stepping:            7
CPU MHz:             1823.779
CPU max MHz:         3400.0000
CPU min MHz:         1600.0000
BogoMIPS:            6185.41
Virtualization:      VT-x
L1d cache:           32K
L1i cache:           32K
L2 cache:            256K
L3 cache:            6144K
NUMA node0 CPU(s):   0-3
Flags:               fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush dts acpi mmx fxsr sse sse2 ss ht tm pbe syscall nx rdtscp lm constant_tsc arch_perfmon pebs bts rep_good nopl xtopology nonstop_tsc cpuid aperfmperf pni pclmulqdq dtes64 monitor ds_cpl vmx smx est tm2 ssse3 cx16 xtpr pdcm pcid sse4_1 sse4_2 x2apic popcnt tsc_deadline_timer aes xsave avx lahf_lm epb pti ssbd ibrs ibpb stibp tpr_shadow vnmi flexpriority ept vpid xsaveopt dtherm ida arat pln pts md_clear flush_l1d
inewlegend@leonid-server:~$ free -m
              total        used        free      shared  buff/cache   available
Mem:           7871        4367         987         529        2516        2853
Swap:          2047        1939         108
````

## Folders;
    config - 
    controllers - 
    core - 
    friends - server engine
    guards - protect controllers
    library -
    models - 
    modules -
    services -
    ext - plugins

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