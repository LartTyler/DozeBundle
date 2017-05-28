<?php
	namespace DaybreakStudios\DozeBundle\Error;

	interface ApiErrorInterface {
		/**
		 * @return string
		 */
		public function getCode();

		/**
		 * @return string
		 */
		public function getMessage();

		/**
		 * @return int|null
		 */
		public function getHttpStatus();
	}