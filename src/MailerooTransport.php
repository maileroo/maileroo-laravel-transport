<?php

namespace Maileroo\LaravelTransport;

use Maileroo\EmailAddress;
use Maileroo\MailerooClient;
use Maileroo\Attachment;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Address;

class MailerooTransport extends AbstractTransport {

    public function __construct(private MailerooClient $client, private array $options = []) {
        parent::__construct();
    }

    public function __toString(): string {
        return 'maileroo';
    }

    protected function doSend(SentMessage $sentMessage): void {

        $message = $sentMessage->getOriginalMessage();

        if (!$message instanceof Email) {
            throw new \InvalidArgumentException('MailerooTransport only supports Symfony\\Mime\\Email messages.');
        }

        $envelope = $sentMessage->getEnvelope();
        $payload = [];

        $from = $envelope->getSender() ? : ($message->getFrom()[0] ?? null);

        if (!$from) {
            throw new \InvalidArgumentException('From address is required.');
        }

        try {

            $payload['from'] = $this->mapAddress($from);

            $payload['to'] = $this->mapAddresses($message->getTo());
            $payload['cc'] = $this->mapAddresses($message->getCc());
            $payload['bcc'] = $this->mapAddresses($message->getBcc());
            $payload['reply_to'] = $this->mapAddresses($message->getReplyTo());

            $payload['subject'] = (string)$message->getSubject();

            if ($message->getHtmlBody()) {
                $payload['html'] = $message->getHtmlBody();
            }

            if ($message->getTextBody()) {
                $payload['plain'] = $message->getTextBody();
            }

            $attachments = [];

            foreach ($message->getAttachments() as $part) {

                if ($part instanceof DataPart) {

                    $name = $part->getFilename() ?? 'attachment';
                    $contentType = $part->getMediaType() . '/' . $part->getMediaSubtype();
                    $content = $part->getBody();
                    $inline = $part->getDisposition() === 'inline';

                    $attachments[] = Attachment::fromContent($name, $content, $contentType, $inline, false);

                }

            }

            if ($attachments) {
                $payload['attachments'] = $attachments;
            }

            $payload['headers'] = [];

            foreach ($message->getHeaders()->all() as $header) {

                $name = strtolower($header->getName());
                $value = $header->getBodyAsString();

                if ($name === 'x-maileroo-track') {

                    $enabled = false;

                    if ($value === 'true' || $value === '1' || $value === 'yes' || $value === true) {
                        $enabled = true;
                    }

                    $payload['tracking'] = $enabled;

                } elseif ($name === 'x-maileroo-tags') {

                    $tags = json_decode($value, true);

                    if (is_array($tags)) {
                        $payload['tags'] = $tags;
                    }

                } else {

                    $reservedHeadersMap = [
                        'mime-version',
                        'content-type',
                        'content-transfer-encoding',
                        'content-disposition',
                        'content-id',
                        'content-description',
                        'message-id',
                        'date',
                        'from',
                        'to',
                        'cc',
                        'bcc',
                        'subject',
                        'reply-to',
                        'return-path',
                        'received',
                        'delivered-to',
                        'authentication-results',
                        'dkim-signature',
                        'x-maileroo-ref-id',
                        'x-maileroo-track',
                    ];

                    if (!in_array($name, $reservedHeadersMap, true)) {
                        $payload['headers'][$name] = $value;
                    }

                }

            }

            if (isset($this->options['tracking'])) {

                $payload['tracking'] = false;

                if ($this->options['tracking'] === true || $this->options['tracking'] === 'true' || $this->options['tracking'] === '1' || $this->options['tracking'] === 'yes') {
                    $payload['tracking'] = true;
                }

            }

            if (!empty($this->options['tags']) && is_array($this->options['tags'])) {
                $payload['tags'] = ($payload['tags'] ?? []) + $this->options['tags'];
            }

            $referenceId = $this->client->sendBasicEmail($payload);

            $sentMessage->setMessageId($referenceId);

            $message->getHeaders()->addTextHeader('X-Maileroo-Ref-Id', $referenceId);

        } catch (\Exception $e) {

            throw new TransportException('Request to Maileroo API failed: ' . $e->getMessage(), 0, $e);

        }

    }

    private function mapAddress(Address $address): EmailAddress {

        $email_address = new EmailAddress($address->getAddress(), $address->getName() ? : null);

        return $email_address;

    }

    private function mapAddresses(?array $addresses): array {

        if (!$addresses) {
            return [];
        }

        return array_map(fn(Address $a) => $this->mapAddress($a), $addresses);

    }

}
