<?php
    require_once("includes/db_functions.php"); 
    db_connect(); 
    // require_once("includes/pagination.php");
    
    if (isset($_GET['filterDeck'])) $requestedDeck = $_GET['filterDeck']; // If user wants specific deck.
	if (isset($_GET['filterClass'])) $requestedClass = $_GET['filterClass']; // If user wants specific class.
	if (isset($_GET['search'])) $searchKeyword = $_GET['search'];

    // Step 3: Process Query for Results
    //SELECT
	$subquery = "SELECT ";
	$subquery .= "passenger.pid, name, homeDest, class, cabinNumber, ";
	
    //FROM
	$subquery = substr($subquery, 0, -2); // Remove last comma.
	$subquery .= " FROM passenger"; // Begin table selection.
	$subquery .= " INNER JOIN ticket ON passenger.pid = ticket.pid";
	$subquery .= " INNER JOIN cabin ON passenger.pid = cabin.pid";
    
    //WHERE
    $subquery .= " WHERE (true)"; // Just makes it so I don't have to check for a condition being the FIRST in a long chain.
    if (!empty($requestedDeck) && ($requestedDeck != 'any')) {
        $subquery .= " AND LEFT(cabin.cabinNumber, 1) = '". $requestedDeck . "'";
    } 
    if (!empty($requestedClass) && ($requestedClass != 'any')) {
        $subquery .= " AND ticket.class = '" . $requestedClass . "'";
    }

    if (!empty($searchKeyword)) {
        $searchKeyword = filter_var($searchKeyword, FILTER_SANITIZE_SPECIAL_CHARS);
        $subquery .= " AND (passenger.name LIKE '%".$searchKeyword."%' OR passenger.homeDest LIKE '%".$searchKeyword."%')";
    }

    //SUBMIT
    $subresult = db_query($subquery); // Send off query to msqli.
    $total = $subresult->num_rows; 
    $limit = 10; 
    $pages = ceil($total / $limit);
    $currPage = 1;
    
    //which page are we currently on
    if(isset($_GET['currPage'])){
        $currPage = $_GET['currPage']; 
    }

    //calculate offset for pagination
    $offset = ($currPage - 1) * $limit; 
    
    $query = "SELECT * FROM ($subquery) AS explore LIMIT $limit OFFSET $offset"; 
    $result = db_query($query); 
    $outputArr = []; 

    while ($row = $result->fetch_assoc()) { // Get associative array row by row.
        $output =  '<tr class="row-listener" id="'.$row['pid'].'">
                    <td id="passengerName">'.$row['name'].'</td>
                    <td id="homeDest"';
                    if (!empty($row['homeDest'])) $output .= '>'.$row['homeDest'];
				    else $output .= ' class="unknown">Unknown';
        $output .=  '</td>
                    <td id="cabinNumber"';
                    if (!empty($row['cabinNumber'])) $output .= '>'.$row['cabinNumber'];
				    else $output .= ' class="unknown">Unknown';
        $output .=  '</td>
                    <td id="class">'.$row['class'].'</td>
                    </tr>';

        array_push($outputArr, $output); 
    }

    $arr = ["output" => $outputArr, "pages" => $pages]; 
    echo json_encode($arr); //encode values in an array to be read as JSON objects in AJAX
                
?>