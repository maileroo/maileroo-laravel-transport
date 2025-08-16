<?php

namespace Maileroo\LaravelTransport\Facades;

use Illuminate\Support\Facades\Facade;
use Maileroo\MailerooClient;
use Maileroo\EmailAddress;
use Maileroo\Attachment;

class Maileroo extends Facade {

    protected static function getFacadeAccessor() {
        return MailerooClient::class;
    }

    public static function client(string $apiKey, int $timeout = 30): MailerooClient {
        return new MailerooClient($apiKey, $timeout);
    }

    public static function emailAddress(string $email, ?string $name = null): EmailAddress {
        return new EmailAddress($email, $name);
    }

    public static function attachmentFromContent(string $path, string $name, ?string $contentType = null, bool $inline = false): Attachment {
        return Attachment::fromContent($path, $name, $contentType, $inline, false);
    }

    public static function attachmentFromFile(string $path, ?string $contentType = null, bool $inline = false): Attachment {
        return Attachment::fromFile($path, $contentType, $inline);
    }

    public static function attachmentFromStream(string $path, ?string $name = null, ?string $contentType = null, bool $inline = false): Attachment {
        return Attachment::fromStream($path, $name, $contentType, $inline);
    }

}
