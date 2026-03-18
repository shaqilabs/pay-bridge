# Pay Bridge

Pay Bridge is a PHP library that provides a consistent way to integrate multiple payment gateways (Pakistan-focused). Each gateway has its own `*Client` (configuration + HTTP) and `*API` (operations) class.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Examples](#examples)
- [Gateways](#gateways)
- [Docs](#docs)
- [Configuration](#configuration)
- [Error Handling](#error-handling)
- [Security Notes](#security-notes)
- [License](#license)

## Requirements

- PHP `^8.0`
- Extensions: `ext-curl`, `ext-json`, `ext-openssl`

## Installation

To install Pay Bridge, you can use [Composer](https://getcomposer.org/). Run the following command:

```bash
composer require shaqi-labs/pay-bridge
```

## Quick Start

Every gateway follows the same pattern:

1. Create a `*Client` with your credentials/config
2. Create a `*API` using that client
3. Call the API methods

Example (AbhiPay):

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use ShaqiLabs\AbhiPay\AbhiPayAPI;
use ShaqiLabs\AbhiPay\AbhiPayClient;

$client = new AbhiPayClient([
    'merchant_id' => 'YOUR_MERCHANT_ID',
    'secret_key' => 'YOUR_SECRET_KEY',
    'return_url' => 'https://example.com/return',

    // Optional network hardening (defaults are already set)
    'timeout' => 30,
    'connect_timeout' => 10,
]);

$api = new AbhiPayAPI($client);

$result = $api->createCheckoutLink([
    'amount' => 25.30,
    'description' => 'Test order',
], 'url'); // url / response / redirect

var_dump($result);
```

## Examples

Examples live in `examples/` and use placeholder credentials (replace with your sandbox/production values).

```bash
composer install
php examples/AbhiPay.php
```

## Gateways

| Provider | Usage Guide | Type | API Doc |
| -------- | ------- | ------- | ------- |
|Safe Pay|[Safe Pay Usage Guide](src/SafePay/Usage%20Guide%20SafePay.md)| Hosted |[Safe Pay API Docs](https://github.com/getsafepay/safepay-php)|
|Safe Pay Embedded|[Safe Pay Embedded Usage Guide](src/SafePayEmbedded/Usage%20Guide%20SafePayEmbedded.md)| Embedded |[Safe Pay Embedded API Docs](https://github.com/getsafepay/sfpy-php)|
|UBL|[UBL Usage Guide](src/UBL/Usage%20Guide%20UBL.md)| Hosted| [UBL API Docs](docs/UBL/Api%20Docs%20UBL.pdf)|
|PayFast|[PayFast Usage Guide](src/PayFast/Usage%20Guide%20PayFast.md)| Hosted| [PayFast API Reference](https://gopayfast.com/qa/docs/) |
|Alfalah IPG|[Alfalah IPG Usage Guide](src/AlfalahIPG/Usage%20Guide%20AlfalahIPG.md)| Hosted|[Alfalah IPG API Docs](https://test-bankalfalah.gateway.mastercard.com/api/documentation/integrationGuidelines/index.html)|
|Alfalah APG|[Alfalah APG Usage Guide](src/AlfalahAPG/Usage%20Guide%20AlfalahAPG.md)| Hosted| [Alfalah APG API Docs](docs/AlfalahAPG/API%20Docs%20Alfalah%20APG.pdf)|
|JazzCash|[JazzCash Usage Guide](src/JazzCash/Usage%20Guide%20JazzCash.md)| Both | [JazzCash API Docs](docs/JazzCash/API%20Docs%20JazzCash.pdf)|
|EasyPaisa|[EasyPaisa Usage Guide](src/EasyPaisa/Usage%20Guide%20EasyPaisa.md)| Both | [EasyPaisa Merchant Portal](https://easypay.easypaisa.com.pk/easypay-merchant/faces/pg/site/Login.jsf) |
|AbhiPay|[AbhiPay Usage Guide](src/AbhiPay/Usage%20Guide%20AbhiPay.md)| Hosted | [AbhiPay API Docs](https://docs.abhipay.com.pk/)|
|BaadMay|[BaadMay Usage Guide](src/BaadMay/Usage%20Guide%20BaadMay.md)| Hosted | [BaadMay API Docs](docs/BaadMay/API%20Docs%20BaadMay.pdf)|

## Docs

- Usage guides (per gateway) are in `src/<Gateway>/Usage Guide <Gateway>.md` and linked in the table above.
- PDF/API documentation shipped in this repo (see `docs/`):
  - Alfalah APG: [API Docs Alfalah APG (PDF)](docs/AlfalahAPG/API%20Docs%20Alfalah%20APG.pdf)
  - BaadMay: [API Docs BaadMay (PDF)](docs/BaadMay/API%20Docs%20BaadMay.pdf)
  - JazzCash: [API Docs JazzCash (PDF)](docs/JazzCash/API%20Docs%20JazzCash.pdf)
  - UBL: [Api Docs UBL (PDF)](docs/UBL/Api%20Docs%20UBL.pdf)
- Internal notes (this repo):
  - PayFast: [Notes](docs/PayFast/README.md)
  - EasyPaisa: [Notes](docs/EasyPaisa/README.md)

## Configuration

- Most clients accept:
  - `environment` (usually `sandbox` / `production`, where supported)
  - `timeout` (seconds) and `connect_timeout` (seconds)
- Where supported, prefer returning data/URL and handle redirects in your application:
  - AbhiPay: `createCheckoutLink($order, 'url' | 'response' | 'redirect')`
  - BaadMay: `createCheckoutLink($order, 'url' | 'response' | 'redirect')`

## Error Handling

- Each gateway throws its own exception class (e.g. `ShaqiLabs\AbhiPay\AbhiPayException`).
- Network layer throws on:
  - cURL errors
  - HTTP status `>= 400`
  - Unparsable JSON responses (where JSON is expected)

## Security Notes

- Never commit real merchant credentials / API keys into the repository.
- Use environment variables or a secrets manager in production.
- Avoid automatic redirects inside libraries when building APIs/CLI apps; return URLs/data and redirect at the application layer.

## License

[MIT](https://choosealicense.com/licenses/mit/)
