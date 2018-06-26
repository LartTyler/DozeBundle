# Installation
```bash
$ composer require dbstudios/doze-bundle
```

# Configuration
Add the following to `app/AppKernel.php`:

```php
<?php
    // app/AppKernel.php
    
    // ...
    class AppKernel extends Kernel {
        public function registerBundles() {
            // ...
            
            $bundles = [
            	// ...
            	new DaybreakStudios\DozeBundle\DaybreakStudiosDozeBundle(),
            ];
            
            // ...
    
            return $bundles;
        }
    
        // ...
    }
```

You will also need to add a service definition for the responder in your bundle's `services.yml`:

```yaml
services:
    app.doze.responder:
        class: DaybreakStudios\DozeBundle\ResponderService
        arguments:
            - '@app.serializer'
            - '@request_stack'

    app.serializer:
        class: Symfony\Component\Serializer\Serializer
        arguments:
            -
                - '@app.serializer.normalizer.datetime'
                - '@app.serializer.normalizer.collection'
                - '@app.serializer.normalizer.entity'
                - '@app.serializer.normalizer.object'
            -
                - '@app.serializer.encoder.json'

    app.serializer.normalizer.datetime:
        class: Symfony\Component\Serializer\Normalizer\DateTimeNormalizer

    # This normalizer is only necessary if you plan on normalizing
    # Doctrine collections to paged collections.
    # @see https://github.com/LartTyler/Doze#paged-collections
    app.serializer.normalizer.collection:
        class: DaybreakStudios\Doze\Serializer\PagedCollectionNormalizer
        arguments:
            - 25

    # This normalizer is only necessary if you plan on normalizing database entities.
    # @see https://github.com/LartTyler/Doze#serializing-database-objects
    app.serializer.normalizer.entity:
        class: DaybreakStudios\Doze\Serializer\EntityNormalizer

    app.serializer.normalizer.object:
        class: Symfony\Component\Serializer\Normalizer\ObjectNormalizer

    app.serializer.encoder.json:
        class: Symfony\Component\Serializer\Encoder\JsonEncoder
```

Optionally, you may also add any or all of the following parameters to your application if you need to
modify the default behavior of Doze.

```yaml
// app/config/parameters.yml

parameters:
    # Whether or not to use the built-in CORS listener. If disabled, Doze will not be able to respond to
    # CORS requests unless you add the necessary CORS headers yourself.
    dbstudios.cors_listener.enabled: true
    
    # An array of origins that are allowed to send CORS requests.
    # If the only value in this array is an asterisk, all origins will be allowed.
    dbstudios.cors_listener.allowed_origins:
        - '*'
        
    # An array of headers that are allowed in CORS requests.
    # An asterisk denotes all headers are allowed.
    dbstudios.cors_listener.allowed_headers:
        - '*'
        
    # An array of methods that are allowed in CORS requests.
    # An asterisk denotes all methods are allowed.
    dbstudios.cors_listener.allowed_methods:
        - '*'

    # A boolean indicating whether or not credentials are allowed in CORS requests.
    dbstudios.cors_listener.allow_credentials: true
```

All of the parameters listed above show their default values. You will only need to include a parameter if you need
to change it from what's shown above.

# Usage
In the example below, the responder will be used to serialize data coming from Doctrine in a controller action. However,
Doze can be used to serialize any kind of data, and may be used anywhere that you have access to the service container,
or via dependency injection.

```php
<?php
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use DaybreakStudios\DozeBundle\ResponderService;

    class MyController extends Controller {
        public function indexAction(ResponderService $responder, $id) {
            $entity = $this->getDoctrine()->getRepository('AppBundle:MyEntity')->find($id);

            if ($entity === null)
                return $responder->createNotFoundResponse();

            return $responser->createResponse($entity);
        }
    }
```
