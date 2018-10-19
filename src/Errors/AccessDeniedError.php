<?php
	namespace DaybreakStudios\DozeBundle\Errors;

	use DaybreakStudios\Doze\Errors\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class AccessDeniedError extends ApiError {
		/**
		 * AccessDeniedError constructor.
		 *
		 * @param string|null $message
		 */
		public function __construct(string $message = null) {
			parent::__construct('access_denied', $message ?? 'Access denied', Response::HTTP_FORBIDDEN);
		}
	}