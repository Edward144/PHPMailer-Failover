# PHPMailer Failover

A PHPMailer class that allows for the configuration of multiple redundant SMTP services. Each service is attempted in turn and if it fails then the next is used. If all services fail then the mail will be logged to a local file for debugging and so that the mail is not lost. 

---

### Installation

Create a composer.json file or amend your existing one. Add this Github repository and require the latest version.

    {
      "repositories": [
        {
          "type": "vcs",
          "url": "https://github.com/Edward144/PHPMailer-Failover.git"
        }
      ],
      "require": {
        "edward144/php-mailer-failover": "^1.0"
      },
      "minimum-stability": "stable"
    }

Run `composer install`

---

### Configuration

A number of constants must be defined for the class to operate. An example of these can be found within **tests/config.example.php**. These should be stored in a secure location as your SMTP credentials are required.

* `MAILER_FAILOVER_SMTP` - an array of SMTP services to use, these will be attempted in order.

    ```
    'service_name' => [
      'host'  => 'smtp.service.com',
      'port' => 587,
      'username' => 'username',
      'password' => 'apikey',
      'encryption' => 'tls' //If omitted then PHPMailer::ENCRYPTION_SMTPS will be used
    ],
    ...
    ```
* `MAILER_FAILOVER_DEBUG_LEVEL` - an integer debug level as defined by PHPMailer, default is 2
  * 0 - Debug off
  * 1 - Client to Server messages
  * 2 - As 1 plus Server to Client messages
  * 3 - As 2 plus connection status
  * 4 - All messages
* `MAILER_FAILOVER_DEBUG_LOCATION` - the absolute path to the location where debug logs and failed mails will be stored, include a trailing slash
* `MAILER_FAILOVER_FROM` - the default email address that mail will be sent from
* `MAILER_FAILOVER_FROM_FRIENDLY` - the default friendlt name that mail will be sent from

---

### Usage

Include the class, require **vendor/autoload.php** and include your defined constants.

You can then add utilize a variety of methods to configure your mail.

```

Use Edward144\PHPMailer-Failover\Mailer

require __DIR__ . '/path/to/vendor/autoload.php';
include __DIR__ . '/path/to/config.php';

$mail = new Mailer();

$mail->addTo('john.smith@example.com'); //Add a single address without a friendly name

$mail->addTo('john.smith@example.com', 'John Smith'); //Add a single address with a friendly name

$mail->addTo([
  'john.smith@example.com',
  'jane.doe@example.com'
]); //Add an array of addresses without friendly names

$mail->addTo([
  'John Smith' => 'john.smith@example.com',
  'Jane Doe' => 'jane.doe@example.com'
]); //Add an array of addresses with friendly names

$mail->addTo([
  'John Smith' => 'john.smith@example.com',
  'jane.doe@example.com'
]); //A mix of emails with and without friendlies can be supplied

$mail->addCc($address, $friendly); //Add a CC address, this can be used the same ways as addTo
$mail->addBcc($address, $friendly); //Add a BCC address, this can also be used the same ways as addTo

$mail->subject('This is my subject);
$mail->body('<p>This is my HTML message!</p>, true); //HTML body, a plaintext version is generated by default, pass a second false parameter to omit this
$mail->altBody('This is my plaintext message!'); //Plaintext body, this should be set before the HTML body, unless the automatic plaintext generation has been disabled.

$mail->from('another.from@example.com', 'Optional Friendly Name'); //Override the default from address and friendly name

$mail->addAttachment('absolute/path/to/attachment.txt'); //Add a single attachment

$mail->addAttachment('absolute/path/to/attachment.txt', 'custom-attachment.txt'); //Add a single attachment with a custom filename

$mail->addAttachment([
  'path/to/attachment-1.txt',
  'path/to/attachment-2.pdf'
]); //An array of attachments without custom filenames

$mail->addAttachment([
  'text-attachment.txt' => 'path/to/attachment-1.txt',
  'pdf-attachment.pdf' => 'path/to/attachment-2.pdf'
]); //An array of attachments with custom filenames

$mail->addAttachment([
  'text-attachment.txt' => 'path/to/attachment-1.txt',
  'path/to/attachment-2.pdf'
]); //A mix of attachments with and without custom filenames can be supplied

$mail->send(); //Send the mail
//true will be returned if a service succeeded
//false if all services failed but the message was logged locally
//An error message will be returned in other cases (an incorrect parameter was supplied, the log file failed to open)

```

---

### Credits

* [PHPMailer](https://packagist.org/packages/phpmailer/phpmailer)
* [Html2Text](https://packagist.org/packages/html2text/html2text)
