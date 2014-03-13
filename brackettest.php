<?php
$pagetitle = 'Bracket tests';

$men = $cols = $rows = $matches = 0;
$heading = null;
$pool = array();
// Testing $_GET['e'] is the number of entrants
if (!empty($_GET['e']) && is_numeric($_GET['e'])) {
    // Testing
    require_once 'bracket.php';
    $bracket = new Bracket($_GET['e']);
    
    // This is our entrant count
    //$entries = $_GET['e'];
    $entries = $bracket->entrants;
    
    // This gets our base number of matchups to determine our rounds
    //$l = log($entries, 2);
    //$rounds = ceil($l);
    $rounds = $bracket->rounds;
    
    // The number of men is 2 to the $rounds power... 
    // makes it compatible with evenly matched parinings
    //$men = pow(2, $rounds);
    $men = $bracket->contestants;
    
    // The total number of matches for this bracket
    //$matches = $men - 1;
    $matches = $bracket->matches;
    
    // The actual bracket table structure
    //$cols = $rounds + 1;
    //$rows = ($men * 2) - 1;
    $cols = $bracket->cols;
    $rows = $bracket->rows;
    
    // Basic output
    $heading = "Entries: $entries<br />Men: $men<br />Rounds: $rounds<br />Cols: $cols<br />Rows: $rows<br />Matches: $matches";
    
    // Handle pairings and match placement within the bracket
    
    $groupMax = $men / 2;
    $groupIndex = 1; // Round
    $groups = array();
    for ($i = 1; $i < $men; $i++) {
        $groups[$groupIndex][] = $i;
        if ($i == $groupMax) {
            $groupMax = $groupMax / 2;
            $groupIndex++;
        }
    }
    //var_dump($groups);
    for ($i = 1; $i <= $rows; $i++) {
        if (!isset($x)) {
            $x = $men;
        } else {
            $x = $x / 2;
        }
        
        //foreach (range(1, $x) as $zz) {
        //    $rounds[$i] = '';
        //}
    }
    
    //var_dump($rounds);
    $names = array();
    for ($i = 1; $i <= $entries; $i++) {
        $names[$i] = getName();
    }
    // 1-16, 2-15, 3-14, 4-13, 5-12, 6-11, 7-10, 8-9
    // 1 vs $men, 2 vs $men - 1, 3 vs $men - 2
    //for ($lower = 1, $upper = $men; $lower < $upper; $lower += 2, $upper -= 2) {
    for ($i = 1, $r = 1; $i <= $men; $i++, $r += 2) {
        if (isset($names[$i])) {
            $pool[1][$r] = $names[$i];
        }
    }
}

function hasUnder($row, $col) {
    // Algorithm for this is simple:
    // row + 2^($col-1) / 2^$col = whole number
    return ($row + (pow(2, ($col - 1)))) % pow(2, $col) == 0;
}

function hasRight($row, $col, $cols) {
    // To keep the championshop round from having a tail...
    if ($col < $cols) {
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
}

function getMatchPair($row, $col, $cols) {
    static $matches = array();
}

function getEntrant($row, $col) {
    global $pool;
    if ($col == 1) {
        if (isset($pool[$col][$row])) {
            return $pool[$col][$row];
        } else {
            return 'BYE ' . $row . ' ' . $col;
            //return getName() . ' ' . $row . ' ' . $col;
        }
    } else {
        return null;
    }
}

function getSeed($row, $col, $rows) {
    if ($col == 1) {
        $men = ($rows + 1) / 2;
        $factors = array(
            1 => 1,
            2 => $rows,
            3 => $men + 1,
            4 => $men - 1,
            5 => ($men / 2) + 1,
            6 => (($rows + ($men + 1)) / 2) - 1,
            7 => (($rows + ($men + 1)) / 2) + 1,
            8 => ($men / 2) - 1,
            9 => ($men / 4) + 1,
            10 => (($rows + $men + 1) / 4) + $men - 1,
            11 => ($men + ($men / 4)) + 1,
            12 => (($rows + ($men + 1)) / 4) - 1,
            13 => (($rows + ($men + 1)) / 4) + 1,
            14 => ($men + ($men / 4)) - 1,
            15 => (($rows + $men + 1) / 4) + $men + 1,
            16 => ($men / 4) - 1,
        );
        
        $key = array_search($row, $factors);
        if ($key) {
            return $key . '. ';
        }
    }
    
    return null;
}

function getName() {
    // First names
    $first = array(
        'Mike', 'John', 'Joey', 'Chance',
        'Billy', 'Dave', 'Sam', 'Drake',
        'Blake', 'Ed', 'Donny', 'Samuel',
        'Tommy', 'Micah', 'Jonas', 'Jacob',
    );
    
    // Last names
    $last  = array(
        'Johnson', 'Kellogg', 'Garcia', 'Gomez',
        'Wilson', 'Williams', 'Wolfe', 'Montgomery',
        'Jefferson', 'Norris', 'Mason', 'Smith',
        'Diaz', 'Percey', 'Neville', 'Baker',
    );
    
    $f = mt_rand(0, 15);
    $l = mt_rand(0, 15);
    
    return $first[$f] . ' ' . $last[$l];
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
    <title><?php echo $pagetitle ?></title> 
    <style type="text/css">
        table {
            empty-cells: show;
        }
        
        table, td {
            /*border: solid 1px #ccc;*/
            border-collapse: collapse;
        }
        
        td {
            width: 200px;
            height: 30px;
        }
        
        td.under {
            border-bottom: solid 1px #000;
        }
        
        td.right {
            border-right: solid 1px #000;
        }
    </style>
</head>
<body>
    <?php if ($men && $cols && $rows): ?> 
    <h1><?php echo $men ?> Man Bracket</h1>
    <div>
        <?php echo $heading ?> 
    </div>
    <table>
        <?php for ($ii = 1; $ii <= $rows; $ii++): ?> 
        <tr id="row-<?php echo $ii ?>">
            <?php for ($jj = 1; $jj <= $cols; $jj++): $under = hasUnder($ii, $jj); $right = hasRight($ii, $jj, $cols); ?> 
            <td id="cell-<?php echo $ii, '-', $jj; ?>" class="cell<?php if ($under) echo ' under'; if ($right) echo ' right'; ?>">
                <?php if ($under) echo getSeed($ii, $jj, $rows) . getEntrant($ii, $jj); ?> 
            </td>
            <?php endfor; ?> 
        </tr>
        <?php endfor; ?> 
    </table>
    <?php endif; ?> 
</body>