<?php

//ADVENT OF CODE 2022 - DAY 11, PART 2

//OPEN FILE HANDLE (READ)
$handle = fopen("input.txt", "r");
//$handle = fopen("test.txt", "r");

//INIT THE MONKEYS ARRAY
$monkeys = array();

//INIT ROUND
$round = 0;
//THE NUMBER OF ROUNDS TO PROCESS
$roundLimit = 10000;


if ($handle) {
	//READ FILE LINE BY LINE
	while (($line = fgets($handle)) !== false) {

		//ARE WE PROCESSING A NEW MONKEY?
		if(substr($line,0,6) === 'Monkey'){
			
			//PROCESS NEW MONKEY
			$monkeyId = substr($line,7,1);
			//echo '<br><hr>PROCESSING MONKEY ' . $monkeyId . '<br>';
			$monkeys[$monkeyId] = array();
			$monkeys[$monkeyId]['inspects'] = 0;
		
		}else if(substr($line,2,14) === 'Starting items'){

			//PROCESS STARTING ITEMS
			$items = trim(substr($line, 18));
			$items = explode(", ",$items);
			$monkeys[$monkeyId]['items'] = $items;
		
		}else if(substr($line,2,9) === 'Operation'){

			//PROCESS OPERATION
			$opLine = substr($line, 20);
			$opLine = explode(" ",$opLine);
			//THE OPERAND (+, *, /, -)
			$operand = $opLine[1];
			//THE OPERATION FACTOR (1, 2, old, 7)
			$opFactor = trim($opLine[2]);
			$monkeys[$monkeyId]['opFactor'] = $opFactor;
			$monkeys[$monkeyId]['operand'] = $operand;
		
		}else if(substr($line,2,4) === 'Test'){

			//EXPLODE THE TEST LINE ON SPACES
			$explodeTest = explode(" ",$line);
			//GET THE FINAL ELEMENT (THE DIVISOR)
			$divisor = trim(end($explodeTest));
			//echo 'TEST IS DIVISIBLE BY ' . $divisor . '<br>';
			$monkeys[$monkeyId]['divisor'] = $divisor;
		
		}else if(substr($line,4,7) === 'If true'){

			//EXPLODE AND GET THE LAST
			$explodeTest = explode(" ",$line);
			//GET THE FINAL ELEMENT (THE DIVISOR)
			$trueThrow = trim(end($explodeTest));
			//echo 'IF TRUE, THROW TO ' . $trueThrow . '<br>';
			$monkeys[$monkeyId]['trueThrow'] = $trueThrow;

		}else if(substr($line,4,8) === 'If false'){

			//EXPLODE AND GET THE LAST
			$explodeTest = explode(" ",$line);
			//GET THE FINAL ELEMENT (THE DIVISOR)
			$falseThrow = trim(end($explodeTest));
			//echo 'IF FALSE, THROW TO ' . $falseThrow . '<br>';
			$monkeys[$monkeyId]['falseThrow'] = $falseThrow;
		}
	}
}

//STORE THE COUNT OF MONKEYS
$monkeyCount = $monkeyId + 1;

//RIGHT - THIS IS THE "TRICK" THAT ALLOWS THIS TO WORK
//https://en.wikipedia.org/wiki/Chinese_remainder_theorem
//BASICALLY, AS THESE VALUES SHARED DENOMINATORS (THEY EACH GET MULTIPLIED BY THE SAME NUMBERS)
//THEN THE DIVISIBILITY RULES WILL WORK FOR *ANY* NUMBER WHICH IS RUN THROUGH 
// $item =  $item % $superModulo
//WHERE THE $superModulo IS THE PRODUCT OF ALL THE DIVISORS
//IT'S AN ANNOYING HACK WHICH CAUGHT ME OUT FOR A WHILE!
$superModulo = array_reduce($monkeys, function($prev, $current){

	return $current['divisor'] * $prev;
}, 1);


//WHILE WE ARE STILL PROCESSING 
while ($round < $roundLimit){

	//ITERATE MONKEYS TO PROCESS THEM
	for($monkeyId = 0; $monkeyId < $monkeyCount; $monkeyId++){

		//STORE THE ITEM COUNT
		$itemCount = count($monkeys[$monkeyId]['items']);

		//ITERATE THROUGH THE MONKEY'S ITEMS
		for($itemId = 0; $itemId < $itemCount; $itemId++){

			//GET THE CURRENT ITEM
			$item = $monkeys[$monkeyId]['items'][$itemId];

			//THIS MONKEY IS INSPECTING AN ITEM
			$monkeys[$monkeyId]['inspects'] = intval($monkeys[$monkeyId]['inspects']) + 1;
			
			//GET THE FACTOR (WHICH COULD BE THE "OLD" VALUE)
			$factor = ($monkeys[$monkeyId]['opFactor'] === 'old') ? $item : $monkeys[$monkeyId]['opFactor'];

			//SWITCH ON THE OPERAND
			switch($monkeys[$monkeyId]['operand']){

				case '+':
					//USE GMP FUNCTION TO ADD 2 LARGE NUMBERS
					$newItem = gmp_add($item, $factor);
				break;

				case '*':
					//USE GMP FUNCTION TO MULTIPLY 2 LARGE NUMBERS
					$newItem = gmp_mul($item, $factor);
				break;
			}

			//REDUCE BY SUPER MODULO
			$newItem = $newItem % $superModulo;

			//RUN THE TEST USING THE DIVISOR METHOD
			$result = (gmp_div_r($newItem, $monkeys[$monkeyId]['divisor'], GMP_ROUND_ZERO) == 0);
			
			if($result){

				//GET THE "TRUE" MONKEY
				$trueMonkey = $monkeys[$monkeyId]['trueThrow'];

				//PUSH THE NEW ITEM ONTO THE TRUE MONKEY ITEMS
				array_push($monkeys[$trueMonkey]['items'],$newItem);
				
			}else{

				//GET THE "FALSE" MONKEY
				$falseMonkey = $monkeys[$monkeyId]['falseThrow'];

				//PUSH THE NEW ITEM ONTO THE FALSE MONKEY ITEMS
				array_push($monkeys[$falseMonkey]['items'],$newItem);
			}
		}

		//CLEAR MONKEY'S ITEMS
		$monkeys[$monkeyId]['items'] = array();
	}

	//INCREMENT ROUND
	$round++;
}

$inspects = array();
foreach($monkeys as $id => $monkey){
	$inspects[] = $monkey['inspects'];
}
rsort($inspects);

//OUTPUT
echo '<h1>MULTIPLY TOP 2 = ' . gmp_mul($inspects[0], $inspects[1]) . '</h1>';