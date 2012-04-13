#!/usr/bin/perl
use lib qw(/root/perl5/lib/perl5/x86_64-linux-gnu-thread-multi /root/perl5/lib/perl5/x86_64-linux-gnu-thread-multi /root/perl5/lib/perl5 /etc/perl /usr/local/lib/perl/5.10.1 /usr/local/share/perl/5.10.1 /usr/lib/perl5 /usr/share/perl5 /usr/lib/perl/5.10 /usr/share/perl/5.10 /usr/local/lib/site_perl .);
#die join(' ',@INC);
#push @INC, "/root/perl5/lib/perl5/x86_64-linux-gnu-thread-multi";
use HTML::Entities;
use PerlIO::gzip;
#$start=time();
$output=join(' ',@ARGV);
open(STATUS,'>'.$output.'.pdml.status');
print STATUS "0";
close STATUS;
$packet=1;
while(<STDIN>){
    $s=$_;
    if($s=~/<packet>/){
        open(FP,'>:gzip',$output.'/farame.'.$packet.'.gz');
    }
    print FP $s;
    if($s=~/<\/packet>/){close FP;$packet++;}
}
#close FP;
open(STATUS,'>'.$output.'.pdml.status');
print STATUS "1";
close STATUS;
#$stop=time();
#print $stop-$start;
#print "\n";