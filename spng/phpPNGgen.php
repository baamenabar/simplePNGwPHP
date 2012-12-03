<?php 
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 B. Agustín Amenabar L <baamenabar@gmail.com> @ImINaBAR
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     B. Agustín Amenabar L <baamenabar@gmail.com> @ImINaBAR
 * @copyright  2012 B. Agustín Amenabar L.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0
 * @link       http://medula.cl/
 */

$shorthand = array(
		'w' =>'width',
		'h' =>'height',
		'b' =>'backgroundColor',
		'oc'=>'overwriteCached',
		'b64' => 'base64'
		);

$defaults = array(
		'width'=>1,
		'height'=>1,
		'backgroundColor'=>'0xFFFFFF',//RGB+Alpha in the following format: #rrggbbaa//could not use hashtag notation due to it being written in the URL string
		'overwriteCached'=>false
		);
$configs = array(
		'cacheFolder' => '',
		'extension' => 'png',
		'mime' => 'image/png',
		'quality'=>95
	);

$options=array();

if ( isset($_GET['o']) ) {
	$options = $_GET;
	$parts = explode( '_', $_GET['o'] ); 
	$options['backgroundColor'] = $parts[0];
	if( count( $parts ) > 1 ){//if we have more than just colour
		$sizeA = explode( 'x', strtolower($parts[1]) );
		$options['width'] = $sizeA[0];
		if( count( $sizeA ) > 1 )$options['height'] = $sizeA[1];
	}
}

foreach ( $shorthand as $key => $value ){
	if( !isset( $options[$value] ) && isset( $options[$key] ) ){
		$options[$value] = $options[$key];
	}
}

//build cache name and check if already on cache
$cachedFile=array('c');

foreach ($shorthand as $key => $value){
	if (isset($options[$value]) && $key!='oc' && $key!='o' && $key!='b64') {
		$cachedFile[]=$key.$options[$value];
	}
}

//separate hex colour into array of RGB[a]
$background=str_split(substr($options['backgroundColor'], 2),2);
if(count($background) < 4)$background[]='FF';

//$cachedFile is the fileneme with wich the image will be stored on disk.
$cachedFile = $configs['cacheFolder'].implode( '_' , $cachedFile ) . '.' . $configs['extension'];
// if $cachedFile is already on disk and we are not asking for a new one, just read the file and deliver it.
if(is_file($cachedFile) && (!isset($options['overwriteCached']) || !$options['overwriteCached'])){
	deliver($cachedFile);
	exit();// < --------------------------------------------------------------------------------------------------------- HERE BE AN EXIT 
}

/*****************************************************************************************
					THE ACTUAL PROCESSING
******************************************************************************************/
$newImage = imagecreatetruecolor( $options['width'] , $options['height'] );
$backgroundColor = imagecolorallocatealpha($newImage, hexdec($background[0]), hexdec($background[1]), hexdec($background[2]), (((~((int)hexdec($background[3]))) & 0xff) >> 1));//The fifth parameter of imagecolorallocatealpha is a 7bit integer // $alpha7 = ((~((int)$alpha8)) & 0xff) >> 1;// http://php.net/manual/es/function.imagecolorallocatealpha.php
imagefill($newImage, 0, 0, $backgroundColor);
imagesavealpha($newImage, TRUE); 
imagepng( $newImage , $cachedFile , floor($configs['quality']/10) );
imagedestroy( $newImage );
//*/
/*
//this is for debugging the .htaccess rewrite

echo "<pre>";
print_r($_GET);
print_r($options);
echo "</pre>";

//*/
$_GET = $options;
deliver($cachedFile);

function deliver($file){
	if ( isset($_GET['base64']) ) {
		echo base64_encode_image($file);
		exit();// < --------------------------------------------------------------------------------------------------------- HERE BE AN EXIT 
	}
	outputImage($file);
}

function outputImage($theURL,$extension='png'){
	switch( $extension )
	{
		case 'jpg':
			header( "Content-type: image/jpeg");
			break;
		case 'gif':
			header( "Content-type: image/gif");
			break;
		case 'png':
			header( "Content-type: image/png");
			break;
	}

	@readfile($theURL);
}

function base64_encode_image ($imagefile) {
	$imgtype = array('jpg', 'gif', 'png');
	$filename = file_exists($imagefile) ? htmlentities($imagefile) : die('Image file name does not exist');
	$filetype = pathinfo($filename, PATHINFO_EXTENSION);
	if (in_array($filetype, $imgtype)){
		$imgbinary = fread(fopen($filename, "r"), filesize($filename));
	} else {
		die ('Invalid image type, jpg, gif, and png is only allowed');
	}
	return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
}

/*
“Stolen” from Andreas A. who posted on http://cl1.php.net/manual/en/function.imagecreatetruecolor.php#97920 
If you are Andreas A. and want better attribution contact me or if an MIT licence for your code doesn't cut it for you I'll remove this, and roll my own.

$gradient=@imagecreatetruecolor(
    ($_GET["orientation"]=="vertical"? 1:(int)$_GET["pixel"]), 
    ($_GET["orientation"]=="horizontal"? 1:(int)$_GET["pixel"]))
  or $gradient=@imagecreate(
    ($_GET["orientation"]=="vertical"? 1:$_GET["pixel"]), 
    ($_GET["orientation"]=="horizontal"? 1:$_GET["pixel"]))
  or exit("error");
for($xy=0; $xy<(int)$_GET["pixel"]; $xy++)
  imageline($gradient,
    ($_GET["orientation"]=="vertical"?0:$xy),
    ($_GET["orientation"]=="horizontal"?0:$xy),
    ($_GET["orientation"]=="vertical"?0:$xy),
    ($_GET["orientation"]=="horizontal"?0:$xy),
      imagecolorallocate($gradient,
        round(
          hexdec(substr($_GET["color1"], 0, 2))-
          (
            hexdec(substr($_GET["color1"], 0, 2))
            -hexdec(substr($_GET["color2"], 0, 2)))
          /($_GET["pixel"]-1)*$xy),
        round(
          hexdec(substr($_GET["color1"], 2, 2))-
          (
            hexdec(substr($_GET["color1"], 2, 2))
            -hexdec(substr($_GET["color2"], 2, 2)))
          /($_GET["pixel"]-1)*$xy),
        round(
          hexdec(substr($_GET["color1"], 4, 2))-
          (
            hexdec(substr($_GET["color1"], 4, 2))-
            hexdec(substr($_GET["color2"], 4, 2)))
          /($_GET["pixel"]-1)*$xy)));
@header("Content-type: image/png");
imagepng($gradient);
imagedestroy($gradient);
*/

 ?>