<?php
	namespace DaybreakStudios\DozeBundle;

	use DaybreakStudios\DozeBundle\Error\AccessDeniedError;
	use DaybreakStudios\DozeBundle\Error\ApiErrorInterface;
	use DaybreakStudios\DozeBundle\Error\NotFoundError;
	use Symfony\Component\HttpFoundation\Response;

	interface ResponderInterface {
		/**
		 * Creates a response.
		 *
		 * If no value is provided for $data, no body content should be be sent, and the HTTP NO CONTENT
		 * status code should be returned. If any value aside from `null` is used, it should be serialized prior to
		 * being set as the response body.
		 *
		 * @param mixed|null $data the response data; if null, no response body should be set, and HTTP_NO_CONTENT
		 *                         should be used as the response status (unless $status is explicitly set
		 * @param int        $status
		 * @param array      $headers
		 *
		 * @return Response
		 */
		public function createResponse($data = null, $status = null, array $headers = []);

		/**
		 * Creates an error response.
		 *
		 * @param ApiErrorInterface $error
		 * @param int|null          $status
		 * @param array             $headers
		 *
		 * @return Response
		 */
		public function createErrorResponse(ApiErrorInterface $error, $status = null, array $headers = []);

		/**
		 * Creates an error response using AccessDeniedError.
		 *
		 * @return Response
		 * @see AccessDeniedError
		 */
		public function createAccessDeniedResponse();

		/**
		 * Creates an error response using NotFoundError.
		 *
		 * @return Response
		 * @see NotFoundError
		 */
		public function createNotFoundResponse();
	}