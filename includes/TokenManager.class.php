<?php
class TokenManager
{
	public static function generateToken()
	{
		return md5(time() . "-" . rand());
	}

	public static function getSendToken($name, $generateNew = false)
	{
		$token = $_SESSION["sendToken_" . $name];

		if (!$token or $generateNew)
		{
			$token = TokenManager::generateToken();
			$_SESSION["sendToken_" . $name] = $token;
		}

		return $token;
	}
}

?>