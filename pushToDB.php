<?php
	header('Access-Control-Allow-Origin: *');
	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	header("Content-type: application/json");
	$input = file_get_contents("php://input");

	$conn = pg_connect("host=92.62.34.78 port=5432 dbname=adrianto user=adrianto password=kalende");
	pg_set_error_verbosity($conn, PGSQL_ERRORS_VERBOSE);
	if ($conn === false)
	{
		echo "An error occurred connecting to the database.\n";
		var_dump(pg_last_error($conn));
		exit;
	}
	$json_object = json_decode($input);

	$values_columns = array();
	$values = array();
	$conditions = array();
	$to_select = array();
	$conditions_array = array();
	$table ="";
	$sqlopt = $json_object->sqlopt;
	if (array_key_exists('table', $json_object)) 
	{
		$table = "public." . $json_object->table;
	}
	if (array_key_exists('values', $json_object)) 
	{
		foreach ($json_object->values as $column)
		{
			$values[$column->column] = $column->data;
		}
	}
	if (array_key_exists('conditions', $json_object))
	{
		foreach ($json_object->conditions as $column)
		{
			$conditions[$column->column] = $column->data;

			array_push($conditions_array, $column->column .' = ' . $column->data);
		}
	}
	if (array_key_exists('to_select', $json_object))
	{
		foreach ($json_object->to_select as $column)
		{
			array_push($to_select, $column->column);
		}
	}
	if (array_key_exists('sql', $json_object))
	{
		$sql = $json_object->sql;
	}

    switch ($sqlopt) {
	    case "insert":
	    	$sql = "insert into " . $table . "(" . implode(", ", array_keys($values)) . ") values (" . implode(", ", array_values($values)) . ");";
	        $result = pg_query($conn, $sql);
	        if ($result == false) {
	        	$result = pg_last_error();
	        } else {
	        	$result = true;
	        }
	        if ($table == 'public.location_user' || $table == 'public.group_user') {
	        	break; //relasjonstabeller skal ikke returnere noen id
	        }
    		$result = pg_query($conn, "select currval('" . $table . "_" . $json_object->table . "_id_seq');");
	        break;
	    case "update":
	        $result = pg_update($conn, $table, $values, $conditions);     
	        break;
	    case "select":
	    	$sql = "select " . implode(", ", $to_select) . " from " . $table . " where " . implode(" and ", $conditions_array) . ";";
	        $result = pg_query($conn, $sql);
	        break;
	    case "delete":
	        $result = pg_delete($conn, $table, $conditions);
	        break;
        case "sql":
        	$result = pg_query($conn, $sql);
        	$str ="[";
    	    while ($result_row = pg_fetch_assoc($result)) {
    			$str .= json_encode($result_row) . ",";
    		}
    		$str = rtrim($str, ",");
    		$str .= "]";
    		echo $str;
    		exit;
    }

	if (is_bool($result)) {
		echo $result ? 'true' : 'false';
		exit;
	}

	// if ($table == 'public.location_user' || $table == 'public.group_user') {
	// 	$result_array = array();
	// 	while ($result_row = pg_fetch_row($result)) {
	// 		$result_array[] = $result_row;
 //    	}
 //    	echo json_encode($result_array);
	// } else {
    while ($result_row = pg_fetch_assoc($result)) {
    	echo json_encode($result_row);
    }
	// }
?>