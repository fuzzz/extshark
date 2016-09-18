<?php

 error_reporting(E_ALL);
/* Main server class.
   Any operation with console tshark */
class TShark {
    /* This method calls when loading main grid */
    function loadGrid($params){
        $response = array();
        $response['data'] = array();
        $response['success'] = true;
        $start=$params->start;
        $limit=$params->limit;
        $filter=$params->filter;

	$file=$params->file;
	$safe_filter=preg_replace('/[\\"\\\\\\*\\/\\?~]/','',$filter); //Remove special characters
	$pinfo=pathinfo($_SERVER['SCRIPT_FILENAME']);
	if(substr($file,0,1) == '/'){
	    $file=substr($file,strlen($pinfo['dirname'].'/../data/dumps/')); //Cut full path
	}
        $dump=$pinfo['dirname'].'/../data/dumps/'.$file;
        $cache_dir=$pinfo['dirname'].'/../data/tmp/dumps/'.$file;
        $view=$cache_dir.'/view_'.$safe_filter.'.csv';
	if($filter){
	    /* Trick to check syntax of filter */
	    $cmd='tshark -r ../data/s/test.pcap -c 1 -Y "'.$filter.'" 2>&1';
	    exec($cmd,$out,$status);
	    if($status){
		err('Error status '.$status.' returned in cmd:<br>'.$cmd.'<br>Output:<br>'.join("\n",$out));
	    }
	}
	
	/* Start caching
	   This code do CSV file of current 'view' */
        if(!file_exists($view)){
           $dircreate=mkdir($cache_dir,'77777',true);
	    $cmd='(tshark -r '.$dump.' -T psml -2 -R "'.$filter.'" | '.$pinfo['dirname'].'/../scripts/do_psml.pl '.$view.') >/dev/null &';

	    system($cmd);
        }
	
	/* Waiting for cachcing
	   Every seconds check for ending of caching
	   or availability of the required rows */
	$flag=true;
	while($flag){
	    if(file_get_contents($view.'.psml.status')){
	    	$flag=true;break;
	    }
	    clearstatcache();
	    if(filesize($view)>500){
			$fp=fopen($view,'r');
			fseek($fp,-450,SEEK_END);
			fgets($fp);
			$str=fgets($fp);
			fclose($fp);
			$record=preg_split('/;/',$str);
			if($record[0]>($start+$limit)){
				$flag=true;break;
			}
	    }
	    sleep(1);
	}
	
	//Getting data
	clearstatcache();
	$filesize=filesize($view);
	$fp=@fopen($view,'r') or err("Error while openning file $view!");
	$pointer=round($filesize/2);
	$elsize=$pointer;
	$founded=false;$k=0;

	//Binary search
	while(!$founded){
	    $k++;
	    fseek($fp,$pointer);
	    fgets($fp); //Read piece of string to start work with beginning of next
	    $prev_pos=$pos;
	    $pos=ftell($fp);
	    $str=fgets($fp);
	    if(!$str){$founded=$prev_pos;}
	    else{
		$record=preg_split('/;/',$str);
		$current_no=$record[0];
		$elsize=round($elsize/2); //Calc offset
		$fdebug.="$k) $current_no - $start<br>\n"; //Some useless debug
		if($current_no==$start){
		    $founded=$pos; //We found it!
		    break;
		}elseif($current_no>$start){
		    $pointer-=$elsize; //Go back
		}else{
		    $pointer+=$elsize; //Go forward
		}
	    }
	    if($k>200){err("Not found in $k steps. Debug:<br>".$fdebug);} //Exit from loop
	}
	fseek($fp,0);
	$str=fgets($fp);
	$str=rtrim($str);
	$colnames=preg_split('/;/',$str);
	foreach($colnames as $key=>$val){
	    $colnames_[$key]=strtolower(preg_replace('/\./', '', $val)); //Generate id for columns
	}
	$i=$start;fseek($fp,$founded);
	while($i<($start+$limit)){
	    $str=fgets($fp);
	    $str=rtrim($str);
	    $record=preg_split('/;/',$str);$piece=array();
	    foreach($record as $key=>$val){
		if($colnames_[$key] and $colnames_[$key]!='#'){
		    $piece[$colnames_[$key]]=htmlspecialchars($val);
		}
	    }
	    array_push($response['data'],$piece);
	    $i++;
	}
	//Reading last rows to calc total value
	fseek($fp,-450,SEEK_END);
	fgets($fp);
	while($str=fgets($fp)){
	    $record=preg_split('/;/',$str);
	}
        $response['total']=$record[0]+1;
	return $response;
    }




    /* This method calls when outputing packet detail info in Ext.tree.Panel */
    function loadPacket($params){
	$response = array();
        $response['data'] = array();
        $response['success'] = true;
	$no=$params->no;
	$file=$params->file;
	$pinfo=pathinfo($_SERVER['SCRIPT_FILENAME']);
	if(substr($file,0,1) == '/'){
	    $file=substr($file,strlen($pinfo['dirname'].'/../data/dumps/')); //Cut full path
	}
	$dump=$pinfo['dirname'].'/../data/dumps/'.$file;
	//To get packet we use editcap and temporary pcap file
	$cmd="(editcap -r $dump ".$dump."_tmp.pcap $no) >/dev/null;tshark -r ".$dump."_tmp.pcap -T pdml;rm ".$dump."_tmp.pcap";
	exec($cmd,$out,$status);
	$xml=join('',$out);
	$packet=new SimpleXMLElement($xml); //Parse output
	$tree=$this->doTree($packet->packet->proto,false);//Calling recursive function to build tree
	$root=array(
	    'text'=>'Root',
	    'cls'=>'folder',
	    'children'=>$tree
	);
	$response['data']=$root;
	return $response;
    }
    /* Private recursive function that parse SimpleXMLElement Object
        and build tree */
    function doTree($nl,$stop){
	$ret=array();
	foreach($nl as $proto){
	    $itt=array();
	    /* Some doubtful operations with PDML format
	       TODO: Study PDML and rewrite it :) */
	    if($proto->attributes()->name=='geninfo'){continue;}
	    if($proto->attributes()->showname){
		$itt=array('text'=>''.$proto->attributes()->showname);
	    }else{
		if($proto->attributes()->name=='fake-field-wrapper'){
		    $itt=array('text'=>'Data');
		}
		if($proto->attributes()->show=='SEQ/ACK analysis' and $proto->field){
		    $itt=array('text'=>''.$proto->attributes()->show);
		}
		if(!$proto->field){
		    continue;
		}
	    }
	    //Cheacking child nodes
	    if($proto->field){
		$itt['children']=$this->doTree($proto->field,true);
	    }else{
		$itt['leaf']=true;
	    }
	    array_push($ret,$itt);
	}
	return $ret;
    }
}
?>