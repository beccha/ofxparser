OFX Parser
=================

This is a fork of [oriatec/ofxparser](https://github.com/oriatec/ofxparser)

--------------------

OFX Parser is a PHP library designed to parse an OFX file downloaded from a financial institution into simple PHP objects.

It supports multiple Bank Accounts, the required "Sign On" response, and recognises OFX timestamps.

## Installation

Simply require the package using [Composer](https://getcomposer.org/):

```bash
$ composer require beccha/ofxparser
```

## Usage

You can access the nodes in your OFX file as follows:

```php
$ofxParser = new \OfxParser\Parser();
$ofx = $ofxParser->loadFromFile('/path/to/your/bankstatement.ofx');

$bankAccount = reset($ofx->bankAccounts);

// Get the statement start and end dates
$startDate = $bankAccount->statement->startDate;
$endDate = $bankAccount->statement->endDate;

// Get the statement transactions for the account
$transactions = $bankAccount->statement->transactions;
```

Most common nodes are support. If you come across an inaccessible node in your OFX file, please submit a pull request!

## Contribute

### Requirements

You must have docker installed on your system.

### Installation

Clone this repository on your system

```bash 
git clone https://github.com/ORIATEC/ofxparser.git
```

Start and build a docker container with php7.4:

```bash 
make start
```

Access shell:
 
```bash
make shell
```

Full list of commands in the Makefile at the root of the project.

## Fork & Credits

This is a fork of [oriatec/ofxparser](https://github.com/oriatec/ofxparser), itself forked of [okonst/ofxparser](https://github.com/okonst/ofxparser), [asgrim/ofxparser](https://github.com/asgrim/ofxparser). Intended to be framework independent. 

Loosely based on the ruby [ofx-parser by Andrew A. Smith](https://github.com/aasmith/ofx-parser).
