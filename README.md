
# SabaNovin PHP

## Installation

Use in these ways :

```php
composer require sabanovin/php
```

Usage
-----

Send Simple SMS by SabaNovin API:

```php
require __DIR__ . '/vendor/autoload.php';

try{
	$api = new \SabaNovin\SabaNovinApi( "API-Key" );
	$gateway = "100020500";
	$text = "متن تست";
	$to = array("09370000000", "09120000000");
	$result = $api->Send($gateway, $to, $text);
	if($result->entries){
		foreach($result->entries as $entry) {
			echo "reference_id = $entry->reference_id";
			echo "status   = $entry->status";
			echo "mobile   = $entry->mobile";
			echo "datetime = $entry->datetime";
		}		
	}
}
catch(\SabaNovin\Exceptions\ApiException $e){
	echo $e->errorMessage();
}
