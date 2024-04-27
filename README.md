OFX Parser
=================

[![Tests](https://github.com/beccha/ofxparser/actions/workflows/test.yml/badge.svg)](https://github.com/beccha/ofxparser/actions/workflows/test.yml)
[![Lint](https://github.com/beccha/ofxparser/actions/workflows/lint.yml/badge.svg)](https://github.com/beccha/ofxparser/actions/workflows/lint.yml)
[![Security](https://github.com/beccha/ofxparser/actions/workflows/security.yml/badge.svg)](https://github.com/beccha/ofxparser/actions/workflows/security.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/beccha/ofxparser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/beccha/ofxparser/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/beccha/ofxparser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/beccha/ofxparser/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/beccha/ofxparser/badges/build.png?b=master)](https://scrutinizer-ci.com/g/beccha/ofxparser/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/beccha/ofxparser/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

This is a fork of [oriatec/ofxparser](https://github.com/oriatec/ofxparser). However, the compatibilty with this library was not preserved.

--------------------

OFX Parser is a PHP library designed to parse an OFX file downloaded from a financial institution into simple PHP objects.

Here are the OFX [Specifications](https://financialdataexchange.org/common/Uploaded%20files/OFX%20files/OFX%20Banking%20Specification%20v2.3.pdf).

## Installation

Simply require the package using [Composer](https://getcomposer.org/):

```sh
$ composer require beccha/ofxparser
```

## Usage

You can access the nodes in your OFX file as follows:

```php
$ofxParser = new \Beccha\OfxParser\Parser();
$ofx = $ofxParser->loadFromFile('/path/to/your/bankstatement.ofx');

$bankAccounts = $ofx->getBankAccounts();
$firstBankAccount = $bankAccounts[0];

// Get the statement start and end dates
$startDate = $firstBankAccount->getStatement()->getStartDate();
$endDate = $firstBankAccount->getStatement()->getEndDate();

// Get the statement transactions for the account
$transactions = $firstBankAccount->getStatement()->getTransactions();
```

## Contribute

### Requirements

You must have docker installed on your system.

### Installation

Clone this repository on your system

```sh 
git clone https://github.com/beccha/ofxparser.git
```

Start and build a docker container with php7.4:

```sh 
make start
```

Deploy Composer packages:
 
```sh
make init
```

Launch unit tests:
 
```sh
make unit
```

### Other commands

Access shell:
 
```sh
make shell
```

Please make sure to check the quality of your code before submitting a pull request:

```sh
make quality-check
```

Full list of commands in the Makefile at the root of the project.

## Fork & Credits

This is a fork of [oriatec/ofxparser](https://github.com/oriatec/ofxparser), itself forked of [okonst/ofxparser](https://github.com/okonst/ofxparser), [asgrim/ofxparser](https://github.com/asgrim/ofxparser). Intended to be framework independent. 

Loosely based on the ruby [ofx-parser by Andrew A. Smith](https://github.com/aasmith/ofx-parser).
