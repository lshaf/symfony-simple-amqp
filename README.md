# Symfony Simple AMQP

I make this based on my needs

### Instalation

add `new lshaf\amqp\AMQPBundle()` to `AppKernel.php`

### Configuration
```yaml
lshaf_amqp:
	connection:
		host: 127.0.0.1
		user: guest
		password: guest
		vhost: /
		port: 5672
	options:
		queueName: up_to_you
		autoDelete: false
		passive: false
		durable: true
		exclusive: false
		nowait: false
		meta:
			x-limit-priority: 10
			...
		# config below being used by amqp:process
		namespace: AppBundle\Jobs
		exchanes:
			master:
				- product
				- data
```

### Run process

Run `bin/console amqp:process`  
It will queue automatically and exchanges you've set on config

### Test

Run `bin/console amqp:listen`  
It will print anything you've sent to listener
