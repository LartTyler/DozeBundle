<?php
	namespace DaybreakStudios\DozeBundle;

	use DaybreakStudios\Doze\Errors\ApiErrorInterface;
	use DaybreakStudios\Doze\Responder;
	use DaybreakStudios\Doze\Serializer\FieldSelectorParser;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
	use Symfony\Component\Serializer\SerializerInterface;

	/**
	 * Class ResponderService
	 *
	 * @package DaybreakStudios\DozeBundle
	 */
	class ResponderService {
		/**
		 * @var Responder
		 */
		protected $responder;
		/**
		 * @var RequestStack
		 */
		protected $requestStack;

		/**
		 * @var string
		 */
		protected $fieldsKey;

		/**
		 * @var string
		 */
		protected $defaultFormat;

		/**
		 * ResponderService constructor.
		 *
		 * @param SerializerInterface $serializer
		 * @param RequestStack        $requestStack
		 * @param string              $fieldsKey
		 * @param string              $defaultFormat
		 */
		public function __construct(
			SerializerInterface $serializer,
			RequestStack $requestStack,
			$fieldsKey = 'fields',
			$defaultFormat = 'json'
		) {
			$this->requestStack = $requestStack;
			$this->responder = new Responder($serializer);
			$this->fieldsKey = $fieldsKey;
			$this->defaultFormat = $defaultFormat;
		}

		/**
		 * Creates a response.
		 *
		 * If no value is provided for $data, no body content should be be sent, and the HTTP NO CONTENT
		 * status code should be returned. If any value aside from `null` is used, it should be serialized prior to
		 * being set as the response body.
		 *
		 * @param mixed|null $data    the response data; if null, no response body should be set, and HTTP_NO_CONTENT
		 *                            should be used as the response status (unless $status is explicitly set)
		 * @param int        $status  the HTTP status; if `null` is provided, the status should be inferred to be 200 OK
		 *                            if $data is not null, or 204 NO CONTENT if it is
		 * @param array      $headers an array of headers to send; this array should take precedence over any default
		 *                            headers (such as Content-Type)
		 * @param array      $context an array containing context options for serialization, in the format
		 *                            "context-key" => "value"
		 *
		 * @return Response
		 */
		public function createResponse($data = null, $status = null, array $headers = [], array $context = []) {
			$request = $this->requestStack->getCurrentRequest();

			if ($fields = $request->get($this->fieldsKey))
				$context[AbstractNormalizer::ATTRIBUTES] = (new FieldSelectorParser($fields))->all();

			return $this->responder->createResponse($request->getRequestFormat($this->defaultFormat), $data, $status,
				$headers, $context);
		}

		/**
		 * Creates an error response.
		 *
		 * @param ApiErrorInterface $error   an error objecting describing the error that occurred
		 * @param int|null          $status  the HTTP status; if no status is provided, it should default to
		 *                                   400 BAD REQUEST
		 * @param array             $headers an array of headers to send; this array should take precedence over any
		 *                                   default headers (such as Content-Type)
		 * @param array             $context an array containing context options for serialization, in the format
		 *                                   "context-key" => "value"
		 *
		 * @return Response
		 */
		public function createErrorResponse(
			ApiErrorInterface $error,
			$status = null,
			array $headers = [],
			array $context = []
		) {
			return $this->responder->createErrorResponse($error, $this->getFormat(), $status, $headers, $context);
		}

		/**
		 * Creates an error response using AccessDeniedError.
		 *
		 * @return Response
		 * @see AccessDeniedError
		 */
		public function createAccessDeniedResponse() {
			return $this->responder->createAccessDeniedResponse($this->getFormat());
		}

		/**
		 * Creates an error response using NotFoundError.
		 *
		 * @return Response
		 * @see NotFoundError
		 */
		public function createNotFoundResponse() {
			return $this->responder->createNotFoundResponse($this->getFormat());
		}

		/**
		 * @return string
		 */
		protected function getFormat() {
			return $this->requestStack->getCurrentRequest()->getRequestFormat($this->defaultFormat);
		}
	}