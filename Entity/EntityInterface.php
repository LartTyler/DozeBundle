<?php
	namespace DaybreakStudios\DozeBundle\Entity;

	/**
	 * An interface to identify database entities. For use with the EntityNormalizer.
	 *
	 * @package DaybreakStudios\DozeBundle\Entity
	 */
	interface EntityInterface {
		/**
		 * @return mixed|null
		 */
		public function getId();
	}