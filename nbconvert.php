<?php
   /*
   Plugin Name: NbConvert
   Description: A plugin to add ipynb files to a blog post or page using nbviewer
   Version: 1.0
   Author: Andrew Challis
   Author URI: http://www.andrewchallis.com
   License: MIT
   */


function sprintf_array($string, $array)
{
    $keys    = array_keys($array);
    $keysmap = array_flip($keys);
    $values  = array_values($array);
   
    while (preg_match('/%\(([a-zA-Z0-9_ -]+)\)/', $string, $m))
    {   
        if (!isset($keysmap[$m[1]]))
        {
            echo "No key $m[1]\n";
            return false;
        }
       
        $string = str_replace($m[0], '%' . ($keysmap[$m[1]] + 1) . '$', $string);
    }
   
    array_unshift($values, $string);
    var_dump($values);
    return call_user_func_array('sprintf', $values);
}


function get_last_update_time($url) {

  $url_list = explode('/', $url);

  $info = array('repo' => $url_list[4], 
                'owner' => $url_list[3], 
                'branch' => $url_list[6], 
                'path' => implode("/", array_slice($url_list, 7))
              );

  $request_url = sprintf_array('https://api.github.com/repos/%(owner)/%(repo)/commits/%(branch)?path=%(path)&page=1', $info);

  //Initialize cURL.
  $ch = curl_init();
   
  //Set the URL that you want to GET by using the CURLOPT_URL option.
  curl_setopt($ch, CURLOPT_URL, $request_url);

  //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   
  //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  //Execute the request.
  $data = curl_exec($ch);
   
  //Close the cURL handle.
  curl_close($ch);

  print_r($data)
}



function add_newstyle_stylesheet() {

    wp_register_style(
        'nbconvert',
        dirname(__FILE__) . '/css/nbconvert.css'
    );
    wp_enqueue_style( 'nbconvert' );
}
add_action( 'wp_enqueue_scripts', 'add_newstyle_stylesheet' );


//tell wordpress to register the nbconvert shortcode

add_shortcode("nbconvert", "nbconvert_handler");

function nbconvert_handler($atts) {
  //run function that actually does the work of the plugin
  $nb_output = nbconvert_function($atts);
  //send back text to replace shortcode in post
  return $nb_output;
}


function get_most_recent_git_change_for_file($url) {
  


  $url_list = explode('/', $url);
  $url_list[5] = 'blame';
  $new_url = implode("/", $url_list);
  //Load the HTML page
  $html = file_get_contents($new_url);
  
  //Create a new DOM document
  $dom = new DOMDocument;
  libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  
  // Get all time-ago tags
  $time_agos = $dom->getElementsByTagName('time-ago');

  $mostRecent= 0;
  foreach($time_agos as $time_ago){
    $datetime = $time_ago->getAttribute('datetime');
    $curDate = strtotime($datetime);
    if ($curDate > $mostRecent) {
       $mostRecent = $curDate;
    }
  }

  $max_date = date('d/m/Y H:i:s', $mostRecent);
  return $max_date;
  
}


function nbconvert_function($atts) {
  //process plugin
  extract(shortcode_atts(array(
        'url' => "",
     ), $atts));

  $clean_url = preg_replace('#^https?://#', '', rtrim($url,'/'));
  $html = file_get_contents("https://nbviewer.jupyter.org/url/" . $clean_url);
  $nb_output = getHTMLByID('notebook-container', $html);

  $last_update_date_time = get_most_recent_git_change_for_file($url);

  
  get_last_update_time($url);

  //send back text to calling function
  return '<div class="nbconvert-notebook">
            <label><a href="'. $url . '" target="_blank">Check it out on github </a> <time-ago>last updated: ' . $last_update_date_time . '</time-ago></label>' . $nb_output . '</div>';
}

function innerHTML(DOMNode $elm) {
  $innerHTML = '';
  $children  = $elm->childNodes;

  foreach($children as $child) {
    $innerHTML .= $elm->ownerDocument->saveHTML($child);
  }

  return $innerHTML;
}

function getHTMLByID($id, $html) {
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $node = $dom->getElementById($id);
    if ($node) {
        $inner_output = innerHTML($node);
        return $inner_output;
    }
    return FALSE;
}
?>
