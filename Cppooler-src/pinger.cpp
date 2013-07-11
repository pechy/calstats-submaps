#include "pinger.h"

unsigned long long Microtime () {
  timeval time;
  gettimeofday(&time, NULL);
  return (unsigned long long)time.tv_sec*1000000 + time.tv_usec;
}

void pinger::wait_select(){
  all_sent=true;
  pthread_join(select_thread,NULL);
}

pinger::pinger() {
  seq_num=getpid();
  if ( (sock = socket(AF_INET, SOCK_RAW, IPPROTO_ICMP)) < 0){
    cerr << "cannot create socket - probably not superuser (needed for SOCKET_RAW)"<<endl;
    exit(1);
  }
  pthread_mutex_init(&lock, NULL);
}

void pinger::start() {
  id_map.clear();
  all_sent=false;
  base_id=10000+rand()%50000;
  pthread_create(&select_thread,NULL,pinger::select_icmp,this);
}
void pinger::raw_ping(ping_target * target) {
  int i, cc, datalen = DEFDATALEN;
  struct sockaddr_in to;
  u_char outpack[MAXPACKET];
  string hostname;
  struct icmp *icp;
  to.sin_family = AF_INET;
  to.sin_addr.s_addr = inet_addr(target->ip.c_str());
  if (to.sin_addr.s_addr != (u_int)-1) hostname = target->ip;
  icp = (struct icmp *)outpack;
  icp->icmp_type = ICMP_ECHO;
  icp->icmp_code = 0;
  icp->icmp_cksum = 0;
  icp->icmp_seq = seq_num;
  icp->icmp_id = base_id;
  cc = datalen + ICMP_MINLEN;
  icp->icmp_cksum = in_cksum((unsigned short *)icp,cc);
  pthread_mutex_lock(&lock);
  id_map.insert (pair<int,ping_target*>(base_id++,target));
  pthread_mutex_unlock(&lock);

  target->start=Microtime();

  i = sendto(sock, (char *)outpack, cc, 0, (struct sockaddr*)&to, (socklen_t)sizeof(struct sockaddr_in));
  if (i < 0 || i != cc)
  {
	  if (i < 0) perror("sendto error");
  }
  usleep(500);
}
void * pinger::select_icmp(void * instance) {
  pinger * inst=(pinger *)instance;
  int packlen, datalen = DEFDATALEN;
  struct ip *ip;
  u_char *packet;
  string hostname;
  struct icmp *icp;
  int ret, hlen;
  struct timeval tv;
  int retval;
  fd_set rdfd;
  int done=0;
  FD_ZERO(&rdfd);
  FD_SET(inst->sock,&rdfd);
  packlen = datalen + MAXIPLEN + MAXICMPLEN;
  if ( (packet = (u_char *)malloc((u_int)packlen)) == NULL)
  {
	  //this should never happen
  }
  tv.tv_sec = SELECT_TIMEOUT_SEC;
  tv.tv_usec = SELECT_TIMEOUT_USEC;
  while(1)
  {
	  retval = select(inst->sock+1, &rdfd, NULL, NULL, &tv);
	  if (retval == -1)
	  {
		  perror("select()");
	  }
	  else if (retval)
	  {
		  if ( (ret = recv(inst->sock, (char *)packet, packlen, 0) < 0))
		  {
			  perror("recvfrom error");
		  }
		  ip = (struct ip *)((char*)packet); 
		  hlen = sizeof( struct ip ); 
		  //recv returns 0 always - it's unconnected socket
		  icp = (struct icmp *)(packet + hlen); 
		  if (icp->icmp_type == ICMP_ECHOREPLY)
		  {
			  if (icp->icmp_seq != inst->seq_num) continue;
			  pthread_mutex_lock(&(inst->lock));
			  map <int, ping_target*>::iterator it=inst->id_map.find(icp->icmp_id);
			  if (it==inst->id_map.end()) continue;
			  it->second->end=Microtime();
			  cout << it->second->ip <<" responded in " << (it->second->end-it->second->start)/1000. << " ms"<<endl;
			  done++;
			  if (inst->id_map.size()==done && inst->all_sent) break;
			  pthread_mutex_unlock(&(inst->lock));
			
		  }
		  else
		  {
			  continue;
		  }
	  }
	  else
	  {
		  break;
	  }
  }
  free(packet);
}
uint16_t pinger::in_cksum(uint16_t *addr, unsigned len){
  uint16_t answer = 0;
  uint32_t sum = 0;
  while (len > 1)  {
    sum += *addr++;
    len -= 2;
  }
  if (len == 1) {
    *(unsigned char *)&answer = *(unsigned char *)addr ;
    sum += answer;
  }
  sum = (sum >> 16) + (sum & 0xffff); // add high 16 to low 16
  sum += (sum >> 16); // add carry
  answer = ~sum; // truncate to 16 bits
  return answer;
}