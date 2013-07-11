calstats-submaps
================
This is extension to CaLStats, network monitoring tool made by Jan Krupa (http://www.mobilnews.cz/honza/calstats).  
This extension allows to create more complex network maps with submaps.  
  
Configuration files that worked with original CaLStats works with this version too, for using submaps you just need to create .submap file (see example for details, it's pretty straightforward).  
After creating .submap file, you can run script gen_html, it generates html and tries to place submaps (currently the code for placing submaps is not by far perfect, sometimes you must edit positions in html manually).  
  
Example
-------
Simple example is [here](https://rawgithub.com/pechy/calstats-submaps/master/example/out/example.html). This example was generated using config files in example/data.  
  
  
C++ pooler
----------
There is also included C++ pooler (Cppooler) for very fast checking of reachability. Please note that the Cppooler is experimental and not yet properly tested.  
To build it, type *make* (libpthread-dev is needed). Cppooler requires root privilege (because of RAW sockets).  
  
Cppooler loads all IPs from comp files, send ICMP request to all of them and listens for replies in second thread. Cppooler also handles submaps by itself, no another script is needed.  