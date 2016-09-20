<?php
/* Various methods will be here */
class Util {
    function loadDumpDir(){
	$response = array();
        $response['data'] = array();
        $response['success'] = true;
	$pinfo=pathinfo($_SERVER['SCRIPT_FILENAME']);
	
	$dir=$pinfo['dirname'].'/../data/dumps';

	$tree=$this->getFilesFromDir($dir);
	$root=array(
	    'text'=>'Root',
	    'cls'=>'folder',
	    'children'=>$tree
	);
	$response['data']=$root;
	return $response;
    }
    function getFilesFromDir($dir) {
	$ret=array(); 
	if ($handle = opendir($dir)) { 
	    while (false !== ($file = readdir($handle))) { 
		if ($file != "." && $file != "..") {
		    $itt=array('text'=>$file);
		    if(is_dir($dir.'/'.$file)) { 
			$dir2 = $dir.'/'.$file; 
			$itt['children']=$this->getFilesFromDir($dir2); 
		    } 
		    else { 
			$itt['leaf']=true;
			$itt['id']=$dir.'/'.$file;
		    }
		    array_push($ret,$itt);
		}
	    } 
	    closedir($handle);
	} 
	return $ret; 
    } 
}
