<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: X-Requested-With");
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

	$values = array();
	$conditions = array();
	$to_select = array();
	$sqlopt = $json_object->sqlopt;
	$table = $json_object->table;

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
		}
	}
	if (array_key_exists('to_select', $json_object))
	{
		foreach ($json_object->to_select as $column)
		{
			array_push($to_select, $column->column);
		}
	}

    switch ($sqlopt) {
	    case "insert":
	        $result = pg_insert($conn, $table, $values);
	        $last_id = pg_last_oid($result);
	        break;
	    case "update":
	        $result = pg_update($conn, $table, $values, $condition);     
	        break;
	    case "select":
	        $result = pg_query($conn, "select " . http_build_query($to_select, '', ",") . " from " . $table . " where " . http_build_query($condition, '', " and " . ";"));
	        break;
	    case "delete":
	        $result = pg_delete($conn, $table, $condition);
	        break;
    }
    if (!$result) {
	  echo "An error asd occurred.\n";
	  var_dump(pg_last_error($conn));
	  exit;
	}
	$result_array = pg_fetch_all($result)
	$result_string = json_encode($result_array);
	echo $result_string;

	// echo $last_id;
?>