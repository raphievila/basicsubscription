# BasicMailgun
This is an **extremely basic and unofficial** Mailgun component for subscription management. Specifically coded to create ajax base subscription method on a website. Do not include mail management capabilities.

Only include tools to subscribe, unsubscribe, verify structure or check status from a given email in an specified mailing list. As I said before, **basic** user based mailing list management to be installed on a website. Build for those whose code conflicts with Composer autoload and are not able to use the excellent Mailguns's own PHP SDK.

## Setup
Install using terminal or powershell `$> composer require raphievila/basicsubscription` when finished, create the setting file copying `basicsubscription/src/config/mg-example.ini` and rename it `mg.ini`.

On the newly created `mg.ini` file, setup your values specified on your Mailgun account. If you are in a hosting that do not allows you file system access to the root of your hosting, you need to take all precautions available to secure this file, since holds important and sensitive information.

## NO COMPOSER SET UP
The main reason I created this code was to implement Mailgun to a huge website I manage, built originally with PHP 3, now PHP 5.7, but the Composer autoload comflicted with an autoload I created years ago, before even `namespace` was implemented on php objects, object oriented language was a beta option for php back then. For this reason I was unable to use the Mailgun SDK.

If you are an old timer like me, you probably have a bunch of applications with your own autoload system that might conflict with Composer autoload. In that case, copy the class to your class folder, and if by the time you created your autoload `namespace` was not available, remove the namespace on `line #2`, this if you still you have conflicts. I suggest, as I'm doing, restructure your code to fit Composer autoload, which is a great includes manager or to any other code manager you prefer.

After saving your class, then save `basicsubscription/src/config` directory on the root of your PHP includes directory or on same directory from where you load your classes, or change `basicsubscription/raphievila/BasicSubsbription::__construct` to fit your needs and specify other location.

## SUPPORT
This is an as-is Class, but if you have any issue or request you can do so by contacting me through [Designer's Gate](http://designersgate.com/) or [Revolution Visual Arts](https://revolutionvisualarts.com) website.

If you need to use Mailgun for sending emails use SwiftMailer instead.
