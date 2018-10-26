<!-- 

The MIT License (MIT)

Copyright (c) 2018 Cloud Andrade

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

-->

<!-- 

*INSTRUCTIONS*

SET THIS SCRIPT INTO YOUR PROJECT ROOT AND RUN IT ON THE BROWSER.

*CONFIGUTATIONS*

TO DISABLE BACKUP FOLDER SET THE _CREATEBACKUP CONSTANT TO FALSE
DON'T BE LAZY AND GO FIGURE OUT HOW THE CONSTANTS WORKS.

~datCloud <3

-->


<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>Image Optimizer 0.8a</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  <script>
  	let logArea;
  </script>
  <style>
  	.log-area{
  		border-radius: 5px;
  		padding: 15px;
	    height: 350px;
	    overflow-x: hidden;
	    overflow-y: scroll;
	    background-color: #e9ecef;
  	}
  </style>
	</head>
	<body>
		<div class="container">
			<div class="jumbotron">
				<h1>Image Optimizer 1.4.3</h1>
				<div class="row">
					<div class="col-sm-6">
						<h2>Features:</h2>
						<ul>
							<li>Get all files(images) located inside /imagens directory</li>
							<li>Resizes to 800x800 max size</li>
							<li>Preserves original dimension if less than 800x800</li>
							<li>Optimizes file size without losing quality</li>
							<li>Works with .jpg, .jpeg and .png</li>
							<li>Preserves PNG transparency</li>
							<li>and stuff...</li>
						</ul>
					</div>
					<div class="col-sm-6">
						<img src="https://pbs.twimg.com/media/CugT5PGXEAASAyl.jpg" class="img-fluid rounded" alt="Celso Portiolli comendo Salsicha" title="Celso Portiolli comendo Salsicha">
					</div>
				</div>
				<hr>
				<p><strong>Total size: </strong><span id="totalSize">Calculating...</span></p>
				<p><strong>Optimized size: </strong><span id="optimizedSize">0.0MB</span></p>
				<p><strong>You saved <span id="savedSize">0.0MB</span></strong></p>
				<br>
				<div class="progress" style="height: 50px; background: white;">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
				</div>
				<br>
				<div class="log-area" id="log-area">

		
		<?php

		ini_set('display_errors', 'On');
    	error_reporting(E_ALL);
    	ini_set('max_execution_time', 0);
    	ini_set('max_file_uploads', 1000);
    	ini_set('upload_max_size', '900M');
    	ini_set('post_max_size', '900M');
    	ini_set("gd.jpeg_ignore_warning", 1);

    	define("_maxWidth", 800);
    	define("_maxHeight", 800);
    	define("_fileSizePrecision", 2);
    	define("_createBackup", false);
    	define("_feedbackDelay", 0.1);

    	function FlushMessage($message){
    		echo $message."<br><script>logArea = document.querySelector('#log-area');
					logArea.scrollTop = logArea.scrollHeight - logArea.clientHeight;</script>";
    		ob_flush();
			flush();
			usleep(_feedbackDelay * 1000000);
    	}

    	function CheckFiles($dir){

    		$rdi = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    		$myCounter = 0;
    		$filesAndFolders;
			foreach(new RecursiveIteratorIterator($rdi) as $file) {
				if(strpos($file, '\slider') || strpos($file, '\bkp')) continue;
		        FlushMessage(++$myCounter." images found");
		        FlushMessage($file);
		        $finalName = substr($file, strrpos($file, '\\') + 1);
		        FlushMessage("File name - ".$finalName);
		        $filePath = explode($finalName, $file);
		        FlushMessage("File directory - ".$filePath[0]);
		        $fileSize=str_replace(",", "", number_format($file->getSize() / 1048576, _fileSizePrecision));
		        $filesAndFolders[$myCounter - 1][0] = $filePath[0];
		        $filesAndFolders[$myCounter - 1][1] = $finalName;
		        $filesAndFolders[$myCounter - 1][2] = $fileSize;
		        FlushMessage("File size - ".$fileSize." MB");
		        if(!isset($totalSize)) $totalSize = 0;
		        $totalSize+=$fileSize;
		        echo "<script>$('#totalSize').html('".$totalSize."MB')</script>";
		    }

		    echo "
		    <script>$('#optimizedSize').html('Calculating...')</script>
		    ";
			FlushMessage($myCounter." images found in total");
    		FlushMessage("Starting image optimization...");
			OptimizeFiles(1, $myCounter, $filesAndFolders, $totalSize);
		}

		function OptimizeFiles($currentCounter, $fileCounter, $filesAndFolders, $totalSize){

			// Get variables from function call to directly optimize images w/o be necessary to especify or scan file and folder
			foreach ($filesAndFolders as $fileAndFolder) {

				if(!is_dir($fileAndFolder[0]."bkp") && _createBackup){
					mkdir($fileAndFolder[0]."bkp");
				}
				if(!file_exists($fileAndFolder[0]."bkp/".$fileAndFolder[1]) && _createBackup) copy($fileAndFolder[0].$fileAndFolder[1], $fileAndFolder[0]."bkp/".$fileAndFolder[1]);

				$w = getimagesize($fileAndFolder[0].$fileAndFolder[1])[0];
				$h = getimagesize($fileAndFolder[0].$fileAndFolder[1])[1];

				if($w < _maxWidth && $h < _maxHeight){
					$newWidth = $w;
					$newHeight = $h;
				}
				else{

					$newWidth = _maxWidth;
					$newHeight = _maxHeight;

					$final_height = $h * ($newWidth / $w);
	                if ($final_height > $newHeight) {
	                    $newWidth = $w * ($newHeight / $h);
	                } else {
	                    $newHeight = $final_height;
	                }
	            }

				if(getimagesize($fileAndFolder[0].$fileAndFolder[1])[2] == 2){
					$originalImage = imagecreatefromjpeg($fileAndFolder[0].$fileAndFolder[1]);
					$newImage = imagecreatetruecolor($newWidth, $newHeight);
				}
				else if(getimagesize($fileAndFolder[0].$fileAndFolder[1])[2] == 3){
					$originalImage = imagecreatefrompng($fileAndFolder[0].$fileAndFolder[1]);
					$newImage = imagecreate($newWidth, $newHeight);
					// imagealphablending($newImage, false);
					// imagesavealpha($newImage, true);
					// $alpha = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
					// imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $alpha);
				}

				imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, getimagesize($fileAndFolder[0].$fileAndFolder[1])[0], getimagesize($fileAndFolder[0].$fileAndFolder[1])[1]);
				if(getimagesize($fileAndFolder[0].$fileAndFolder[1])[2] == 2){
					imagejpeg($newImage, $fileAndFolder[0].$fileAndFolder[1]);
				}
				else if(getimagesize($fileAndFolder[0].$fileAndFolder[1])[2] == 3){
					imagepng($newImage, $fileAndFolder[0].$fileAndFolder[1]);
				}

				imagedestroy($originalImage);
    			imagedestroy($newImage);

    			clearstatcache();
    			$optimizedSize = str_replace(",", "", number_format(fileSize($fileAndFolder[0].$fileAndFolder[1]) / 1048576, _fileSizePrecision));
    			FlushMessage("Original File Size/Optimized File Size - ".$fileAndFolder[2]."/".$optimizedSize);
    			if(!isset($optimizedTotalSize)) $optimizedTotalSize = 0;
				$optimizedTotalSize += $optimizedSize;
				$totalSize -= $optimizedSize;
				FlushMessage("$currentCounter/$fileCounter optimized images");
    			echo "
    			<script>
		    		$('#optimizedSize').html('".$optimizedTotalSize."MB');
    				$('#savedSize').html('".round($totalSize, _fileSizePrecision)."MB');
	    			$('.progress-bar').css('width', '".(($currentCounter/$fileCounter)*100)."%');
	    			$('.progress-bar').attr('aria-valuenow', '".floor(($currentCounter/$fileCounter)*100)."');
	    			$('.progress-bar').html('".floor(($currentCounter/$fileCounter)*100)."%');
    			</script>
    			";
    			$currentCounter++;

			}

			FlushMessage("Finished!");
			$percentResult = round($totalSize / ($optimizedTotalSize + $totalSize), _fileSizePrecision) * 100;
    		echo "
    		<div class=\"modal fade\" id=\"myModal\">
				<div class=\"modal-dialog modal-dialog-centered\">
					<div class=\"modal-content\">

						<div class=\"modal-header\">
							<h4 class=\"modal-title\">All images has been optimized</h4>
							<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
						</div>

						<div class=\"modal-body\">
							You saved ".round($totalSize, 3)."MB (".$percentResult."% less than the original total size)
				        </div>

				        <div class=\"modal-footer\">
							<button type=\"button\" class=\"btn btn-success\" data-dismiss=\"modal\">Ok</button>
				        </div>

					</div>
				</div>
			</div>
			<script>$('#myModal').modal('show');</script>
    		";

			echo "
    			<script>
	    			$('.progress-bar').addClass('bg-success');
	    			$('.progress-bar').removeClass('progress-bar-striped progress-bar-animated');
    			</script>
    			";

			//unlink(__FILE__);
			
		}
			FlushMessage("Searching for images...");
			CheckFiles("imagens/");
		?>
		</div>
		</div>
			</div>

	</body>
</html>
