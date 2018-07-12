<?php
	namespace DaybreakStudios\DozeBundle\Request;

	use Psr\Log\LoggerInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
	use Symfony\Component\HttpKernel\Event\GetResponseEvent;

	/**
	 * Used to automatically add CORS required headers to responses, as well as to respond to OPTIONS requests that
	 * are not explicitly handled by routing.
	 *
	 * @package DaybreakStudios\DozeBundle\Request
	 */
	class CORSListener {
		/**
		 * @var LoggerInterface
		 */
		private $logger;

		/**
		 * @var bool
		 */
		private $enabled;

		/**
		 * @var array
		 */
		private $allowedOrigins;

		/**
		 * @var array
		 */
		private $allowedHeaders;

		/**
		 * @var array
		 */
		private $exposedHeaders;

		/**
		 * @var array
		 */
		private $allowedMethods;

		/**
		 * @var bool
		 */
		private $allowCredentials;

		/**
		 * @var bool
		 */
		private $wildcardOriginAllow;

		/**
		 * CORSListener constructor.
		 *
		 * @param LoggerInterface $logger
		 * @param bool            $enabled
		 * @param array           $allowedOrigins
		 * @param array           $allowedHeaders
		 * @param array           $allowedMethods
		 * @param bool            $allowCredentials
		 * @param array           $exposedHeaders
		 */
		public function __construct(
			LoggerInterface $logger,
			$enabled,
			array $allowedOrigins,
			array $allowedHeaders,
			array $allowedMethods,
			$allowCredentials = true,
			array $exposedHeaders = []
		) {
			$this->logger = $logger;
			$this->enabled = $enabled;
			$this->allowedOrigins = $allowedOrigins ?: ['null'];
			$this->allowedHeaders = $allowedHeaders;
			$this->exposedHeaders = $exposedHeaders;
			$this->allowedMethods = $allowedMethods;
			$this->allowCredentials = $allowCredentials;

			$this->wildcardOriginAllow = sizeof($allowedOrigins) === 1 && $allowedOrigins[0] === '*';

			if ($allowCredentials && $this->wildcardOriginAllow)
				throw new \InvalidArgumentException('You cannot use wildcard allowed origins when allow ' .
					'credentials is true');
		}

		/**
		 * @return boolean
		 */
		public function isEnabled() {
			return $this->enabled;
		}

		/**
		 * @param Request $request
		 *
		 * @return string
		 */
		public function getAllowedOrigin(Request $request) {
			$origin = $request->headers->get('origin');

			if ($this->wildcardOriginAllow || in_array($origin, $this->allowedOrigins))
				return $origin;

			return @$this->allowedOrigins[0];
		}

		/**
		 * @param Request $request
		 *
		 * @return array
		 */
		public function getAllowedHeaders(Request $request) {
			if (sizeof($this->allowedHeaders) === 1 && $this->allowedHeaders[0] === '*') {
				$requestHeaders = $request->headers->get('Access-Control-Request-Headers');

				if ($requestHeaders)
					$this->allowedHeaders = array_map(function($header) {
						return trim($header);
					}, explode(',', $requestHeaders));
			}

			return $this->allowedHeaders;
		}

		/**
		 * @param Response $response
		 *
		 * @return array
		 */
		public function getExposedHeaders(Response $response) {
			if (sizeof($this->exposedHeaders) === 1 && $this->exposedHeaders[0] === '*') {
				$headers = [];

				foreach ($response->headers->all() as $key => $value)
					$headers[$key] = true;

				$this->exposedHeaders = array_keys($headers);
			}

			return $this->exposedHeaders;
		}

		/**
		 * @param Request $request
		 *
		 * @return array
		 */
		public function getAllowedMethods(Request $request) {
			if (sizeof($this->allowedOrigins) === 1 && $this->allowedOrigins[0] === '*') {
				$requestMethod = $request->headers->get('Access-Control-Request-Method');

				if ($requestMethod)
					$this->allowedMethods = [$requestMethod];
			}

			return $this->allowedMethods;
		}

		/**
		 * @return boolean
		 */
		public function getAllowCredentials() {
			return $this->allowCredentials;
		}

		/**
		 * @param GetResponseEvent $event
		 */
		public function onKernelRequest(GetResponseEvent $event) {
			if (!$this->isEnabled() || !$event->isMasterRequest())
				return;

			$request = $event->getRequest();

			$this->logger->debug('Considering request for CORS injection');

			if ($request->getMethod() !== Request::METHOD_OPTIONS)
				return;

			$allowHeaders = array_map(function ($item) {
				return strtolower(trim($item));
			}, explode(',', $request->headers->get('Access-Control-Request-Headers')));

			$this->logger->debug('Detected Access-Control-Request-Headers', [
				'headers' => $allowHeaders,
			]);

			$this->logger->notice('Injecting CORS response');

			$event->setResponse(new Response('', Response::HTTP_OK, $this->getCORSHeadersForRequest($request)));
		}

		/**
		 * @param FilterResponseEvent $event
		 */
		public function onKernelResponse(FilterResponseEvent $event) {
			if (!$this->isEnabled())
				return;

			$response = $event->getResponse();

			if ($response->headers->has('Access-Control-Allow-Origin'))
				return;

			$response->headers->add($this->getCORSHeadersForRequest($event->getRequest()));

			if ($exposed = $this->getExposedHeaders($response)) {
				$response->headers->add([
					'Access-Control-Expose-Headers' => implode(', ', $exposed),
				]);
			}
		}

		/**
		 * @param Request $request
		 *
		 * @return array
		 */
		protected function getCORSHeadersForRequest(Request $request) {
			$headers = [
				'Access-Control-Allow-Origin' => $this->getAllowedOrigin($request),
				'Access-Control-Allow-Headers' => implode(', ', $this->getAllowedHeaders($request)),
				'Access-Control-Allow-Credentials' => $this->getAllowCredentials() ? 'true' : 'false',
				'Access-Control-Allow-Methods' => implode(', ', $this->getAllowedMethods($request)),
			];

			if (!$this->wildcardOriginAllow)
				$headers['Vary'] = 'Origin';

			return $headers;
		}
	}