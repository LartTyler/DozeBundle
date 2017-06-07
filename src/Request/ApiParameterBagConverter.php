<?php
	namespace DaybreakStudios\DozeBundle\Request;

	use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
	use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
	use Symfony\Component\HttpFoundation\Request;

	class ApiParameterBagConverter implements ParamConverterInterface {
		/**
		 * {@inheritdoc}
		 */
		public function supports(ParamConverter $configuration) {
			$class = $configuration->getClass();

			return $class === ApiParameterBag::class || is_subclass_of($class, ApiParameterBag::class);
		}

		/**
		 * {@inheritdoc}
		 */
		public function apply(Request $request, ParamConverter $configuration) {
			$bag = call_user_func([$configuration->getClass(), 'fromRequest'], $request);

			$request->attributes->set($configuration->getName(), $bag);

			return true;
		}
	}