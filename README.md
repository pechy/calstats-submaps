calstats-submaps
================
This is extension to CaLStats, network monitoring tool made by Jan Krupa (http://www.mobilnews.cz/honza/calstats).  
This extension allows to create more complex network maps with submaps.  
  
Configuration files that worked with original CaLStats works with this version too, for using submaps you just need to create .submap file (see example for details, it's pretty straightforward).  
After creating .submap file, you can run script gen_html, it generates html and tries to place submaps (currently the code for placing submaps is not by far perfect, sometimes you must edit positions in html manually).  

Example
-------
Simple example is [here](https://rawgithub.com/pechy/calstats-submaps/master/example/out/example.html). This example was generated using config files in example/data.