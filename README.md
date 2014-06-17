MutiFaucet v0.8.2
=================
Copyright 2014 by The Daniel Morante Company, Inc.
http://www.unibia.net/crypto-faucet

Easy to setup crypto currency faucet that supports multiple currencies.  
It was loosely derived from the Simple Faucet script by Dogenes.  This faucet
is built using the Enchilada Frame Work 3.0 and Enchilada Libraries 2.0 (http://www.buenapp.com/).

It's current features include:

- Web installer that makes it easy to setup, just extract and go.
- Automatic locale and translation into any language.
- Support for either Hot or Cold crypto wallets.
- Themes.
- Simple Captcha, re-CAPTCHA, or Solve Media.
- SpammerSlapper* Anti-Proxy Abuse
- Remote Management via JSON-RPC.
- Muti-site capable (premium add-on).

A live example can be seen at: http://faucet.securepayment.cc

Requirements
-----------

- MySQL 5.1 or later
- PHP 5.3 or later
- Crypto currency wallet with RPC access (can be on a seprate server)

Installation
-------------

1) Create a MySQL database and user that will be used for the faucet.

2) Download the archive and extract into any folder or root folder on your web server.  

3) Allow write permissions to the "config" folder

4) Open the website in your browser to start the web based installer or visit 
	http://webserver/<upload_folder>/install.php.
	
5) Delete or rename install.php

If you want to re-configure any settings in the future simply (restore install.php if needed) 
and re-run the installation script or manually edit the config files.

If you are using the cold wallet it's recommended that you place the data file outside your web directory.

Usage
-----

Once the faucet has enough funds, visitors only need to enter a valid wallet address to obtain coins. 
they will also need to solve a CAPTCHA if enabled. Depending on the anti-abuse features to enabled. 
The user may be rewarded or told to come back later.

The promo codes feature (if enabled) awards an extra amount of coins to the visitor.  At the moment the
codes and rewards will need to be manually entered into the database table "PRFX_promo_codes".

If you are using the hot wallet, payments will be sent immediately.  If using the cold wallet the funds need
to be sent manually or by a script.  The faucet's RPC interface can aid with this process.  A sample PHP 
script that interacts with the faucet's RPC interface to send payments and re-fill the faucet along with
detailed usage can be found at:

http://www.unibia.net/crypto-faucet

Restrictions
------------

All installations must preserve the HTML in "powered by" section in the default theme.  Custom themes 
must also produce the same HTML code unless written permission is granted by the MultiFaucet author.

If you found this program useful please show your appreciation by sending me some:

BTC: 1B6eyXVRPxdEitW5vWrUnzzXUy6o38P9wN
ZET: ZK6kdE5H5q7H6QRNRAuqLF6RrVD4cFbiNX

Donators will be allowed to remove the "powered by" link.
