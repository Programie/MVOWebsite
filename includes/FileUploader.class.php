<?php

/**
 * Class FileUploader
 *
 * Handles HTTP POST form uploads
 */
class FileUploader
{
	private $files;

	public function __construct()
	{
		$this->files = array();

		foreach ($_FILES as $fileField)
		{
			// A file element can contain multiple files
			if (is_array($fileField["tmp_name"]))
			{
				$files = array();

				foreach ($fileField as $key => $items)
				{
					foreach ($items as $index => $item)
					{
						$files[$index][$key] = $item;
					}
				}

				foreach ($files as $file)
				{
					$fileId = $this->processFile($file);

					if ($fileId === null or $fileId === false)
					{
						continue;
					}

					$this->files[] = $fileId;
				}
			}
			else
			{
				$fileId = $this->processFile($fileField);

				if ($fileId === null or $fileId === false)
				{
					continue;
				}

				$this->files[] = $fileId;
			}
		}
	}

	/**
	 * Process the specified file
	 *
	 * @param array $file The element from the $_FILES array
	 * @throws Exception if an error occurred
	 * @return null|int the ID of the uploaded file or null if there is no file
	 */
	private function processFile($file)
	{
		// Fields without a file should be skipped
		if ($file["error"] == UPLOAD_ERR_NO_FILE)
		{
			return null;
		}

		if ($file["error"] != UPLOAD_ERR_OK)
		{
			throw new Exception("Upload error", $file["error"]);
		}

		$filename = md5_file($file["tmp_name"]);

		if (!@move_uploaded_file($file["tmp_name"], __DIR__ . "/../uploads/" . $filename))
		{
			throw new Exception("Unable to move uploaded file");
		}

		$query = Constants::$pdo->prepare("
			INSERT INTO `uploads`
			SET
				`name` = :name,
				`title` = :title
		");

		$query->execute(array
		(
			":name" => $filename,
			":title" => basename($file["name"])
		));

		return (int) Constants::$pdo->lastInsertId();
	}

	/**
	 * Get the list of IDs of successfully uploaded files
	 * @return array
	 */
	public function getFileIds()
	{
		return $this->files;
	}
}