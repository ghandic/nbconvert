<?php
   /*
   Plugin Name: NbConvert
   Description: A plugin to add ipynb files to a blog post or page using nbviewer
   Version: 1.0
   Author: Andrew Challis
   Author URI: http://www.andrewchallis.com
   License: MIT
   */

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
   
  //Parse the HTML. The @ is used to suppress any parsing errors
  //that will be thrown if the $html string isn't valid XHTML.
  libxml_use_internal_errors(true);
  $dom->loadHTML($html);
   
  //Get all links. You could also use any other tag name here,
  //like 'img' or 'table', to extract other tags.
  $time_agos = $dom->getElementsByTagName('time-ago');

  /*$datetimes = array();
  //Iterate over the extracted links and display their URLs
  foreach ($time_agos as $time_ago) {
      //Extract and show the "href" attribute. 
    $datetime = $time_ago->getAttribute('datetime');
    $datetimes[] = date_create_from_format('Y-m-d\TH:i:sZ', $datetime);
  }
  */

  $mostRecent= 0;
  foreach($time_agos as $time_ago){
    $datetime = $time_ago->getAttribute('datetime');
    $curDate = strtotime($datetime);
    if ($curDate > $mostRecent) {
       $mostRecent = $curDate;
    }
  }


  print_r($mostRecent);

  $max_date = date('Y-m-d H:i:s', $mostRecent);
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

  
  //send back text to calling function
  return '<div class="nbconvert-notebook">
            <label><a href="'. $url . '" target="_blank">Check it out on github, <time-ago>last updated: ' . $last_update_date_time . '</time-ago</a></label>' . $nb_output . '</div>';
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
