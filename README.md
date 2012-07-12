# CodeIgniter Hook Class
This class gives CodeIgniter an implementation of the Observer pattern, with a little added PubSub flavor. CodeIngiter already has a basic system for this this, in the form of the core Hooks class. However, it does not expose the ability to add your own hooks nor is it namespaced. This library adds both of those abilities.

**Naming Conventions**

1. **Hook:** The 
2. **Topics:** The name of the hook fired, e.g. some.namespaced.topic

**Features**

1. Create hooks in `config/hooks.php` OR on the fly in code
2. Hooks are namedspaced, and so a hook call could fire multiple, parent hooks.
3. All native CodeIgniter usage.

##Listen for a hook call

**Method 1: In configuration file**

```php
<?php
	$default_mail_param = 'Unknown Email Info';
	$hook['notifications.email.sent'][] = array(
		'class'    => 'EmailLogger',
		'function' => 'LogSentEmail',
 		'filename' => 'EmailLogger.php',
 		'filepath' => 'hooks',
 		'params'   => $default_mail_param
	);
	
	$hook['notifications.email'][] = array(
		'class'    => 'EmailLogger',
		'function' => 'LogEmail',
 		'filename' => 'EmailLogger.php',
 		'filepath' => 'hooks',
 		'params'   => 'Some Email Activity Occured'
	);
```

**Method 2: In code**

Note that we can pass anything to the parameters - These will be used as defaults if no parameters are passed at the time of the hook being fired
```php
<?php	
	$this->hook->register('notifications.email.sent',  array(
		'class'    => 'EmailLogger',
		'function' => 'LogSentEmail',
 		'filename' => 'EmailLogger.php',
 		'filepath' => 'hooks',
 		'params'   => 'Email sent'
	));
	
	$this->hook->register('notifications.email',  array(
		'class'    => 'EmailLogger',
		'function' => 'LogEmail',
 		'filename' => 'EmailLogger.php',
 		'filepath' => 'hooks',
 		'params'   => 'Some Email Activity Occured'
	));
```

##Call a hook, so listening callbacks will fire
Note that we can pass anything to the parameters - in this case, we're passing an array with data on a sent email

```php
<?php
	class Email {
		public function __construct() {
			$this->CI =& get_instance();
		}
		
		public function sendEmail(to, $subject, $message) {
			if(mail($to, $subject, $message)) {
				$this->CI->hook->call('notifications.email.sent', array('to' => $to, 'from' => $from, 'message' => $message));
				return TRUE;
			}
			return FALSE;
		}
	}
```
	
## Create code for hooks:

`application/hooks/EmailLogger.php`

```php
<?php
	class EmailLogger {
		public function LogSentEmail($data) {
			log_message('debug', print_r($data, TRUE));
		}
	
		public function LogEmail($data)) {
			log_message('debug', print_r($data, TRUE));
		}
	}
```

## What's happening?
We call hooks once in our Email class, however BOTH registered hooks will be called. This is the power of namespaced hooks - The parent namespaced items get called as well. This allows a developer to listen in on any activity or specific activity of fired hooks.

Let's say we fire the hook:  `notifications.email.sent.complete`
This will perform the function callbacks on any hook listening for:

* notifications.email.sent.complete
* notifications.email.sent
* notifications.email
* notifications

Note that there won't necessarily be hooks listening to each of those topics. Hooks with no listeners are not fired. Also, a hook listening to `notifications.email` might not be expected the data it receives, if the data passed was for `notifications.email.sent.complete`, so code structed around namespaced callbacks needs to be created carefully. Sloppy or inexperienced programmers can make your life hell using the Observer model.


## License
MIT