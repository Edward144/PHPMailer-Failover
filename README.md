# PHPMailer Failover

### How to install

Require the composer packages.

    composer require PHPMailer/PHPMailer
    composer require html2text/html2text

Create a copy of the **config.example.php** file, called **config.php**. Replace the various SMTP details with those required by your services. 

Also set your default mail from address, and friendly name. These can be overridden when calling the **sendemail()** function.

Finally set the directory where debug logs will be stored. This includes any mails that fail to send via both SMTP services. 

### Usage

Simply require both the **config.php** and **mailer.php** files and then call the **sendmail()** function.

The function accepts the following parameters:

 * **$to** - an array of email addresses, this can be a mixed array containing email addresses or a key pair of email address and friendly name.
 * **$subject** - the subject line of the email address.
 * **$body** - the HTML body of the email. A plaintext version alternative will be created automatically.
 * **$attachments** - an optional array of attachments, this can be a mixed array containing absolute paths to files, or a key pair of the absolute path and a friendly file name.
 * **$from** - an optional from email address to use, the default from address defined in **config.php** will be used if this is ommited.
 * **$fromFriendly** - an optional from friendly name to use, the default from friendly name defined in **config.php** will be used if this is ommited.

You will most likely want to configure a cronjob to delete older log files on a regular basis to prevent bloat.

### Example

See **index.php** for an example showing how different variations of to addresses and attachments can be included.    

### Credits

 * [PHPMailer](https://github.com/PHPMailer/PHPMailer)
 * [HTML2Text](https://packagist.org/packages/html2text/html2text)