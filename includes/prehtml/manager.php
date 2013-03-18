<?php
if ($_SERVER["PHP_AUTH_USER"] == "manager" and $_SERVER["PHP_AUTH_PW"] == MANAGER_PASSWORD)
{
	$data = json_decode(file_get_contents("php://input"));
	if ($data)
	{
		$data->service = basename($data->service);
		
		$file = ROOT_PATH . "/includes/remote_manager/Remote_" . $data->service . ".class.php";
		if (file_exists($file))
		{
			require_once $file;
			$class = "Remote_" . $data->service;
			$instance = new $class;
			if ($instance)
			{
				if (method_exists($instance, $data->method))
				{
					$response = $instance->{$data->method}($data->params);
				}
				else
				{
					$response = "Method '" . $data->method . "' does not exist!";
				}
			}
			else
			{
				$response = "Unable to initialize class '" . $data->service . "'!";
			}
		}
		else
		{
			$response = "Class '" . $data->service . "' does not exist!";
		}
	}
	else
	{
		$response = "Invalid JSON-RPC request!";
	}

	header("Content-Type: application/json");
	echo json_encode(array
	(
		"id" => $data->id,
		"result" => $response
	));
}
else
{
	header("HTTP/1.0 401 Unauthorized");
	header("WWW-Authenticate: Basic realm='Manager API'");
}
exit;
?>