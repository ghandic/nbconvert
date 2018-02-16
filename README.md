# nbconvert

## Installation

Simply install [WP Pusher](https://wppusher.com/) by downloading and installing the package from a zip file then add my wordpress plugin from my git repo! You do this by adding the uri into the git plugin installer (ghandic/nbconvert)

## How it works

Simply add a shortcode and a url to the notebook file into your page editor on wordpress and voila

`[nbconvert url="https://github.com/ghandic/confluenceapi/blob/master/examples/Updating%20a%20confluence%20page.ipynb"]`

PHP then sends the url to an nbviewer API that will convert the ipynb file to html

## Example of how it renders on Wordpress default theme

![](https://www.andrewchallis.co.uk/wp-content/uploads/2018/02/demo.png)
