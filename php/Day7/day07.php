<?php

//ADVENT OF CODE 2022 - DAY 7

//OPEN FILE HANDLE (READ)
$handle = fopen("input.txt", "r");

//INIT TOTAL
$total = 0;
//INIT FOLDERS
$folders = array();
//CURRENT DIRECTORY
$currentDir = 'root';
$folders = array();
$folders['root'] = 0;

echo '<pre>';
echo '<h1>DOWN&nbsp;root/</h1>';

if ($handle) {
	//READ FILE LINE BY LINE
	while (($line = fgets($handle)) !== false) {

		//IF THE LINE STARTS WITH "$" (COMMAND)
		if(substr($line,0,1) === "$"){

			//PROCESS COMMAND
			switch(substr($line,2,2)){

				//THE COMMAND IS "cd"
				case 'cd':

					//GET THE DIRECTORY TO CHANGE TO
					$changeDir = trim(substr($line,4));

					//IF THIS IS ".."
					if($changeDir === '..'){
						
						//GO UP ONE FOLDER
						$index = strrpos($currentDir,'/');
						$currentDir = substr($currentDir,0,$index);
						echo "<h1>UP&nbsp;&nbsp;&nbsp;$currentDir</h1>";
						
					}else{
						//DON'T CHANGE ROOT ON /
						if($changeDir !== '/'){

							//CHANGE DIR
							$currentDir .= '/' . $changeDir;
							echo "<h1>DOWN&nbsp;$currentDir</h1>";
						}
					}
				break;
				//THE COMMAND IS "ls"
				case 'ls':
					//LIST FILES - IGNORE?
				break;
			}

		//ELSE IF WE ARE STARTING WITH "dir"
		}else if(substr($line,0,3) === "dir"){

			//GET THE DIRECTORY NAME
			$dirName = trim(substr($line,3));
			//PROCESS DIR
			if(!isset($folders[$dirName])){
			
				//PREPARE FOLDER (WILL ADD FILES SOON)
				$folders[$dirName] = array();
			}
		}else{

			//PROCESS FILE
			$size = intval(explode(" ",$line)[0]);
			echo '<h2>Processing File - ' . $line . '</h2>';
			addSizeToFolders($currentDir, $size);
		}
	}
}

echo '</pre>';

//FILTER TO FOLDERS BELOW THE LIMIT
$belowLimit = array_filter($folders, function($el){
	
	$limit = 100000;
	return $el <= $limit;
});

//SUM THE VALUES
$sum = array_sum($belowLimit);
echo "<h1>FINAL SUM: $sum</h1>";
var_dump($folders);


function addSizeToFolders($currentDir, $size){

	global $folders;
	//DEBUG
	echo '<h3>CALLING ADDSIZE ON ' . $currentDir . ' AND SIZE=' . $size . '</h3>';
	
	//SPLIT THE CURRENT DIR ON "/"
	$splitPath = explode("/", $currentDir);

	//GO UP TO THE FINAL PATH
	for($i = 1; $i <= count($splitPath); $i++){

		//GET THE CURRENT FOLDER IN DOT-FORM (e.g. "root.a.b")
		$currentFolder = implode('.',array_slice($splitPath, 0, $i));
		//IF THIS VALUE EXISTS
		if(array_key_exists($currentFolder,$folders)){
			//ADD THE CURRENT SIZE
			$folders[$currentFolder] += $size;
		}else{
			//SET THE CURRENT SIZE
			$folders[$currentFolder] = $size;
		}
		//DEBUG
		echo "ADD TO FOLDER: " . $currentFolder . " - NOW " . $folders[$currentFolder] . "<br>";
	}
}