<?php

namespace App;

use Mailgun\Mailgun;

class Mail  
{
    // Send a message
    public static function send($to, $subject, $text, $html)
    {
        # Instantiate the client.
        $mgClient = Mailgun::create(Config::MAILGUN_API_KEY, 'https://api.mailgun.net');  // US region the base URL. EU region the base URL: https://api.eu.mailgun.net/
        $domain = Config::MAILGUN_DOMAIN;
        $params = array(
            'from'    => 'Excited User <admin-sender@example.com>',
            'to'      => $to,
            'subject' => $subject,
            'text'    => $text,
            'html'    => $html
        );

        # Make the call to the client.
        $mgClient->messages()->send($domain, $params);
    }
}
