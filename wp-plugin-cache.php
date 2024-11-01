<?php
/*
Plugin Name: WP Plugin Cache
Plugin URI: http://www.artiss.co.uk/wp-plugin-cache
Description: File caching utility
Version: 1.2
Author: David Artiss
Author URI: http://www.artiss.co.uk
*/
register_activation_hook(__FILE__,'plugin_cache_install');

// Function to read a cache and, optionally, update it
function plugin_cache_read($file_key,$filein="",$timeout_hour="",$prefix="") {
    define_content_urls;
    if ($prefix!="") {$prefix.="_";}
    $cache_filename=$prefix.sha1($file_key).".cch";
    $cache_dir=WP_CONTENT_DIR."/uploads/plugin_cache/";
    $return['cache_dir']=$cache_dir.$cache_filename;

    // Attempt to find a cached URL. If exists, return it.
    if (file_exists($cache_dir.$cache_filename)===true) {
        $file_return=plugin_get_file(WP_CONTENT_URL."/uploads/plugin_cache/".$cache_filename);
        $return['get_rc']=$file_return['rc'];
        if ($file_return['rc']<1) {
            $return['read_rc']=-1; // Could not read cache
        } else {
            $output=$file_return['file'];
            $timeout=substr($output,0,10);
            if ((time()>$timeout)&&($timeout!=0)) {
                $output="";
                $return['read_rc']=1; // Cache expired
            } else {
                $output=substr($output,10);
                $return['read_rc']=2; // Fetched from cache
            }
        }
    } else {
        $return['read_rc']=3; // no cache
    }

    // Nothing returned - no cache or failure in fetching it
    if ($output=="") {
        if ($filein!="") { 
            $file_return=plugin_get_file($filein);
            $output=$file_return['file'];
        }
        $return['cache_update']="Y";
    }

    // If a timeout has been specified, perform the cache update now
    if (($timeout_hour!="")&&($return['cache_update']=="Y")&&($output!="")&&($filein!="")) {

       // Add timeout to beginning of output file
       if ($timeout_hour!=0) {$timeout=time()+($timeout_hour*3600);} else {$timeout_hour=0;}
       $filein=str_pad($timeout,10," ",STR_PAD_LEFT).$output;

       // Update cache file
       $return['update_rc']=plugin_put_file($cache_dir.$cache_filename,$filein);
    }

    // Return the appropriate data
    $return['data']=$output;
    $return['type']="R";
    return $return;
}

// Function to update a cache
function plugin_cache_update($file_key,$filein,$timeout_hour,$prefix="") {
    define_content_urls;
    if ($prefix!="") {$prefix.="_";}
    $cache_dir=WP_CONTENT_DIR."/uploads/plugin_cache/";
    // Add timeout to beginning of output file
    if ($timeout_hour!=0) {$timeout=time()+($timeout_hour*3600);} else {$timeout_hour=0;}
    $filein=str_pad($timeout,10," ",STR_PAD_LEFT).$filein;
    // Update cache
    $cache_filename=$prefix.sha1($file_key).".cch";
    $return['update_rc']=plugin_put_file($cache_dir.$cache_filename,$filein);
    $return['cache_dir']=$cache_dir.$cache_filename;
    $return['type']="W";
    return $return;
}

// Output return code details if requested for debug purposes
function plugin_cache_debug($data_in) {
    echo "<h3>Cache Debug</h3>\n";
    echo "<p>Cache File: ".$data_in['cache_dir'];
    echo "<br/>\nDirectory Check: ";
    $data_in['dir_check']=plugin_cache_install();
    if ($data_in['dir_check']==-1) {echo "Error: Can't create the cache folder";}
    if ($data_in['dir_check']==-2) {echo "Error: Can't update the cache folder permissions";}
    if ($data_in['dir_check']==11) {echo "Info: The cache folder was created / the permissions were updated";}
    if ($data_in['dir_check']==12) {echo "Info: The cache folder already exists / the permissions were updated";}
    if ($data_in['dir_check']==21) {echo "Info: The cache folder was created / the permissions were fine";}
    if ($data_in['dir_check']==22) {echo "Info: The cache folder already exists / the permissions were fine";}
    if ($data_in['type']=="R") {
        echo "<br/>\nget_file Function: ";
        if ($data_in['get_rc']==1) {echo "Info: File fetched successfully with cURL";}
        if ($data_in['get_rc']==2) {echo "Info: File fetched successfully with file_get_contents";}
        if ($data_in['get_rc']==-1) {echo "Error: cURL active but no file fetched";}
        if ($data_in['get_rc']==-2) {echo "Error: file_get_contents allowed but no file fetched";}
        if ($data_in['get_rc']==-3) {echo "Error: cURL and file_get_contents not active";}
        echo "<br/>\nCache read: ";
        if ($data_in['read_rc']==-1) {echo "Error: The cache file could not be read";}
        if ($data_in['read_rc']==-2) {echo "Error: There was a problem with the cache folder - see Directory Check code";}
        if ($data_in['read_rc']==1) {echo "Info: The cache had expired";}
        if ($data_in['read_rc']==2) {echo "Info: The file was fetched from cache";}
        if ($data_in['read_rc']==3) {echo "Info: No cache exists";}
        echo "<br/>\nPerform a Cache Update: ";
        if ($data_in['cache_update']=="Y") {echo "Yes";} else {echo "No";}
    }
    echo "<br/>\nCache update: ";
    if ($data_in['update_rc']==-1) {echo "Error: The cache file could not be opened";}
    if ($data_in['update_rc']==-2) {echo "Error: The cache file could not be written to";}
    if ($data_in['update_rc']==-3) {echo "Error: There was a problem with the cache folder - see Directory Check code";}
    if ($data_in['update_rc']==1) {echo "Info: The cache file was updated";}
    if ($data_in['update_rc']=="") {echo "Info: An update was not performed";}
    if ($data_in['type']=="R") {echo "<br/>\nSize of returned data: ".strlen($data_in['data'])."<p/>\n";}
    return;
}

// Function to create cache directory and set permissions
function plugin_cache_install() {
    define_content_urls;    
    $cache_dir=WP_CONTENT_DIR."/uploads/plugin_cache/";
    if (!file_exists($cache_dir)) {
        if(!mkdir($cache_dir,0777)) {$rc=-1;} else {$rc=1;}
    } else {
        $rc=2;
    }
    if ($rc!=-1) {
        if (!is_writable($cache_dir)) {
            if (@chmod($cache_dir,octdec("0777"))) {$rc=$rc+10;} else {$rc=-2;}
        } else {
            $rc=$rc+20;
        }
    }
    return $rc;
}

// Function to clear down all cache files and delete cache folder
function plugin_cache_uninstall() {
    define_content_urls;    
    remove_cache_file("*");
    $rc=rmdir(WP_CONTENT_DIR."/uploads/plugin_cache");
    if ($rc===true) {
        echo "<br/>The cache folder was successfully removed.<br/>\n";
    } else {
        echo "<br/>The cache folder could not be removed.<br/>\n";
    }
}

// Function to remove a selected cache file
function remove_cache_file($file) {
    define_content_urls;    
    $file=WP_CONTENT_DIR."/uploads/plugin_cache/".$file.".cch";
    $deleted=0;
    foreach(glob($file) as $delete_file) {
        $rc=unlink($delete_file);
        if ($rc===true) {
            $deleted++;
        } else {
            echo "<br/>File ".$delete_file." could not deleted.<br/>\n";
        }
    }
    echo "<br/>".$deleted." file(s) were deleted.<br/>\n";
}

// Function to get a file using CURL or alternative (1.4)
function plugin_get_file($filein) {
    $fileout="";
    // Try to get with CURL, if installed
    if (in_array('curl',get_loaded_extensions())===true) {
        $cURL = curl_init();
        curl_setopt($cURL,CURLOPT_URL,$filein);
        curl_setopt($cURL,CURLOPT_RETURNTRANSFER,1);
        $fileout=curl_exec($cURL);
        curl_close($cURL);
        if ($fileout=="") {$rc=-1;} else {$rc=1;}
    }
    // If CURL failed and a url_fopen is allowed, use that
    $fopen_status=strtolower(ini_get('allow_url_fopen'));
    if (($fileout=="")&&(($fopen_status===true)or($fopen_status=="yes")or($fopen_status=="on")or($fopen_status=="1"))) {
        $fileout=file_get_contents($filein);
        if ($fileout=="") {$rc=-2;} else {$rc=2;}
    }
    if ((in_array('curl',get_loaded_extensions())!==true)&&(ini_get('allow_url_fopen')==1)) {$rc==-3;}
    $file_return['file']=$fileout;
    $file_return['rc']=$rc;
    return $file_return;
}

// Function to write a file (1.1)
function plugin_put_file($file,$data) {
    $file_open=@fopen($file,'w');
    $rc=1;
    if ($file_open!==false) {
        $file_write=fwrite($file_open,$data);
        if ($file_write!==false) {fclose($file_open);} else {$rc=-2;}
    } else {$rc=-1;}
    return $rc;
}

// Function to define content folder (pre-2.6 compatibility)
function define_content_urls() {
    if (!defined('WP_CONTENT_URL')) {define('WP_CONTENT_URL',get_option('siteurl').'/wp-content');}
    if (!defined('WP_CONTENT_DIR')) {define('WP_CONTENT_DIR',ABSPATH.'wp-content');}
}
?>