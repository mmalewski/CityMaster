<?php
class Autoloader
{
	public $path;

	/**
	* Sets classes folder path and registers the autoloader
	* @param string $path class folder path
	*
	* @return void
	*/
	public static function init ($path = null)
	{
		$autoloader = new Autoloader();

		if (!empty($path)) {
			$autoloader->path = $path;
		}

		spl_autoload_register(array($autoloader, 'loader'));
	}

	/**
	 * Loads a class
	 * @param string $class class name
	 *
	 * @return void
	 */
	private function loader ($class)
	{
		//check if its a controller class
		$firstChar = substr($class, 0, 1);
		$oClass = $class;

		if ($firstChar == 'c')
		{
			$class = strtolower(substr($class, 1));
			$path = $this->path.'controllers/';
		} else {
			$path = $this->path.'classes/';
		}

		$ClassFile = str_replace('\\', DIRECTORY_SEPARATOR, $class);

		$ClassFile = $ClassFile.'.php';
		$ClassPath = $path.$ClassFile;

		$ClassPath = str_replace('\\', DIRECTORY_SEPARATOR, $ClassPath);
		$ClassPath = str_replace('/', DIRECTORY_SEPARATOR, $ClassPath);
		//echo $ClassPath."-".var_dump(file_exists($ClassPath))."-".var_dump(class_exists($class))." (".$oClass.") <br>";

		// boost speed on production env
		if (Config::get('currentEnv') == 'local') {
			if (file_exists($ClassPath) && (!class_exists($oClass)) ) {
				include_once($ClassPath);
			}
		} else {
			include_once($ClassPath);
		}
	}
}

