<?php

class AuthToken extends BasicClass
{
	private $duration = 7200;
	public $attributes = array
	(
		array('id','integer'),
		array('app','integer'),
		array('token','integer'),
		array('date','datetime'),
	);

	public function __construct($array=null)
	{
		parent::__construct();

		if ($array != null) {
			foreach ($array as $row => $v) {
				$this->$row = $v;
			}
		}
	}

	/**
         * Generates an authToken
         *
         * @return string
         **/
	public function generateToken() {
		return md5($this->app.'-'.time());
	}

	/**
	 * Validates an authToken
	 * @param string $authToken
	 *
	 * @return object (App object)
	 */
	public function validate($authToken)
	{
		$key = "authToken-".$authToken;
		
		$data = Cache::get($key);

		if(empty($data))
		{
			$where = array(
				array('token','=',$authToken)
			);
			$results = $this->find($where);

			if (sizeof($results) == 0) {
				throw new Exception("Invalid token");
			}
			$app = new App();
			$rawData = $app->getRaw($results[0]->app);

			if (!$rawData) {
				throw new Exception("This app doesn't exist");
			} else {
				$data = $rawData;
			}

			Cache::set($key, $data, $this->duration);
		}
		$app = new App($data);

		if ($app->isBanned()) {
			throw new Exception("This app can't create tokens");
		}

		return $app;
	}

	/**
        *  Insert/Update a token
        *
        * @return string (token)
        */
	public function save()
	{
		$this->token = $this->generateToken();
		$this->date = $this->now();

		$dataToSave = array();

		$q = "select `id` from `".$this->table."` where `app`='".$this->app."'  LIMIT 1";
		$rows = Service::getDB()->query($q);

		foreach ($this->attributes as $attr) {
			$k = $attr[0];
			$dataToSave[$k] = $this->$k;
		}

		if (sizeof($rows)>0)
		{
			Service::getDB()->where('id', $rows[0]['id']);
			$insertId = Service::getDB()->update($this->table, $dataToSave);
		} else {
			$insertId = Service::getDB()->insert($this->table, $dataToSave);
		}

		if ($this->id == "") {
			$this->id = $insertId;
		}
		return $this->token;
	}

}
