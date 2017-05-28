<?php
	namespace DaybreakStudios\DozeBundle\Utility;

	/**
	 * Contains methods that are useful for working with Doctrine's collections library.
	 *
	 * @package AppBundle\Utility
	 */
	final class CollectionUtil {
		/**
		 * @param mixed $item
		 *
		 * @return bool
		 */
		public static function isIterable($item) {
			return is_array($item) || $item instanceof \Traversable;
		}
	}