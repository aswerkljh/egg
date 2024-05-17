<?php //v1.0.0
	// Specify the directory containing the images and videos
	$dir = '.';
	
	// Create thumbnail directory if it doesn't exist
	$thumbDir = $dir . '/.thumb';
	if (!file_exists($thumbDir)) {
		mkdir($thumbDir, 0755);
	}
	
	// Get all the folders in the directory
	$folders = array_diff(scandir($dir), array('..', '.', '.thumb'));
	sort($folders);
	
	// Get all the image and video files in the directory
	$imagesPng = preg_grep('~\.(png)$~', scandir($dir));
	$imagesJpg = preg_grep('~\.(jpg)$~', scandir($dir));
	$imagesGif = preg_grep('~\.(gif)$~', scandir($dir));
	$videosMp4 = preg_grep('~\.(mp4)$~', scandir($dir));

	$imagesBeforeReverse = array_merge($imagesPng, $imagesJpg, $imagesGif);
	$images = array_reverse($imagesBeforeReverse); // reverse array so newer images are displayed first
	$videos = $videosMp4;

	// Create thumbnails for each image and video that doesn't have an existing thumbnail
	foreach ($images as $img) {
		if (!file_exists($thumbDir . '/' . $img)) {
			$thumbImg = imagecreatefromstring(file_get_contents($dir . '/' . $img));
			$width = imagesx($thumbImg);
			$height = imagesy($thumbImg);
			$newHeight = 200;
			$newWidth = intval($newHeight * $width / $height);
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			imagecopyresampled($thumb, $thumbImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagedestroy($thumbImg);
			imagejpeg($thumb, $thumbDir . '/' . $img, 99);
			imagedestroy($thumb);
		}
	}

	// Generate MP4 thumbnail for video using ffmpeg
	function generateVideoMp4Thumbnail($videoPath, $thumbnailPath, $maxHeight = 200)
	{
		$ffmpegPath = 'ffmpeg'; // ffmpeg binary must be in system PATH
		//$cmd = "$ffmpegPath -i \"$videoPath\" -vf \"scale=-1:$maxHeight,setsar=1/1\" -c:v libx264 -pix_fmt  yuv420p -movflags +faststart \"$thumbnailPath\"";
		$cmd = "$ffmpegPath -i \"$videoPath\" -vf \"scale=-1:$maxHeight,setsar=1/1\" -c:v libx264 -crf 30 -preset veryslow -pix_fmt yuv420p -movflags faststart \"$thumbnailPath\"";

		exec($cmd);
	}

	foreach ($videos as $video) {
		if (!file_exists($thumbDir . '/' . pathinfo($video, PATHINFO_FILENAME) . '.mp4')) {
			$videoPath = $dir . '/' . $video;
			$thumbnailPath = $thumbDir . '/' . pathinfo($video, PATHINFO_FILENAME) . '.mp4';
			generateVideoMp4Thumbnail($videoPath, $thumbnailPath, 200);
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=0.5">
		<title>drkt.eu Photography</title>
		<style>
			body {background-color:hsl(0, 0%, 11%);min-width:900px;}
			#folder-wrapper a, p {color:#f2f2f2;font-size: 30pt;text-align: center;text-decoration:none;font-family: monospace;letter-spacing: 0px;}
			#folder-wrapper {margin-top:30px;height:63px;}
			.folder {margin:4px;background-color:hsl(0, 0%, 27%);float:left;padding: 4px 11px 4px 11px;}
			.folder:hover {background-color:hsl(0, 0%, 18%);}
			#separator {margin:4px;background-color:hsl(0, 0%, 11%);float:left;padding: 4px 0px 4px 0px;}
			#image-wrapper {margin:auto;}
			.wrapper-array-items {margin-top:25px;float:left;}
			.thumb-item {margin: 4.5px 4px 0px 4px;height:200px;}

		</style>
	</head>
	<body>
		<div id="folder-wrapper">
			<?php
				// Check if ".rootdir" file exists
				$is_root_dir = file_exists($dir . '/.rootdir');
				if ($is_root_dir) {
					// If is root dir, print RSS and about instead of Return
					echo '<a href="about.html"><p class="folder">About</p></a>';
					echo '<a href="viewcounts.php"><p class="folder">Viewcounts</p></a>';
					echo '<a href="feed.rss"><p class="folder">RSS feed</p></a>';
				}
				else {
					// Print return button
					echo '<a href=".."><p style="margin-left:5px;" class="folder">< Return</p></a>';
				}

				// Check if there are any folders
				$has_folders = false;
				foreach($folders as $folder) {
					if(is_dir($dir.'/'.$folder)) {
						$has_folders = true;
						break;
					}
				}

				// Decide if to output subfolder separator or not
				if($has_folders) {
					echo '<p style="margin-left:5px;" id="separator">|</p>';
				}

				// Output HTML for each folder
				foreach($folders as $folder) {
					if(is_dir($dir.'/'.$folder)) {
						echo '<a class="folder" href="'.$folder.'">'.$folder.'/</a>';
					}
				}
			?>
		</div>
		<div id="image-wrapper">
			<div class="wrapper-array-items">
				<?php
					// If root dir, print manually curated images (NOT VIDEOS).
					if ($is_root_dir) { 
						$fpImages = array(
							"https://p.drkt.eu/2024/auroras.jpg",
							"https://p.drkt.eu/2024/ghosts.jpg",
							"https://p.drkt.eu/2024/_MG_5180.jpg",
							"https://p.drkt.eu/2023/_MG_4779.jpg",
							"https://p.drkt.eu/2023/_MG_4359.jpg",
							"https://p.drkt.eu/2023/_MG_4305.jpg",
							"https://p.drkt.eu/2023/_MG_4464.jpg",
							"https://p.drkt.eu/2023/IMG_2954.jpg",
							"https://p.drkt.eu/2023/Horsens-from-Borre-Knob.jpg",
							"https://p.drkt.eu/2022/IMG_1991.jpg",
							"https://p.drkt.eu/2022/IMG_9880.jpg",
							"https://p.drkt.eu/2022/Ree_Park_19_July/IMG_1167.jpg",
							"https://p.drkt.eu/2022/Givskud_Zoo_April_26/IMG_9407.jpg",
							"https://p.drkt.eu/2022/Givskud_Zoo_April_26/IMG_9162.jpg",
							"https://p.drkt.eu/2022/Givskud_Zoo_April_26/IMG_9640.jpg",
							"https://p.drkt.eu/2022/Givskud_Zoo_April_26/IMG_9624.jpg",
							"https://p.drkt.eu/2021/IMG_4170.jpg",
							"https://p.drkt.eu/2021/Silkeborg_September_4/IMG_5822.jpg",
							"https://p.drkt.eu/2021/Randers_Regnskov_November_4/IMG_7388.jpg",
							"https://p.drkt.eu/2021/Randers_Regnskov_November_4/IMG_7187.jpg",
							"https://p.drkt.eu/2021/Randers_Regnskov_November_4/IMG_7359.jpg",
							"https://p.drkt.eu/2021/Hirtshals_June_19/IMG_4396.jpg",
							"https://p.drkt.eu/2021/Givskud_September_3/IMG_5041.jpg",
							"https://p.drkt.eu/2020/IMG_2479.jpg",
							"https://p.drkt.eu/2020/IMG_2813.jpg",
							"https://p.drkt.eu/2020/IMG_2317.jpg",
							"https://p.drkt.eu/2020/Sukkertoppen_May_3/IMG_2038.jpg",
							"https://p.drkt.eu/2019/June_21/IMG_1387.jpg",
							"https://p.drkt.eu/2018/rovfugle_silkeborg/IMG_0127.jpg",
							"https://p.drkt.eu/2017/IMG_2598.jpg",
							"https://p.drkt.eu/2016/14372293_2108291979396890_7326883603774323269_o.jpg",
							"https://p.drkt.eu/2016/14188640_2101374800088608_5453883739002624296_o.jpg",
							"https://p.drkt.eu/2024/Abandoned_Building/_MG_5056.jpg",
							"https://p.drkt.eu/2024/Abandoned_Building/_MG_5067.jpg",
							"https://p.drkt.eu/2024/Abandoned_Building/_MG_5053.jpg",
							"https://p.drkt.eu/2021/IMG_3430.jpg",
							"https://p.drkt.eu/2021/Hirtshals_June_19/IMG_4242.jpg",
							"https://p.drkt.eu/2021/Hirtshals_June_19/IMG_3772.jpg"
						);

						// Shuffle the image order for each refresh
						shuffle($fpImages);

						// Output HTML for each image in array, with thumbnails for img src
						foreach ($fpImages as $image) {
							$pathParts = pathinfo($image);
							$directory = $pathParts['dirname'];
							$filename = $pathParts['basename'];
							$thumbnailUrl = $directory . "/.thumb/" . $filename;
						
							echo "<a href='$image'><img src='$thumbnailUrl' class=\"thumb-item\"></a>";
						}
					}

					// If not root dir, loop through and print thumbnails
					else {
						// Output HTML for each thumbnail, videos first images last
						foreach ($videos as $video) {
							$thumbPath = './.thumb/' . pathinfo($video, PATHINFO_FILENAME) . '.mp4';
							$videoPath = './' . $video;
							echo '<a href="' . $videoPath . '"><video src="' . $thumbPath . '" class="thumb-item"autoplay loop muted playsinline style="outline: none;"></video></a>';
						}

						foreach($images as $link){
							$thumbPath = './.thumb/' . $link;
							$imgPath = './' . $link;
							if(file_exists($thumbPath)) {
								$srcPath = $thumbPath;
							} else {
								$srcPath = $imgPath;
							}
							echo "<a href=\"$imgPath\"><img src=\"$srcPath\" class=\"thumb-item\"></a>";
						}
					}
				?>
			</div>       
		</div>
	</body>
</html>