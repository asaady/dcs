<?php
namespace tzVendor;
use tzVendor\Common_data;

define("MAX_FLOAT_VALUE",9999999.9999);

class Hungarian 
{

	private $numRows;
	private $numCols;

	private $primes;
	private $stars;
	private $rowsCovered;
	private $colsCovered = array();
	private $costs = array();

	function __construct($theCosts) 
        {
		$this->costs = $theCosts;
		$this->numRows = count($this->costs);
		$this->numCols = count($this->costs[0]);

		$this->primes = array();
		$this->stars = array();

		// Инициализация массивов с покрытием строк/столбцов
		$this->rowsCovered = array();
		$this->colsCovered = array();
                
                
		for ($i = 0; $i < $this->numRows; $i++) 
                {
                    $this->rowsCovered[$i] = false;
		}
		for ($j = 0; $j < $this->numCols; $j++) 
                {
                    $this->colsCovered[$j] = false;
		}
		// Инициализация матриц
		for ($i = 0; $i < $this->numRows; $i++) 
                {
                    $this->primes[$i] = array();
                    $this->stars[$i] = array();
                    for ($j = 0; $j < $this->numCols; $j++) 
                    {
                        $this->primes[$i][$j] = false;
                        $this->stars[$i][$j] = false;
                    }
		}
	}
	// looks at the colsCovered array, and returns true if all entries are true, false otherwise
	private function allColsCovered() 
        {
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                if (!$this->colsCovered[$j]) 
                {
                        return false;
                }
            }
            return true;
	}
        
	private function subtractRowColMins() 
        {
            for ($i = 0; $i < $this->numRows; $i++) 
            { //for each row
                $rowMin = MAX_FLOAT_VALUE;
                for ($j = 0; $j < $this->numCols; $j++) 
                { // grab the smallest element in that row
                    if ($this->costs[$i][$j] < $rowMin) 
                    {
                        $rowMin = $this->costs[$i][$j];
                    }
                }
                for ($j = 0; $j < $this->numCols; $j++) 
                { // subtract that from each element
                        $this->costs[$i][$j] -= $rowMin;
                }
            }
            for ($j = 0; $j < $this->numCols; $j++) 
            { // for each col
                $colMin = MAX_FLOAT_VALUE;
                for ($i = 0; $i < $this->numRows; $i++) 
                { // grab the smallest element in that column
                    if ($this->costs[$i][$j] < $colMin) 
                    {
                            $colMin = $this->costs[$i][$j];
                    }
                }
                for ($i = 0; $i < $this->numRows; $i++) { // subtract that from each element
                        $this->costs[$i][$j] -= $colMin;
                }
            }
	}
	/*
	 * get the first zero in each column, star it if there isn't already a star in that row
	 * cover the row and column of the star made, and continue to the next column
	 * O(n^2)
	 */
	public function findStars() 
        {
            $rowStars = array();
            $colStars = array();

            for ($i = 0; $i < $this->numRows; $i++) 
            {
                $rowStars[$i] = false;
            }
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                $colStars[$j] = false;
            }

            for ($j = 0; $j < $this->numCols; $j++) 
            {
                for ($i = 0; $i < $this->numRows; $i++) 
                {
                    if (($this->costs[$i][$j] == 0) && (!$rowStars[$i]) && (!$colStars[$j]))
                    {
                            $this->stars[$i][$j] = true;
                            $rowStars[$i] = true;
                            $colStars[$j] = true;
                            break;
                    }
                }
            }
	}

	/*
	 * resets covered information, O(n)
	 */
	public function resetCovered() 
        {
            for ($i = 0; $i < $this->numRows; $i++) 
            {
                $this->rowsCovered[$i] = false;
            }
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                $this->colsCovered[$j] = false;
            }
	}

	/*
	 * sets the columns covered if they contain starred zeros
	 * O(n^2)
	 */
	private function coverStarredZeroCols() 
        {
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                $this->colsCovered[$j] = false;
                for ($i = 0; $i < $this->numRows; $i++) 
                {
                    if ($this->stars[$i][$j]) 
                    {
                        $this->colsCovered[$j] = true;
                        break; // break inner loop to save a bit of time
                    }
                }
            }
	}

	/*
	 * Finds an uncovered zero, primes it, and returns an array
	 * describing the row and column of the newly primed zero.
	 * If no uncovered zero could be found, returns -1 in the indices.
	 * O(n^2)
	 */
	private function primeUncoveredZero() 
        {
            $location = array();

            for ($i = 0; $i < $this->numRows; $i++) 
            {
                if (!$this->rowsCovered[$i]) 
                {
                    for ($j = 0; $j < $this->numCols; $j++) 
                    {
                        if (!$this->colsCovered[$j]) 
                        {
                            if ($this->costs[$i][$j] == 0) 
                            {
                                $this->primes[$i][$j] = true;
                                $location[0] = $i;
                                $location[1] = $j;
                                return $location;
                            }
                        }
                    }
                }
            }

            $location[0] = -1;
            $location[1] = -1;
            return $location;
	}
        
	/*
	 * Finds the minimum uncovered value, and adds it to all the covered rows then
	 * subtracts it from all the uncovered columns. This results in a cost matrix with
	 * at least one more zero.
	 */
	private function minUncoveredRowsCols() 
        {
            // find min uncovered value
            $minUncovered = MAX_FLOAT_VALUE;
            for ($i = 0; $i < $this->numRows; $i++) 
            {
                if (!$this->rowsCovered[$i]) 
                {
                    for ($j = 0; $j < $this->numCols; $j++) 
                    {
                        if (!$this->colsCovered[$j]) 
                        {
                            if ($this->costs[$i][$j] < $minUncovered) 
                            {
                                $minUncovered = $this->costs[$i][$j];
                            }
                        }
                    }
                }
            }

            // add that value to all the COVERED rows.
            for ($i = 0; $i < $this->numRows; $i++) 
            {
                if ($this->rowsCovered[$i]) 
                {
                    for ($j = 0; $j < $this->numCols; $j++) 
                    {
                        $this->costs[$i][$j] += $minUncovered;
                    }
                }
            }

            // subtract that value from all the UNcovered columns
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                if (!$this->colsCovered[$j]) 
                {
                    for ($i = 0; $i < $this->numRows; $i++) 
                    {
                        $this->costs[$i][$j] -= $minUncovered;
                    }
                }
            }
	}

	/*
	 * Given a column index, finds a row with a star. returns -1 if this isn't possible.
	 */
	public function findStarRowInCol($theCol) 
        {
            for ($i = 0; $i < $this->numRows; $i++) 
            {
                if ($this->stars[$i][$theCol]) 
                {
                    return $i;
                }
            }
            return -1;
	}


	public function findStarColInRow($theRow) 
        {
            if ($theRow >= $this->numRows)
            {
                return -1;
            }    
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                if ($this->stars[$theRow][$j]) 
                {
                    return $j;
                }
            }
            return -1;
	}

	/*
	 * Given a row index, finds a column with a prime. returns -1 if this isn't possible.
	 */
	private function findPrimeColInRow($theRow) 
        {
            for ($j = 0; $j < $this->numCols; $j++) 
            {
                if ($this->primes[$theRow][$j]) 
                {
                    return $j;
                }
            }
            return -1;
	}



	/*
	 * Given an arraylist of  locations, star them
	 */
	private function starLocations($locations) 
        {
            for ($k = 0; $k < count($locations); $k++) 
            {
                $location = $locations[$k];
                $row = $location[0];
                $col = $location[1];
                if (($row < $this->numRows)&&($col < $this->numCols))
                {    
                    $this->stars[$row][$col] = true;
                }    
                else
                {
                    Common_data::_log('/log','starLocations error row = '.$row.' col = '.$col);
                    break;
                }    
            }
	}

	/*
	 * Given an arraylist of starred locations, unstar them
	 */
	private function unStarLocations($starLocations) 
        {
            for ($k = 0; $k < count($starLocations); $k++) 
            {
                $starLocation = $starLocations[$k];
                $row = $starLocation[0];
                $col = $starLocation[1];
                if (($row < $this->numRows)&&($col < $this->numCols))
                {    
                    $this->stars[$row][$col] = false;
                }
                else
                {
                    Common_data::_log('/log','unStarLocations error row = '.$row.' col = '.$col);
                    break;
                }    
            }
	}


	/*
	 * Starting at a given primed location[0=row,1=col], we find an augmenting path
	 * consisting of a primed , starred , primed , ..., primed. (note that it begins and ends with a prime)
	 * We do this by starting at the location, going to a starred zero in the same column, then going to a primed zero in
	 * the same row, etc, until we get to a prime with no star in the column.
	 * O(n^2)
	 */
	private function augmentPathStartingAtPrime($location) 
        {
            // Make the arraylists sufficiently large to begin with
            $primeLocations = array();
            $starLocations = array();
            $primeLocations[] = $location;
            $currentRow = $location[0];
            $currentCol = $location[1];
            while (true) 
            { // add stars and primes in pairs
                $starRow = $this->findStarRowInCol($currentCol);
                // at some point we won't be able to find a star. if this is the case, break.
                if ($starRow == -1) 
                {
                    break;
                }
                $starLocation = array($starRow, $currentCol);
                $starLocations[] = $starLocation;
                $currentRow = $starRow;

                $primeCol = $this->findPrimeColInRow($currentRow);
                if ($primeCol == -1) 
                {
                    break;
                }
                $primeLocation = array($currentRow, $primeCol);
                $primeLocations[] = $primeLocation;
                $currentCol = $primeCol;
            }

            $this->unStarLocations($starLocations);
            $this->starLocations($primeLocations);
	}
	/*
	 * resets prime information
	 */
	public function resetPrimes() 
        {
            for ($i = 0; $i < $this->numRows; $i++) 
            {
                for ($j = 0; $j < $this->numCols; $j++) 
                {
                    $this->primes[$i][$j] = false;
                }
            }
	}

	/*
	 * the starred 0's in each column are the assignments.
	 * O(n^2)
	 */
	public function starsToAssignments() 
        {
		$toRet = array();
		for ($j = 0; $j < $this->numCols; $j++) 
                {
                    $toRet[$j] = array($this->findStarRowInCol($j), $j); // O(n)
		}
		return $toRet;
	}

        
	public function execute() 
        {
                Common_data::_log('/log','START num_row = '.$this->numRows.' num_col  = '.$this->numCols);
                $start = microtime(TRUE);
		$this->subtractRowColMins();

		$this->findStars(); // O(n^2)
		$this->resetCovered(); // O(n);
		$this->coverStarredZeroCols(); // O(n^2)

		while (!$this->allColsCovered()) {
                    $primedLocation = $this->primeUncoveredZero(); // O(n^2)
                    // It's possible that we couldn't find a zero to prime, so we have to induce some zeros so we can find one to prime
                    if ($primedLocation[0] == -1) 
                    {
                        $this->minUncoveredRowsCols(); // O(n^2)
                        $primedLocation = $this->primeUncoveredZero(); // O(n^2)
                    }
                    // is there a starred 0 in the primed zeros row?
                    $primedRow = $primedLocation[0];
                    $starCol = $this->findStarColInRow($primedRow);
                    if ($starCol != -1) 
                    {
                        // cover ther row of the primedLocation and uncover the star column
                        $this->rowsCovered[$primedRow] = true;
                        $this->colsCovered[$starCol] = false;
                    } 
                    else 
                    { // otherwise we need to find an augmenting path and start over.
                        $this->augmentPathStartingAtPrime($primedLocation);
                        $this->resetCovered();
                        $this->resetPrimes();
                        $this->coverStarredZeroCols();
                    }
		}

                Common_data::_log('/log',' execute time :'.(microtime(TRUE) - $start).' sec');
		return $this->starsToAssignments(); // O(n^2)

	}

}
