<?php
class Constants
{
	/**
	 * @var $accountManager AccountManager The instance of the account manager
	 */
	public static $accountManager;
	/**
	 * @var $pageManager PageManager The instance of the page manager
	 */
	public static $pageManager;
	/**
	 * @var $pagePath Array An array holding all page parts of the path of the requested page
	 */
	public static $pagePath;
	/**
	 * @var $pdo PDO An instance of the MySQL database connection using PDO
	 */
	public static $pdo;
}