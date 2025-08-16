# Maileroo Laravel Transport

This is a Laravel transport for the Maileroo email service, allowing you to send emails using the <a href="https://maileroo.com/docs/">Maileroo API</a>.

## Requirements

- PHP 8.0 or higher
- cURL Extension (`ext-curl`)
- JSON Extension (`ext-json`)

## Installation

```bash
composer require maileroo/maileroo-laravel-transport
```

## Configuration

This package comes with a Laravel service provider that will automatically register the transport. All you've to do is update your `.env` file with the Maileroo API key and set the mail driver to `maileroo`.

```dotenv
MAIL_MAILER=maileroo
MAILEROO_API_KEY=your_maileroo_api_key
MAIL_FROM_ADDRESS="[YOUR_MAILEROO_FROM_ADDRESS]"
MAIL_FROM_NAME="[YOUR_MAILEROO_FROM_NAME]"
```

That is it! The transport will automatically use the `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` settings from your `.env` file to set the `From` header in your emails. No extra configuration is needed.

## Usage

```php
Mail::send([], [], function ($m) {
    $m->to('test@example.com', 'Test User')
      ->subject('Hello from Laravel via Maileroo (Subject)')
      ->text('Hello from Laravel via Maileroo (Plain Text)')
      ->html('<h1>Hello from Laravel via <b>Maileroo</b></h1>');
});
```

## Facade

For advanced usage, you can use the `Maileroo` facade to send basic, templated or even bulk emails. 

```php
use Maileroo\LaravelTransport\Facades\Maileroo;

// Create a client (or rely on the transport's internal one)

$client = Maileroo::client(env('MAILEROO_API_KEY'), (int) env('MAILEROO_TIMEOUT', 30));

// Build addresses

$from = Maileroo::emailAddress('no-reply@yourdomain.com', 'Your App');
$to   = Maileroo::emailAddress('user@example.com', 'User');

// Attachments

$att1 = Maileroo::attachmentFromFile(storage_path('app/invoice.pdf'));
$att2 = Maileroo::attachmentFromContent('report.csv', "id,name\n1,Demo\n", 'text/csv', false);

// Example: send via SDK (method names depend on the Maileroo PHP SDK)

$referenceId = $client->sendBasicEmail([
    'from'   => $from,
    'to'     => [$to],
    'subject'=> 'Welcome via SDK',
    'html'   => '<strong>Hello SDK!</strong>',
    'plain'  => "Hello SDK!",
    'attachments' => [$att1, $att2],
    'tracking' => true,
    'tags' => ['source' => 'laravel'],
]);

logger()->info('Maileroo reference id: '.$referenceId);
```

Available helpers on the facade:

1. `Maileroo::client(string $apiKey, int $timeout = 30): MailerooClient`
2. `Maileroo::emailAddress(string $email, ?string $name = null): EmailAddress`
3. `Maileroo::attachmentFromFile(string $path, ?string $contentType = null, bool $inline = false): Attachment`
4. `Maileroo::attachmentFromContent(string $name, string $content, ?string $contentType = null, bool $inline = false): Attachment`
5. `Maileroo::attachmentFromStream(string $stream, ?string $name = null, ?string $contentType = null, bool $inline = false): Attachment`

To send templated, bulk or scheduled emails, refer to the <a href="https://github.com/maileroo/maileroo-php-sdk">Maileroo PHP SDK</a> documentation.

## Documentation

For detailed API documentation, including all available endpoints, parameters, and response formats, please refer to the [Maileroo API Documentation](https://maileroo.com/docs).

## License

This SDK is released under the MIT License.

## Support

Please visit our [support page](https://maileroo.com/contact-form) for any issues or questions regarding Maileroo. If you find any bugs or have feature requests, feel free to open an issue on our GitHub repository.