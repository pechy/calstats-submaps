all:
	g++ -g -lpthread  Cppooler-src/main.cpp Cppooler-src/calstats.cpp Cppooler-src/pinger.cpp -o Cppooler
