<?php
	namespace DaybreakStudios\DozeBundle;

	use DaybreakStudios\DozeBundle\Error\AccessDeniedError;
	use DaybreakStudios\DozeBundle\Error\ApiErrorInterface;
	use DaybreakStudios\DozeBundle\Error\NotFoundError;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\SerializerInterface;

	class Responder implements ResponderInterface {
		/**
		 * @var SerializerInterface
		 */
		protected $serializer;

		/**
		 * @var RequestStack
		 */
		protected $requestStack;

		/**
		 * Responder constructor.
		 *
		 * @param SerializerInterface $serializer
		 * @param RequestStack        $requestStack
		 */
		public function __construct(SerializerInterface $serializer, RequestStack $requestStack) {
			$this->serializer = $serializer;
			$this->requestStack = $requestStack;
		}

		/**
		 * {@inheritdoc}
		 */
		public function createResponse($data = null, $status = null, array $headers = []) {
			$format = $this->requestStack->getCurrentRequest()->getRequestFormat('json');

			if ($data === null && $status === null)
				$status = Response::HTTP_NO_CONTENT;
			else if ($data !== null)
				$data = $this->serializer->serialize($data, $format);

			return new Response($data, $status ?: Response::HTTP_OK, $headers + [
				'Content-Type' => 'application/' . $format,
			]);
		}

		/**
		 * {@inheritdoc}
		 */
		public function createErrorResponse(ApiErrorInterface $error, $status = null, array $headers = []) {
			if ($status === null)
				$status = $error->getHttpStatus() ?: Response::HTTP_BAD_REQUEST;

			return $this->createResponse([
				'error' => [
					'code' => $error->getCode(),
					'message' => $error->getMessage(),
				],
			], $status, $headers);
		}

		/**
		 * {@inheritdoc}
		 */
		public function createAccessDeniedResponse() {
			return $this->createErrorResponse(new AccessDeniedError());
		}

		/**
		 * {@inheritdoc}
		 */
		public function createNotFoundResponse() {
			return $this->createErrorResponse(new NotFoundError());
		}
	}