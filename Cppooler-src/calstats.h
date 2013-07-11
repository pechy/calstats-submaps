#include "pinger.h"

#define MAX_IN_BATCH 500

class calstats {
public:
  calstats(const char * datadir);
  ~calstats();
  void outputFiles();
  void parseFiles();
  void parseSubmaps();
private:
  void parseSubmap(string filename);
  bool parseFile(string filename);
  map<string,ping_target*> names;
  multimap<string,ping_target*> work;
  map<string,bool> submap_status;
  void sum_submap();
  string datadir;
  pinger p;
  int sent_in_batch;
};