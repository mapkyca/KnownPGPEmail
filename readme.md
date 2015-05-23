Known PGP Email
===============

This is a simple tool that opportunistically PGP encrypts email sent from Known, so long as a valid key for the recipient is on the system's keyring.

This is particularly handy when combined with PGP signin <https://github.com/mapkyca/IdnoOpenPGPSignin> since this code extracts keys automatically from user profiles.

I wrote this to scratch my itch of having maximum use of encryption, but it might be of use to you.

Requirements
------------
* php5-gnupg module

Issues
------

* PGP only really works with plain text messages, since most email clients won't render a decrypted email's html as html. Therefore, you'll need to be running the latest version of Known (which supports plain text email), otherwise it'll attempt to strip tags (with intermittent success).

Licence
-------

GPL 2

See
---

* Author: Marcus Povey http://www.marcus-povey.co.uk
