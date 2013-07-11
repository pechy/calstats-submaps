#include "calstats.h"

int main( int argc, char* argv[] )
{
  if (argc!=2) {
    cerr << "usage: ./calstats_pooler datadir"<<endl;
    exit(1);
  }
  calstats c (argv[1]);
  c.parseFiles();
  c.parseSubmaps();
  c.outputFiles();
  return 0;
}