#!/usr/bin/perl
($input, $output)=@ARGV;
#$output=join(' ',@ARGV);
$s=time();
open(STATUS,'>'.$output.'.slice.status');
print STATUS "0";
close STATUS;
foreach(`capinfos -c $input`){
	if(/Number of packets:\s+(\d+)/){
		$total=$1;
	}
}
#$packet=1;
#print "$packet/$total";
for($packet=1;$packet<=100;$packet++){
	`editcap -r $input $output/frame_$packet.pcap packet`;
	print "\r$packet/$total";
#    $s=$_;
#    if($s=~/<packet>/){
#        open(FP,'>:gzip',$output.'/farame.'.$packet.'.gz');
#    }
#    print FP $s;
#    if($s=~/<\/packet>/){close FP;$packet++;}
}
#close FP;
open(STATUS,'>'.$output.'.slice.status');
print STATUS "1";
close STATUS;
#$stop=time();
#print $stop-$start;
#print "\n";
$e=time();
$d=$e-$s;
print "\n\nDuration $d sec\n";