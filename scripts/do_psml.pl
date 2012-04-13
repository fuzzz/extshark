#!/usr/bin/perl
use HTML::Entities;
#$start=time();
$output=join(' ',@ARGV);
open(STATUS,'>'.$output.'.psml.status');
print STATUS "0";
close STATUS;
open(FP,'>'.$output);$i=0;
print FP '#;';
while(<STDIN>){
    $s=$_;
    if($s=~/<section>(.+)<\/section>/){print FP decode_entities($1).";";next;}
    if($s=~/<\/packet>/ or $s=~/<\/structure>/){print FP "\n";next;}
    if($s=~/<packet>/){print FP "$i;";$i++;}
}
close FP;
open(STATUS,'>'.$output.'.psml.status');
print STATUS "1";
close STATUS;
#$stop=time();
#print $stop-$start;
#print "\n";