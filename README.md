# Hello
Built off of [defaultnamehere](https://github.com/defaultnamehere/zzzzz)'s source code. He did a great job on the Python version of this project and I just imported it to PHP.
I mostly did this because his version would crash about every 8 hours for what ever reason. I've been running this code for about 8 days stright now (as of 2016-11-21) without any show stopping issues.

# Known Issues
* You will get an SSL handshake error every now and again. It seems to be because my connection is a little spotty, but you might see that as well.
* When your connection resets because of the above, it will fail any outstanding connections. So you might see bytes being failed to write to the socket in the log console output.

# What do I need?
You need OpenSSL for this to work. As it's required for the secure connection to the facebook servers.
The socket connections are pretty easy, you don't need cURL installed, I use the simple [file_get_contents()](http://php.net/manual/en/function.file-get-contents.php) function call with [stream_context_create()](http://php.net/manual/en/function.stream-context-create.php).

# System Requirements
* [PHP 7.0.0](http://php.net/downloads.php) or better, 7.1.0 recommended.
* [openssl](http://php.net/manual/en/book.openssl.php) extension must be enabled.
* [fopen wrappers](http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen) must be enabled.
* [https:// wrapper](http://php.net/manual/en/wrappers.http.php) must be allowed.

# How to setup
* The [instructions are the same as defaultnamehere's implamentation](https://github.com/defaultnamehere/zzzzz/blob/master/README.md).
* You need to make a SECRETS.txt file.
* In that file you need one line that starts with `uid=` and you'll need to get your facebook uid.
* You'll also need to add `cookie=` and you'll need to copy your facebook cookie.
* Finally add `client_id=` and the client ID value that you find in the headers to / from facebook's server.

# How to use
    php Stalky.php > output.log

It will make the `log`, `php` and `raw` directories all by it's self if they are not already made. Just make sure that you can write to the directory that you clone this into.

That's pretty much it.

# Web Server
    php -S 0.0.0.0:8080

The above command will start the PHP web server on port `8080` and attach it's self to all interfacces available to it. From there in your browser on the same machine simply type `[localhost:8080](http://localhost:8080/)` or any [FQDN](https://en.wikipedia.org/wiki/Fully_qualified_domain_name) attached to that computer with the port `8080` and it will load `index.php` automaticlly for you. This interface allows you to view the log files in aggregate. Simply select the time you wish to view from and to, and select at least one uid from the list. From there you can search, view raw files, or view the print_f of the json file all from the browser. This is useful for debugging. I set the timezone in the file to my own `America/New_York`. You might want to change that to something else. Keep in mind that the log files are created with the file name in the unix epoch timestamp, so they are always in UTC. You have to convert them to your timezone for this to make more sense.

# NAMES.txt
If you want the datalist with a label containing the persons name and not just their uid, make a NAMES.txt file in the following format.

    uid=Full Name

You can fill in as many lines as you wish.