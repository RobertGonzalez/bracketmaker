<?php
/**
 * Class for handling bracket production
 */
class Bracket {
    /**
     * The number of contestant slots for this bracket
     * 
     * @var int
     */
    public $contestants = 0;
    
    /**
     * The number of entries for this bracket. This is the actual number of names
     * in the bracket, not the number of slots, which would be $contestants.
     * 
     * @var int
     */
    public $entrants = 0;
    
    /**
     * The total number of matches for this bracket
     * 
     * @var int
     */
    public $matches = 0;
    
    /**
     * The total number of rounds for this bracket
     * 
     * @var int
     */
    public $rounds = 0;
    
    /**
     * List of cells that are seed cells. This will always be first column cells
     * 
     * @var array
     */
    public $seedCells = array();
    
    /**
     * Lists of cells that will contain contestant slots (not to be confused with
     * entrants)
     * 
     * @var array
     */
    public $contestantCells = array();
    
    /**
     * Listing of cells that are connector cells, connecting one contestant cell
     * to another contentant cell
     * 
     * @var array
     */
    public $connectorCells = array();
    
    /**
     * The number of rows for the HTML table for this bracket
     * 
     * @var int
     */
    public $rows = 0;
    
    /**
     * The number of columns for the HTML table for this bracket
     * 
     * @var int
     */
    public $cols = 0;
    
    /**
     * Object constructor... takes the number of entries and sets up the whole 
     * shebang. 
     * 
     * @param int $entrants
     */
    public function __construct($entrants = 8) {
        if (!empty($entrants) && is_numeric($entrants)) {
            // This is our entrant count
            $this->entrants = intval($entrants);
            
            // This gets our base number of matchups to determine our rounds
            $l = log($this->entrants, 2);
            $this->rounds = ceil($l);
            
            // Contestants is 2 to the $rounds power... 
            // makes it compatible with evenly matched pairings
            $this->contestants = pow(2, $this->rounds);
            
            // The total number of matches for this bracket
            $this->matches = $this->contestants - 1;
            
            // The actual bracket table structure
            $this->cols = $this->rounds + 1;
            $this->rows = ($this->contestants * 2) - 1;
            
            // Set our seed cells
            $this->_setSeedCells();
            
            // Set our contestant cells
            $this->_setContestantCells();
            
            // Set our connector cells
            $this->_setConnectorCells();
        }
    }
    
    public function isContestantCell($row, $col) {
        // Algorithm for this is simple:
        // row + 2^($col-1) / 2^$col = whole number
        //return ($row + (pow(2, ($col - 1)))) % pow(2, $col) == 0;
        return !empty($this->contestantCells[$row][$col]);
    }
    
    public function isConnectorCell($row, $col) {
        return !empty($this->connectorCells[$row][$col]);
        // To keep the championship round from having a tail...
        /*
        if ($col < $this->cols) {
            // This is a commonality for steps in a range and for next range start
            $colPow = pow(2, $col);
            
            // How many consecutive column cells are right bordered in a column 
            $steps = $colPow - 1;
            
            // This is actually to lower end of the range of bordered cells in the column 
            $limit = pow(2, ($col - 1)) + 1;
            
            // This builds an array of cell locations in a column
            $range = array();
            
            // Only work on cells that are within our row range so we don't do too much math
            while ($limit <= $row) {
                // Stick the beginning of the range on to the range list
                $range[] = $limit;
                
                // Walk the steps adding in each column cell in the column to the list of bordered cells
                for ($i = 0; $i < $steps; $i++) {
                    $range[] = ++$limit;
                }
                
                // Move to the next group of cells in the column
                $limit += $colPow + 1;
            }
    
            // Let the client know if their requested cell is to be bordered right
            return in_array($row, $range);
        }
    
        return false;
        */
    }
    
    protected function _setSeedCells() {
        $this->seedCells = array(
            1 => 1,
            2 => $this->rows,
            3 => $this->contestants + 1,
            4 => $this->contestants - 1,
            5 => ((1 + ($this->contestants - 1)) / 2) + 1,
            6 => (($this->rows + ($this->contestants + 1)) / 2) - 1,
            7 => (($this->rows + ($this->contestants + 1)) / 2) + 1,
            8 => ((1 + ($this->contestants - 1)) / 2) - 1,
            9 => ($this->contestants / 4) + 1,
            10 => (($this->rows + $this->contestants + 1) / 4) + $this->contestants - 1,
            11 => ($this->contestants + ($this->contestants / 4)) + 1,
            12 => (($this->rows + ($this->contestants + 1)) / 4) - 1,
            13 => (($this->rows + ($this->contestants + 1)) / 4) + 1,
            14 => ($this->contestants + ($this->contestants / 4)) - 1,
            15 => (($this->rows + $this->contestants + 1) / 4) + $this->contestants + 1,
            16 => ($this->contestants / 4) - 1,
        );
    }
    
    protected function _setContestantCells() {
        for ($i = 1; $i <= $this->rows; $i++) {
            for ($j = 1; $j <= $this->cols; $j++) {
                if (($i + (pow(2, ($j - 1)))) % pow(2, $j) == 0) {
                    $this->contestantCells[$i][$j] = true;
                }
            }
        }
    }
    
    protected function _setConnectorCells() {
        for ($i = 1; $i <= $this->rows; $i++) {
            for ($j = 1; $j < $this->cols; $j++) {
                // This is a commonality for steps in a range and for next range start
                $colPow = pow(2, $j);
                
                // How many consecutive column cells are right bordered in a column 
                $steps = $colPow - 1;
                
                // This is actually to lower end of the range of bordered cells in the column 
                $limit = pow(2, ($j - 1)) + 1;
                
                // This builds an array of cell locations in a column
                $range = array();
                
                // Only work on cells that are within our row range so we don't do too much math
                while ($limit <= $i) {
                    // Stick the beginning of the range on to the range list
                    $range[] = $limit;
                    
                    // Walk the steps adding in each column cell in the column to the list of bordered cells
                    for ($k = 0; $k < $steps; $k++) {
                        $range[] = ++$limit;
                    }
                    
                    // Move to the next group of cells in the column
                    $limit += $colPow + 1;
                }
        
                // Let the client know if their requested cell is to be bordered right
                if (in_array($i, $range)) {
                    $this->connectorCells[$i][$j] = true;
                }
                
            }
        }
    }
} 
