<?
namespace ApisLv;

/**
 * APIs.lv base class for creating API requests.
 *
 * Basic usage:
 * <code>
 * $api = new \ApisLv\Api('put-your-api-key-here');
 * echo $api->namedays_for_date(12, 31)->as_json();
 * //=> ["Silvestrs","Silvis","Kalvis"]
 * </code>
 *
 * The getter methods are:
 * <ul>
 * <li>as_php() - returns a PHP array</li>
 * <li>as_xml() - returns an XML string</li>
 * <li>as_json() - returns a JSON string</li>
 * </ul>
 *
 * The API key can also be set statically:
 * <code>
 * \ApisLv\Api::set_key('put-your-api-key-here');
 * $api = new \ApisLv\Api();
 * </code>
 *
 * Or you can edit the class itself to set the key
 * @see Api::$default_key
 * <code>
 * private static $default_key = 'put-your-api-key-here';
 * </code>
 *
 * @throws Exception
 */
class Api {

	/**
	 * @var string
	 * The API key that will be used if no key is passed in the constructor.
	 * Set it here if you want to avoid a call to set_key()
	 */
	private static $default_key;
	/** @var string */
	private $key;


	/**
	 * Sets default API key for new object instances
	 *
	 * @static
	 * @param string $key Your API key
	 */
	public static function set_key($key)
	{
		static::$default_key = $key;
	}

	/**
	 * @param string $key Your API key
	 */
	public function __construct($key = null)
	{
		if ($key) {
			$this->key = $key;
		} else if (static::$default_key) {
			$this->key = static::$default_key;
		} else {
			throw new Exception('Please provide an API key in the constructor or statically with set_key()');
		}
	}


	/**
	 * Namedays
	 *
	 * @return Request
	 */
	public function namedays()
	{
		return $this->request('namedays');
	}

	/**
	 * Namedays on a given date
	 *
	 * @param int $month
	 * @param int $day
	 * @return Request
	 */
	public function namedays_for_date($month, $day)
	{
		return $this->request('namedays', array('date' => "{$month}-{$day}"));
	}

	/**
	 * List of banks
	 *
	 * @param string $language Language for internationalized country names
	 * @return Request
	 */
	public function banks($language = null)
	{
		return $this->request('banks', array('lang' => $language));
	}

	/**
	 * List of countries
	 *
	 * @param string $language Language for internationalized country names
	 * @return Request
	 */
	public function countries($language = null)
	{
		return $this->request('countries', array('lang' => $language));
	}

	/**
	 * Currency rates for the given date
	 *
	 * @param int|null $date Leave blank to use today's date
	 * @return Request
	 * @throws Exception
	 */
	public function currency_rates($date = null)
	{
		if ($date !== null) {
			if (!is_int($date)) {
				throw new Exception('currency_rates() expects parameter $date to be integer');
			}
			$date = date('Y-m-d', $date);
		}

		return $this->request('currencyrates', array('date' => $date));
	}


	/**
	 * @param string $resource
	 * @param array $params
	 * @return Request
	 */
	private function request($resource, $params = array())
	{
		return new Request($this->key, $resource, $params);
	}

}

/**
 * An API request.
 *
 * @throws Exception
 */
class Request {

	const BASE_URL = 'http://apis.lv/';

	/** @var string */
	private $resource;
	/** @var array */
	private $params;


	/**
	 * @param string $key Your API key
	 * @param string $resource The API to fetch
	 * @param array $params Query string parameters
	 */
	public function __construct($key, $resource, $params = array())
	{
		$this->resource = $resource;
		$this->params = $params;
		$this->params['key'] = $key;
	}

	/**
	 * Returns the data as a JSON string
	 *
	 * @return string
	 * @throws Exception
	 */
	public function as_json()
	{
		return $this->fetch('json');
	}

	/**
	 * Returns the data as an XML string
	 *
	 * @return string
	 * @throws Exception
	 */
	public function as_xml()
	{
		return $this->fetch('xml');
	}

	/**
	 * Returns the data as a PHP array
	 *
	 * @return array
	 * @throws Exception
	 */
	public function as_php()
	{
		return json_decode($this->fetch('json'));
	}


	/**
	 * @param string $format
	 * @return string
	 * @throws Exception
	 */
	private function fetch($format)
	{
		$url = static::BASE_URL . $this->resource . '.' . $format . '?' . http_build_query($this->params);

		$data = @file_get_contents($url);
		if ($data === false) {
			throw new Exception("Error: Cannot read {$url}");
		}

		return $data;
	}

}


class Exception extends \Exception {

}
