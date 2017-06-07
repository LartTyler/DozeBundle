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
		private $allowedMethods;

		/**
		 * @var bool
		 */
		private $allowCredentials;

		/**
		 * CORSListener constructor.
		 *
		 * @param LoggerInterface $logger
		 * @param bool            $enabled
		 * @param array           $allowedOrigins
		 * @param array           $allowedHeaders
		 * @param array           $allowedMethods
		 * @param bool            $allowCredentials
		 */
		public function __construct(
			LoggerInterface $logger,
			$enabled,
			array $allowedOrigins,
			array $allowedHeaders,
			array $allowedMethods,
			$allowCredentials = true
		) {
			$this->logger = $logger;
			$this->enabled = $enabled;
			$this->allowedOrigins = $allowedOrigins;
			$this->allowedHeaders = $allowedHeaders;
			$this->allowedMethods = $allowedMethods;
			$this->allowCredentials = $allowCredentials;
		}

		/**
		 * @return boolean
		 */
		public function isEnabled() {
			return $this->enabled;
		}

		/**
		 * @return array
		 */
		public function getAllowedOrigins() {
			return $this->allowedOrigins;
		}

		/**
		 * @return array
		 */
		public function getAllowedHeaders() {
			return $this->allowedHeaders;
		}

		/**
		 * @return array
		 */
		public function getAllowedMethods() {
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
		}

		/**
		 * @param Request $request
		 *
		 * @return array
		 */
		protected function getCORSHeadersForRequest(Request $request) {
			return [
				'Access-Control-Allow-Origin' => implode(', ', $this->getAllowedOrigins()),
				'Access-Control-Allow-Headers' => implode(', ', $this->getAllowedHeaders()),
				'Access-Control-Allow-Credentials' => $this->getAllowCredentials() ? 'true' : 'false',
				'Access-Control-Allow-Methods' => implode(', ', $this->getAllowedMethods()),
			];
		}
	}