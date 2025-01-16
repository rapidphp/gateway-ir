# Gateway IR

This package allows you to easily and powerfully use Iranian gateways in Laravel.

## Installation

```shell
composer require rapid/gateway-ir
```

_Optional to publish assets:_

```shell
php artisan vendor:publish --provider="Rapid\GatewayIR\GatewayIRServiceProvider"
```

## Portals

### Fixed Portals

This section defines the available payment gateway portals that can be used for
processing payments. Each portal should be configured with the necessary
settings and credentials.

```php

'portals' => [

    'zarinpal' => [
        'driver' => \Rapid\GatewayIR\Portals\ZarinPal::class,
        'key' => env('GATEWAY_ZARINPAL_KEY', '9f82b83f-7893-4b2e-93b8-9a096ceb3428'),
        'sandbox' => env('GATEWAY_ZARINPAL_SANDBOX', false),
    ],

    'idpay' => [
        'driver' => \Rapid\GatewayIR\Portals\IDPay::class,
        'key' => env('GATEWAY_IDPAY_KEY', ''),
        'sandbox' => env('GATEWAY_IDPAY_SANDBOX', false),
    ],

    'internal_sandbox' => [
        'driver' => \Rapid\GatewayIR\Portals\InternalSandbox::class,
    ],

],

```

You can now define default and secondary values using the portals you have created:

```php

'default' => env('GATEWAY_DEFAULT', 'zarinpal'),

'secondary' => env('GATEWAY_SECONDARY', 'idpay'),

```

And you can use the `payment` helper to access the portal instance:

```php
$default = payment();
$secondary = Payment::secondary();
$idpay = payment('idpay');
```

### Dynamic Portals

You can also define dynamic portals using the `define` method in service providers:

```php
Payment::define('my_portal', function () {
    return new ZarinPal('key');
});
```

## Payment

### New Transaction

```php
return payment()
    ->request(2000, 'Description', MyHandler::class, ['meta' => null])
    ->redirect();
```

```php
class MyHandler extends PaymentHandler
{
    public function setup(HandleSetup $setup): void
    {
        $setup
            ->success(function (PaymentVerifyResult $result) {
                return "Payment success!";
            });
    }
}
```

Or pass an instance of handler:

```php
return payment()
    ->request(2000, 'Description', new MyHandler(), ['meta' => null])
    ->redirect();
```

### Payment Handler

Handlers remember their variables and respond to payments.
Therefore, you can store payment information in these classes that you define
yourself and then implement payment logic.

```php
class BuyProductHandler extends PaymentHandler
{
    public function __construct(
        protected User $user,
        protected Product $product,
    ) {}

    public function setup(HandleSetup $setup): void
    {
        $setup
            ->validate(function (PaymentPrepare $prepare) {
                return !$this->user->boughts()->where('product_id', $this->product->id)->exists() &&
                    $this->product->price == $prepare->amount;
            })
            ->success(function (PaymentVerifyResult $result) {
                $this->user->boughts()->create(['product_id' => $this->product->id]);
                
                event(new ProductBought($this->user, $this->product));
                
                return view('payment.success', ['to' => route('product.show', $this->product)]);
            })
            ->cancel(function () {
                return view('payment.cancel');
            })
            ->fail(function () {
                return view('payment.fail');
            });
    }
}
```

Usage:

```php
public function buy(Product $product)
{
    return payment()
        ->request($product->price, 'Buy ' . $product->name, new BuyProductHandler(auth()->user(), $product))
        ->redirect();
}
```
