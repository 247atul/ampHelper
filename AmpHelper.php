<?php 

/**
* @author atul chaurasia
*/
class AmpHelper
{
	public static $correspondingAmpHtmlTags = [
								"<img" => "<amp-img ",
								"</img>" => "</amp-img>",
								"<iframe" => "<amp-iframe width='925' height='725' layout='responsive' sandbox='allow-scripts allow-popups' allowfullscreen frameborder='0' srcdoc='<iframe",								"</iframe>" => "<amp-img layout='fill' src='https://i.vimeocdn.com/video/536538454_640.webp' placeholder></amp-img></amp-iframe>",
								"</iframe>"=>"</iframe>'><amp-img layout='fill' src='https://collegedunia.com/public/asset/img/logo-bigger.png' placeholder></amp-img></amp-iframe>",
								"<video" => "<amp-video",
								"</video>" => "</amp-video>",
								"<font" => "<span",
								"</font>" => "</span>",	
								"<space" => "<span",
								"</space>" => "</span>"	,
								"data-src" => "src",	
								'src="null"'=>"",						
								'src=""'=>"",
								'style'=>"data-style"							
								];
	public static $htmlTagsToTraverseData = [
									'<img'=>"<img",

								];
	public static $htmlTagsToRemove = [
									'<svg (.*?)<\/svg>'=>"",
									'<form id="leadform_native"(.*?)<\/form>'=>"",
									'<form (.*?)<\/form>'=>"",
									'<style (.*?)<\/style>'=>"",

								];


	public static $htmlTagAttributeToRemove = ['style',
												'cropbottom',
												'croptop',
												'mkey',
												'cropleft',
												'cropright',
												'border',
												'align',
												'nowrap',
												'clear',
												'color',
												'contenteditable',
												'onclick'];

	public static function replaceHtmlTagToCorresAmpTags($data)
	{
		// invalid attribute for amp page is removed
		foreach (self::$htmlTagAttributeToRemove as $key => $value) 
		{			
			$data = preg_replace('/(<[^>]+) '.$value.'=".*?"/i', '$1', $data);
			$data = preg_replace('/(<[^>]+) '.$value."='.*?'/i", '$1', $data);
		}

        foreach(self::$htmlTagsToRemove as $key => $value) 
		{
        	$data = preg_replace('#'.$key.'#is'," ", $data);			
        }
        	
        self::traverseHtmlDataForTag($data);

		foreach(self::$correspondingAmpHtmlTags as $key => $value) 
		{
			$data = str_replace($key, $value, $data);
		}

        // to remove lead from content
        // preg_match("<form (.*?)<\/form>", $data, $match);
        
		return $data;
	}

	public static function traverseHtmlDataForTag(&$data)
	{
		$combinedData = "";
		foreach (self::$htmlTagsToTraverseData as $tagkey => $tag) 
		{
			$needle = $tagkey;
			$lastPos = 0;
			$positions = array();

			while (($lastPos = strpos($data, $needle, $lastPos))!== false) {
			    $positions[] = $lastPos;
			    $lastPos = $lastPos + strlen($needle);
			}
			$positions[] = strlen($data);
			$dataSliceArray = [];
			if ($tagkey=="<img") {
				$startPos = 0;
				foreach ($positions as $positionskey => $positionsvalue) 
				{
					$dataSliceArray[$positionskey] = substr($data, $startPos,$positionsvalue-$startPos);
					$startPos = $positionsvalue;
					if (strpos($dataSliceArray[$positionskey], $tagkey)===0) {
						$endPosOfImgTag = strpos($dataSliceArray[$positionskey], ">");
						$fullImgTag = substr($dataSliceArray[$positionskey], 0,$endPosOfImgTag+1);
						preg_match('/height=\"([^"]*)\"/i', $fullImgTag,$height);
						preg_match('/width=\"([^"]*)\"/i', $fullImgTag,$width);
						// '#'.$key.'#is'
						if (isset($height[0]) && isset($width[0])) 
						{
							if (isset($width[0]) && $width[1]<280 ) {
								$fullImgTag = str_replace($width[0], $width[0].' layout="fixed"', $fullImgTag);								
							}
							else
							{
								$fullImgTag = str_replace($width[0], $width[0].' layout="responsive"', $fullImgTag);									
							}
						
						}
						else if (isset($height[0])) 
						{
							$fullImgTag = str_replace($height[0], $height[0].' layout = "fixed-height" width = "auto"', $fullImgTag);															
						}
						else if (isset($width[0])) 
						{
							$fullImgTag = str_replace($width[0], $width[0].' layout = "responsive" height="100" ', $fullImgTag);															
						}
						elseif(!isset($height[0]) && !isset($width[0]))
						{

							$fullImgTag = str_replace("<img", '<img layout="responsive" width="100" height="100" ', $fullImgTag);															
						}
						$dataSliceArray[$positionskey] = $fullImgTag.substr($dataSliceArray[$positionskey], $endPosOfImgTag+1);
					}
				}
			}
			foreach ($dataSliceArray as $key => $value) {
				$combinedData .=$value;
			}
		}
		if ($combinedData !="") 
		{
			$data = $combinedData;
		}
	}

}

?>
