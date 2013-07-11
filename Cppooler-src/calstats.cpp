#include "calstats.h"

calstats::calstats(const char * data) {
  datadir=string(data);
  if (data[strlen(data)-1]!='/') datadir.push_back('/');
  sent_in_batch=0;
}

calstats::~calstats() {
  for (std::multimap<string,ping_target*>::iterator it=work.begin(); it!=work.end(); it++) delete it->second;
}

void calstats::parseFiles() {
  struct dirent **namelist;
  int n = scandir(datadir.c_str(), &namelist, 0, alphasort);
  if (n > 0) {
      p.start();
      while(n--) {
	string filename=namelist[n]->d_name;
	if(filename.substr(filename.find_last_of(".") + 1) == "comp") {
	  filename=datadir+filename;
	  parseFile(filename);
	}
	free(namelist[n]);
      }
      free(namelist);
  }
  p.wait_select();
}

void calstats::parseSubmaps() {
  sum_submap();
  struct dirent **namelist;
  int n = scandir(datadir.c_str(), &namelist, 0, alphasort);
  if (n > 0) {
      while(n--) {
	string filename=namelist[n]->d_name;
	if(filename.substr(filename.find_last_of(".") + 1) == "submap") {
	  filename=datadir+filename;
	  parseSubmap(filename);
	}
	free(namelist[n]);
      }
      free(namelist);
  }
}

bool calstats::parseFile(string filename) {
  ifstream compFile;
  string line,ip,name;
  compFile.open(filename.c_str());
  string fn=filename.substr(0, filename.size()-5);
  while(getline(compFile, line)) {
    istringstream iss(line);
    getline(iss, name, ';');
    getline(iss, ip, ';');
    if (++sent_in_batch==MAX_IN_BATCH) { //wait before sending next
      p.wait_select();
      sent_in_batch=0;
      p.start();
    }
    ping_target* target=new ping_target(ip);
    work.insert ( std::pair<string,ping_target*>(fn,target));
    names.insert ( std::pair<string,ping_target*>(fn+name,target));
    p.raw_ping(target);
  }
  return true;
}
void calstats::outputFiles(){
  string last_fn="";
  ofstream f;
  for (multimap<string,ping_target*>::iterator it=work.begin(); it!=work.end(); it++) {
    if (it->first!=last_fn) {
      f.close();
      f.open((it->first+".state").c_str());
    }
    bool status=(it->second->end!=0);
    double delay;
    if (status) delay=it->second->end-it->second->start;
    else {
      delay=0;
      cout << it->second->ip << " did not responded"<<endl;
    }
    f << it->second->ip << ";" << it->second->status<<";"<<delay/1000<<endl;
    last_fn=it->first;
    }
  }

void calstats::sum_submap(){
  string last_fn="";
  bool status=true;
  for (multimap<string,ping_target*>::iterator it=work.begin(); it!=work.end(); it++) {
    if (it->first!=last_fn) {
      if (last_fn.length()!=0) submap_status.insert ( std::pair<string,bool>(last_fn,status));
      status=true;
    }
    it->second->status=(it->second->end!=0);
    if (status) status=it->second->status;
    last_fn=it->first;
  }
  submap_status.insert ( std::pair<string,bool>(last_fn,status));
  for (map<string,bool>::iterator it=submap_status.begin(); it!=submap_status.end(); it++) cout <<"submap: "<< it->first<<" - "<<it->second<<endl;
}

void calstats::parseSubmap(string filename) {
  ifstream submapFile;
  submapFile.open(filename.c_str());
  string fn=filename.substr(0, filename.size()-7);
  string line, name, sub;
  while(getline(submapFile, line)) {
    istringstream iss(line);
    getline(iss, name, ';');
    getline(iss, sub, ';');
    map <string, ping_target*>::iterator it_name=names.find(fn+name);
    if (it_name==names.end()) {cerr<<"name "<<name<<" from "<<filename<<" doesn't exist"<<endl; exit(1);}
    map <string, bool>::iterator it_submap=submap_status.find(datadir+sub);
    if (it_submap==submap_status.end()) {cerr<<"submap "<<sub<<" from "<<filename<<" doesn't exist"<<endl; exit(1);}
    if (it_name->second->status!=0) {
      if (it_submap->second) it_name->second->status=1;
      else it_name->second->status=3;
    }
  }
}