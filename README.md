# Rollbar for Nette

## Instalation

```
composer require webchemistry/rollbar
```

Neon:
```yaml
extensions:
	rollbar: WebChemistry\RollBar\DI\Extension
	
rollbar:
	accessToken: xxx
```

## First use

```php
class BasePresenter extends Presenter {
	
	/** @var WebChemistry\RollBar\RollBar @inject */
	public $rollbar;

	public function startup() {
		parent::startup();
		
		$this->rollbar->sendTest(); // After first use remove
	}
}
```

## Config

```yaml
rollback:
	accessToken: xxx
	logging: no
	enable: %productionMode%
	config: # For Rollback::init
		included_errno: -1
```
