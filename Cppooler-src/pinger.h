#include <arpa/inet.h>
#include <sys/types.h>
#include <sys/param.h>
#include <sys/socket.h>
#include <sys/file.h>
#include <sys/time.h>

#include <netinet/in_systm.h>
#include <netinet/in.h>
#include <netinet/ip.h>
#include <netinet/ip_icmp.h>
#include <netdb.h>
#include <unistd.h>
#include <stdio.h>
#include <ctype.h>
#include <string.h>
#include <string>
#include <stdlib.h>
#include <stdint.h>
#include <iostream>
#include <fstream>
#include <sstream>
#include <map>
#include <pthread.h>
#include <dirent.h>

using namespace std;

#define USLEEP			500 //time to sleep between sending next ICMP request [usec]

//select timeout
//please note, that select thread is started BEFORE first ICMP request is sent, you must add some time (approx. (number of hosts)*USLEEP)
#define SELECT_TIMEOUT_SEC	2
#define SELECT_TIMEOUT_USEC	500000

#define ICMP_MINLEN	8
#define	MAXIPLEN	60
#define	MAXICMPLEN	76
#define	MAXPACKET	(65536 - 60 - ICMP_MINLEN)
#define	DEFDATALEN	(64-ICMP_MINLEN)


struct ping_target {
  ping_target(string ip_): ip(ip_), start(-1), end(0) {}
  string ip;
  unsigned long long start;
  unsigned long long end;
  short status;
};

class pinger {
public:
  pinger();
  void start();
  void wait_select();
  void raw_ping(ping_target * target);
private:
  uint16_t in_cksum(uint16_t *addr, unsigned len);
  static void * select_icmp(void* instance);
  map<int,ping_target*> id_map;
  int base_id;
  int seq_num;
  int sock;
  bool all_sent;
  pthread_t select_thread;
  pthread_mutex_t lock;
};