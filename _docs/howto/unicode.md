Working with Unicode Text
=========================

PHP5 doesn't natively support unicode text (this is something we're all looking forward to, eventually!), and some care needs to be taken if you want to support iñtërnâtiônàlizætiøn characters. This library assumes you are working in UTF8, a multi-byte text encoding that can safely cope with almost any character.

Why Do I Need to be Careful?
----------------------------

UTF8 is a *multi-byte* character encoding, this means that a single character may be stored as more than a single byte. This can cause problems if you work with the default PHP string functions since they count **bytes** rather than characters and won't always give you the correct result when manipulating strings.

    <?php
    // strlen counts BYTES
    echo strlen('iñtërnâtiônàlizætiøn'); // 27

    // mb_strlen counts CHARACTERS
    mb_internal_encoding('UTF-8');          // setup mb_string extension
    echo mb_strlen('iñtërnâtiônàlizætiøn'); // 20
    ?>

The strlen example above demonstrates the problem but is pretty trivial, more serious string corruption can occur when chopping up strings (e.g. substr) where if a multibyte UTF8 character is chopped into two pieces the result is a severly corrupted string.

Manipulating UTF8 Strings
-------------------------

### mb_string Extension

To safely work with UTF8 strings, the library requires the PHP mb_string extension, and in addition adds a number of extra 'mb_'-prefixed functions that can be found in unicode.php in the library root dir. These are loaded when the library is bootstrapped, and should be used when working with strings.

The mb_string extension can be configured within php.ini and the recommended settings for maximum compatibility with this library are:

    mbstring.language = Neutral
    mbstring.internal_encoding = UTF-8
    ; leave input/output exactly as it is produced
    mbstring.http_input = pass
    mbstring.http_output = pass
    mbstring.encoding_translation = Off
    mbstring.detect_order = auto
    mbstring.substitute_character = none
    ; overload none of the operators (screws up binary strings)
    mbstring.func_overload = 0

### Regular Expressions

When using regular expressions with UTF8 strings you need to let the regex library know you want to use unicode matching (the 'u' switch for PCRE), and remember if you are trying to match "letters" they are not just A-Z!

    <?php
    $regex = '/^\p{L}+$/u';
    if (!preg_match($regex,$value)) {
        throw new UnexpectedValueException("$value must be letters only");
    }
    ?>

There's some [useful info on unicode regular expressions on regular-expressions.info](http://www.regular-expressions.info/unicode.html).

Working with UTF8 in a HTTP Context
-----------------------------------

Since we are working with UTF8 strings, the easiest way to support their display over HTTP is by telling the browser that we will be sending UTF8:

    <?php
    header('Content-type: text/html; charset=utf-8');
    ?>

... and making sure the meta-tag for charset is consistent with this header:

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

The browser will now treat the incoming text as UTF8, which is consistent with the internal encoding we are using throughout our code. We just need to make sure when we escape our strings for HTML we use UTF8 encoding too:

    <?php
    echo htmlentities('iñtërnâtiônàlizætiøn',ENT_COMPAT,'UTF-8');
    ?>

Finally, when accepting data from the user via forms most browsers will already send this data to the server in the current page encoding, but to be certain we can use the accept-charset attribute of HTML forms:

    <form action="https://www.example.com/"
          accept-charset="UTF-8"
          enctype="application/x-www-form-urlencoded"
          method="post">
    <!-- inputs -->
    </form>

Working with External Datasources
---------------------------------

When working with external datasources character encoding problems can be very painful if not considered up-front. The easiest way to handle them is to try and make sure everything uses the same UTF8 encoding as you do.

### Your Database

When using a DB, you need to use connect to it using a UTF8 character set. This varies depending on the database type and PHP connection library, but for example using PDO to connect to MySQL:

    <?php
    $sql = "SET CHARACTER SET utf8, time_zone='+0:00'";
    $db = new PDO("mysql:host=$host;dbname=$db_name",
                  $user,$passwd,
                  array(PDO::MYSQL_ATTR_INIT_COMMAND => $sql));

    ?>

It also makes sense for you to store the text as UTF8 internally in the database if you are also responsible for its schema. For example, to create a MySQL db that defaults to UTF8:

    CREATE DATABASE db_name
      CHARACTER SET utf8 DEFAULT CHARACTER SET utf8
      COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

### Your IDE!

Finally, don't forget your own code IDE. This should be configured to save files in an UTF8 encoding otherwise any strings you hard-code won't be in the right encoding. You can usually find a default character encoding setting somewhere in amongst your IDE preferences.
