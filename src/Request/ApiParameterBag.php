<?php
	namespace DaybreakStudios\DozeBundle\Request;

	use Symfony\Component\HttpFoundation\ParameterBag;
	use Symfony\Component\HttpFoundation\Request;

	/**
	 * Used as a controller action argument type hint to automatically convert request content into a ParameterBag.
	 *
	 * To define required body parameters, you may set `_payload_required` in the route's defaults section to an array
	 * of required keys.
	 *
	 * @package DaybreakStudios\DozeBundle\Request
	 */
	class ApiParameterBag extends ParameterBag {
		/**
		 * @var string[]
		 */
		protected $missing = [];

		/**
		 * ApiParameterBag constructor.
		 *
		 * @param array $parameters
		 * @param array $required
		 */
		public function __construct(array $parameters = [], array $required = []) {
			parent::__construct($parameters);

			foreach ($required as $key)
				if (!$this->has($key))
					$this->missing[] = $key;
		}

		/**
		 * Returns true if no required parameter keys were missing from the initial bag contents.
		 *
		 * @return bool
		 */
		public function isValid() {
			return sizeof($this->missing) === 0;
		}

		/**
		 * Returns an array containing any keys from the required keys array that were not present in the initial
		 * parameters array.
		 *
		 * @return string[]
		 */
		public function getMissing() {
			return $this->missing;
		}

		/**
		 * Creates a new instance using JSON data in the request body.
		 *
		 * @param Request $request
		 *
		 * @return static
		 */
		public static function fromRequest(Request $request) {
			$required = $request->attributes->get('_payload_required', []);

			return new static(json_decode($request->getContent(), true) ?: [], $required);
		}
	}