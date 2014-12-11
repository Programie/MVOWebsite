<?php

/**
 * Class FileUploader
 *
 * Handles HTTP POST form uploads
 */
class FileUploader
{
	/**
	 * @var array
	 */
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
					$fileData = $this->processFile($file);

					if ($fileData === null)
					{
						continue;
					}

					$this->files[] = $fileData;
				}
			}
			else
			{
				$fileData = $this->processFile($fileField);

				if ($fileData === null)
				{
					continue;
				}

				$this->files[] = $fileData;
			}
		}
	}

	/**
	 * Process the specified file
	 *
	 * @param array $file The element from the $_FILES array
	 * @throws Exception if an error occurred
	 * @return null|StdClass A map containing the ID, filename and title or null if there is no file
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

		$title = basename($file["name"]);

		$query = Constants::$pdo->prepare("
			INSERT INTO `uploads`
			SET
				`name` = :name,
				`title` = :title
		");

		$query->execute(array
		(
			":name" => $filename,
			":title" => $title
		));

		$data = new StdClass;

		$data->id = (int) Constants::$pdo->lastInsertId();
		$data->name = $filename;
		$data->title = $title;

		return $data;
	}

	/**
	 * Get a list of successfully uploaded files.
	 * Each array element is a map containing the ID, filename and title of the file.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}
}