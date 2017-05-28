<?php
	namespace DaybreakStudios\DozeBundle\Error;

	use Symfony\Component\HttpFoundation\Response;

	class NotFoundError extends ApiError {
		public function __construct() {
			parent::__construct('not_found', 'Not Found', Response::HTTP_NOT_FOUND);
		}
	}