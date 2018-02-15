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
  $new_url = implode(",", $array);

  $html = file_get_html($new_url);

  $dates = array();
  foreach($html->find('time-ago') as $element) {
    $dtime = $element->datetime;
    $dates[] = date_create_from_format('Y-m-d\TH:i:sZ', $s);
  };
  $max_date = max($dates);

  $formatted_date = date('Y-m-d H:i:s', $max);

  return $formatted_date;
}


function nbconvert_function($atts) {
  //process plugin
  extract(shortcode_atts(array(
        'url' => "",
     ), $atts));

  $clean_url = preg_replace('#^https?://#', '', rtrim($url,'/'));
  $html = file_get_contents("https://nbviewer.jupyter.org/url/" . $clean_url);
  $nb_output = getHTMLByID('notebook-container', $html);

  try {
    $last_update_date_time = get_most_recent_git_change_for_file($url);
  } catch (Exception $e) {
    $last_update_date_time = 'didnt work';
}

  
  //send back text to calling function
  return '<div class="nbconvert-notebook">
            <label><a href="'. $url . '" target="_blank">Check it out on github, last updated:' . $last_update_date_time . '</a></label>' . $nb_output . '</div>';
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
