<?php
	namespace DaybreakStudios\DozeBundle;

	use DaybreakStudios\DozeBundle\DependencyInjection\DaybreakStudiosDozeExtension;
	use Symfony\Component\HttpKernel\Bundle\Bundle;

	class DaybreakStudiosDozeBundle extends Bundle {
		/**
		 * {@inheritdoc}
		 */
		public function getContainerExtension() {
			return new DaybreakStudiosDozeExtension();
		}
	}
