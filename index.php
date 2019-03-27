<?php

class Maze
{

	private $map;
	private $entry;
	private $destination;
	private $path;
	private $step_count = 0;
	private $directionText;

	public function __construct($map, $entry, $destination)
	{
		$this->map = $map;
		$this->entry = $entry;
		$this->destination = $destination;
	}

	private function determineWinning($coordinate)
	{
		if ($this->destination === $coordinate) {
			return true;
		} else {
			return false;
		}
	}

	private function determineAvailableCoordinate($coordinate)
	{
		if (isset($this->map[$coordinate[0]][$coordinate[1]])
			&& !$this->map[$coordinate[0]][$coordinate[1]]) {
			return true;
		} else {
			return false;
		}
	}

	private function getAvailableDirections($coordinate, $last_step_coordinate = [])
	{
		if ($this->determineAvailableCoordinate([$coordinate[0]+1,$coordinate[1]])) {
			$available_coordinates[] = [$coordinate[0]+1, $coordinate[1]];
		}
		if ($this->determineAvailableCoordinate([$coordinate[0],$coordinate[1]+1])) {
			$available_coordinates[] = [$coordinate[0], $coordinate[1]+1];
		}
		if ($this->determineAvailableCoordinate([$coordinate[0]-1,$coordinate[1]])) {
			$available_coordinates[] = [$coordinate[0]-1, $coordinate[1]];
		}
		if ($this->determineAvailableCoordinate([$coordinate[0],$coordinate[1]-1])) {
			$available_coordinates[] = [$coordinate[0], $coordinate[1]-1];
		}

		if (empty($available_coordinates)) {
			return false;
		} else {
			foreach ($available_coordinates as $key => $coordinate) {
				if ($coordinate === $last_step_coordinate) {
					unset($available_coordinates[$key]);
				}
			}

			return $available_coordinates;
		}

	}

	private function traceBack($last_node_coordinate)
	{
		foreach ($this->path as $key => $coordinate) {
			if ($coordinate === $last_node_coordinate) {
				$offset = $key;
			}
		}

		if (empty($offset)) {
			return false;
		} else {
			$this->path = array_slice($this->path, 0, $offset+1);
			$this->step_count = count($this->path);
			return true;
		}
	}

	public function printStepCount()
	{
		echo "It takes {$this->step_count} steps.";
	}

	public function printPath()
	{

		foreach ($this->path as $coordinate) {
			$path_for_printing[$coordinate[0]][$coordinate[1]] = 1;
		}

		echo '<div style="display:block;">';
		foreach ($this->map as $y => $map_x) {
			foreach ($map_x as $x => $value) {				
				if (isset($path_for_printing[$y][$x])) {
					echo '<div style="width:20px;height:20px;background-color:red;display:inline-block;border-radius:20px;"></div>';
				} elseif ($value == 0) {
					echo '<div style="width:20px;height:20px;background-color:white;display:inline-block;"></div>';
				} else {
					echo '<div style="width:20px;height:20px;background-color:black;display:inline-block;"></div>';
				}
			}
			echo '<br/>';
		}
		echo '</div>';
	}

	public function walk($current_coordinate = [], $last_step_coordinate = [])
	{

		if (empty($current_coordinate)) {
			$current_coordinate = $this->entry;
		}

		$this->path[] = $current_coordinate;
		$available_coordinates = $this->getAvailableDirections($current_coordinate, $last_step_coordinate);

		if ($this->determineWinning($current_coordinate)) {
			$this->step_count++;
			return true;
		} elseif (empty($available_coordinates)) {
			return false;
		} else {
			$if_node = count($available_coordinates) > 1 ? true : false;
			
			if ($if_node) {
				foreach ($available_coordinates as $coordinate) {
					$result = $this->walk($coordinate, $current_coordinate);
					if ($result) {
						return true;
					} else {
						$this->traceBack($current_coordinate);
					}
				}
			} else {
				$this->step_count++;
				$coordinate = current($available_coordinates);
				$result = $this->walk($coordinate, $current_coordinate);
				return $result;
			}
		}
	}

}

class DigitalPuzzle
{
	private $scale;
	private $originPuzzle;
	private $currentPuzzle;
	private $whiteBlock;
	private $path;
	private $steps;
	private $stepCount = 0;
	private $directionText;
	private $stepQueue;

	public function __construct($puzzle = [])
	{
		if (empty($puzzle)) {
			$this->originPuzzle = $this->initPuzzle();
		} else {

			$y_count = count($puzzle);
			foreach ($puzzle as $x_axis) {
				if ($y_count != count($x_axis)) {
					return false;
					break;
				}
			}

			$this->scale = $y_count;
			$this->originPuzzle = $puzzle;
		}

		$this->currentPuzzle = $this->originPuzzle;
		$this->whiteBlock = $this->locateWhiteBlock($this->currentPuzzle);

		$path = $this->puzzleToPlain($this->currentPuzzle);
		$this->path[] = $path;
	}

	public function initPuzzle($scale = 3)
	{
		$puzzle = [];

		for ($i=0; $i<$scale*$scale; $i++) { 
			$digits[] = $i;
		}

		shuffle($digits);

		for ($i=0; $i<$scale; $i++) {
			$x_axis = [];
			for ($j=0; $j<$scale; $j++) {
				$x_axis[] = array_pop($digits);
			}
			$puzzle[] = $x_axis;
		}

		return $puzzle;
	}

	public function printPuzzle($puzzle = []) {

		if (empty($puzzle)) {
			$puzzle = $this->originPuzzle;
		}

		echo '<div style="display:inline-block;border:1px #000 solid;">';
		foreach ($puzzle as $y_axis) {
			foreach ($y_axis as $digit) {
				if ($digit === 0) {
					echo '<div style="display:inline-block;background-color:#FFF;width:50px;height:50px;border:1px #000 solid;line-height:50px;text-align:center;font-size:30px;font-weight:bold;">';
				echo "#";
				} else {
					echo '<div style="display:inline-block;background-color:#CCC;width:50px;height:50px;border:1px #000 solid;line-height:50px;text-align:center;font-size:30px;font-weight:bold;">';
				echo $digit;
				}
				echo '</div>';
			}
			echo '<br/>';
		}
		echo '</div>';
		echo '<br/>';
		echo '<br/>';
	}

	private function puzzleToPlain($puzzle)
	{
		if (empty($puzzle)) {
			return false;
		}
		foreach ($puzzle as $y => $y_axis) {
			foreach ($y_axis as $x => $digit) {
				$path[] = $digit;
			}
		}
		return $path;
	}

	private function locateWhiteBlock($puzzle)
	{
		foreach ($puzzle as $y => $y_axis) {
			foreach ($y_axis as $x => $digit) {
				if ($digit === 0) {
					return [$y, $x];
					break;
				}
			}
		}
	}

	private function checkPath($puzzle)
	{
		$plainForCheck = $this->puzzleToPlain($puzzle);

		return in_array($puzzle, $plainForCheck) ? false : true;
	}

	private function move($puzzle, $block, $direction)
	{
		switch ($direction) {
			case 1:
				$temp = $puzzle[$block[0]-1][$block[1]];
				$puzzle[$block[0]-1][$block[1]] = $puzzle[$block[0]][$block[1]];
				$puzzle[$block[0]][$block[1]] = $temp;
				break;
			case 2:
				$temp = $puzzle[$block[0]][$block[1]+1];
				$puzzle[$block[0]][$block[1]+1] = $puzzle[$block[0]][$block[1]];
				$puzzle[$block[0]][$block[1]] = $temp;
				break;
			case 3:
				$temp = $puzzle[$block[0]+1][$block[1]];
				$puzzle[$block[0]+1][$block[1]] = $puzzle[$block[0]][$block[1]];
				$puzzle[$block[0]][$block[1]] = $temp;
				break;
			case 4:
				$temp = $puzzle[$block[0]][$block[1]-1];
				$puzzle[$block[0]][$block[1]-1] = $puzzle[$block[0]][$block[1]];
				$puzzle[$block[0]][$block[1]] = $temp;
				break;
			
			default:
				return false;
				break;
		}

		return $puzzle;
	}

	public function getAvailableDirections($puzzle)
	{
		$whiteBlock = $this->locateWhiteBlock($puzzle);
		$tempPuzzle = $puzzle;
		$directions = [];

		if ($whiteBlock[0] > 0) {
			$tempPuzzle = $this->move($tempPuzzle, $whiteBlock, 1);
			if ($this->checkPath($tempPuzzle)) {
				$directions[] = 1;
			}
		}
		if ($whiteBlock[1] < 2) {
			$tempPuzzle = $this->move($tempPuzzle, $whiteBlock, 2);
			if ($this->checkPath($tempPuzzle)) {
				$directions[] = 2;
			}
		}
		if ($whiteBlock[0] < 2) {
			$tempPuzzle = $this->move($tempPuzzle, $whiteBlock, 3);
			if ($this->checkPath($tempPuzzle)) {
				$directions[] = 3;
			}
		}
		if ($whiteBlock[1] > 0) {
			$tempPuzzle = $this->move($tempPuzzle, $whiteBlock, 4);
			if ($this->checkPath($tempPuzzle)) {
				$directions[] = 4;
			}
		}

		return $directions;
	}

	private function determineWinning($puzzle)
	{
		$plain = $this->puzzleToPlain($puzzle);
		$lastDigit = 0;
		foreach ($plain as $key => $digit) {
			if ($key != array_key_last($plain) && $digit != $lastDigit+1) {
				return false;
			}
			$lastDigit = $digit;
		}
		return true;
	}

	private function traceTo($puzzle)
	{
		$plainForTrace = $this->puzzleToPlain($puzzle);
		foreach ($this->path as $key => $plain) {
			if ($plain === $plainForTrace) {
				$this->path = array_slice($this->path, 0, $key+1);
				$this->steps = array_slice($this->steps, 0, $key);
				$this->stepCount = count($this->path);
				return true;
			}
		}
		return false;
	}

	public function setDirectionText($directionText) {
		$this->directionText = $directionText;
	}

	public function printSteps() {
		if (empty($this->directionText)) {
			return false;
		}
		foreach ($this->steps as $direction) {
			$direction = preg_replace(['/1/', '/2/', '/3/', '/4/'], $this->directionText, $direction);
			echo $direction;
			echo ' ';
		}
	}

	public function walk($type = 'bfs')
	{
		switch ($type) {
			case 'bfs':
				return $this->BfsWalk();
				break;

			case 'dfs':
				return $this->DfsWalk();
				break;
			
			default:
				return $this->BfsWalk();
				break;
		}
	}

	public function DfdWalk()
	{

		if ($this->stepCount > 5) {
			return false;
		}

		$currentPuzzle = $this->currentPuzzle;
		$whiteBlock = $this->locateWhiteBlock($currentPuzzle);

		$availableDirections = $this->getAvailableDirections($currentPuzzle);

		if ($this->determineWinning($currentPuzzle)) {
			return true;
		} elseif (empty($availableDirections)) {
			return false;
		} else {
			$ifNode = $availableDirections > 1 ? true : false;

			if ($ifNode) {
				foreach ($availableDirections as $direction) {
					$this->currentPuzzle = $this->move($currentPuzzle, $whiteBlock, $direction);
					$this->whiteBlock = $this->locateWhiteBlock($currentPuzzle);
					$this->path[] = $this->puzzleToPlain($this->currentPuzzle);
					$this->steps[] = $direction;
					$this->stepCount++;
					$result = $this->walk();
					if ($result) {
						return true;
					} else {
						$this->traceTo($currentPuzzle);
					}
				}
			} else {
				$direction = current($availableDirections);
				$this->currentPuzzle = $this->move($this->currentPuzzle, $whiteBlock, $direction);
				$this->whiteBlock = $this->locateWhiteBlock($this->currentPuzzle);
				$this->path[] = $this->puzzleToPlain($this->currentPuzzle);
				$this->steps[] = $direction;
				$this->stepCount++;
				return $this->walk();
			}
		}

	}

	private function traceSteps()
	{
		$step = array_shift($this->fullStepQueue);
		$lastId = $step['last_id'];
		$this->steps[] = $step['direction'];

		while ($step = array_shift($this->fullStepQueue)) {
			if ($step['id'] == $lastId) {
				$this->steps[] = $step['direction'];
				$lastId = $step['last_id'];
			}
		}

		$this->steps = array_reverse($this->steps);
	}

	private function stepFingerprint($stepId, $lastStepId, $direction)
	{
		$step = [
			'id' => $stepId,
			'last_id' => $lastStepId,
			'direction' => $direction
		];
		return $step;
	}

	public function BfsWalk()
	{

		$searchQueue = [];
		$stepQueue = [];
		$this->fullStepQueue = [];
		$stepId = 0;

		$availableDirections = $this->getAvailableDirections($this->currentPuzzle);

		foreach ($availableDirections as $direction) {
			$puzzle = $this->move($this->currentPuzzle, $this->whiteBlock, $direction);
			$stepId++;
			$step = $this->stepFingerprint($stepId, 0, $direction);
			$this->path[] = $this->puzzleToPlain($puzzle);
			array_unshift($searchQueue, $puzzle);
			array_unshift($stepQueue, $step);
			array_unshift($this->fullStepQueue, $step);
			if ($winning = $this->determineWinning($puzzle)) {
				$this->traceSteps();
				return true;
			}
		}

		while (!empty($searchQueue)) {
			$currentPuzzle = array_pop($searchQueue);
			$currentStep = array_pop($stepQueue);
			$whiteBlock = $this->locateWhiteBlock($currentPuzzle);
			$availableDirections = $this->getAvailableDirections($currentPuzzle);

			foreach ($availableDirections as $direction) {
				$puzzle = $this->move($currentPuzzle, $whiteBlock, $direction);
				$stepId++;
				$step = $this->stepFingerprint($stepId, $currentStep['id'], $direction);
				$this->path[] = $this->puzzleToPlain($puzzle);
				array_unshift($searchQueue, $puzzle);
				array_unshift($stepQueue, $step);
				array_unshift($this->fullStepQueue, $step);
				if ($winning = $this->determineWinning($puzzle)) {
					$this->traceSteps();
					return true;
				}
			}
		}
	}
}

// way=0, wall=1
$map = [
	[1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
	[1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,1],
	[1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,1,0,1],
	[1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,0,1],
	[1,0,1,0,1,0,1,0,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,1],
	[1,0,1,0,1,0,1,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,1],
	[1,0,1,0,1,0,1,1,0,1,0,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,1,1,1,0,1],
	[1,0,1,0,1,0,1,0,0,1,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,0,0,1,0,1,0,1,0,0,0,0,1],
	[1,0,1,0,1,0,1,0,1,1,1,0,1,0,0,0,1,0,1,0,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1],
	[1,0,1,0,1,0,1,0,0,0,1,0,1,0,1,1,1,0,1,0,1,0,0,0,0,0,0,1,0,1,0,1,0,1,0,1,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,0,0,1,0,0,0,1,0,1,0,1,0,1,1,1,1,0,1,0,1,0,1,0,1,0,1,1,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,0,1,0,1,1,0,1,0,1,0,1,0,1,0,1,0,1,0,0,1],
	[1,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,1,0,0,0,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1],
	[1,0,1,1,1,0,1,0,1,1,1,0,1,1,1,1,1,0,1,0,1,1,1,1,1,1,0,1,0,1,0,1,0,1,0,1,0,0,1],
	[1,0,1,0,0,0,1,0,1,0,0,0,1,0,0,0,1,0,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,1,0,1,1,0,1],
	[1,0,1,0,1,1,1,0,1,0,1,1,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,0,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,1,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
	[1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
	[1,0,1,0,0,0,1,0,1,0,0,0,1,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
	[1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
	[1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
	[1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1],
	[1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
	[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
];

$puzzle = [
	[4,0,7],
	[1,8,6],
	[5,3,2]
];

$directionText = [
	1 => '上',
	2 => '右',
	3 => '下',
	4 => '左'
];
// 26 38
$digitalPuzzle = new Maze($map, [0, 1], [26, 38]);
//$digitalPuzzle->setDirectionText($directionText);
//$digitalPuzzle->printPuzzle();
$digitalPuzzle->walk();
$digitalPuzzle->printPath();
echo "<br/>";
$digitalPuzzle->printStepCount();