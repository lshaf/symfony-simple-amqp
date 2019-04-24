# Symfony Simple AMQP
===

I make this based on my needs

### Instalation

add `new lshaf\amqp\AMQPBundle()` to `AppKernel.php`

### Configuration
```
[
	'listener' => [
		'namespace' => "AppBundle\\jobs\\"
	],
	'options' => [
		'queueName' => null,
		'autoDelete' => false,
		'passive' => false,
		'durable' => true,
		'exclusive' => false,
		'nowait' => false,
		'meta' => []
	],
	'connection' => [
		'host' => '127.0.0.1',
		'user' => 'guest',
		'password' => 'guest',
		'vhost' => '/',
		'port' => 5672
	],
]
```
