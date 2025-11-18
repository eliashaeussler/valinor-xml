<div align="center">

[![Code example](docs/img/header.svg)](#-installation)

# XML source for `cuyz/valinor`

[![Coverage](https://img.shields.io/coverallsCoverage/github/eliashaeussler/valinor-xml?logo=coveralls)](https://coveralls.io/github/eliashaeussler/valinor-xml)
[![CGL](https://img.shields.io/github/actions/workflow/status/eliashaeussler/valinor-xml/cgl.yaml?label=cgl&logo=github)](https://github.com/eliashaeussler/valinor-xml/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/eliashaeussler/valinor-xml/tests.yaml?label=tests&logo=github)](https://github.com/eliashaeussler/valinor-xml/actions/workflows/tests.yaml)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/eliashaeussler/valinor-xml/php?logo=php)](https://packagist.org/packages/eliashaeussler/valinor-xml)

</div>

A Composer library that provides an additional XML source for use with
the popular [`cuyz/valinor`](https://github.com/CuyZ/Valinor) library.
This allows to easily map XML files or contents to any signature supported
by Valinor, e.g. objects or special array shapes. It leverages the
[`mtownsend/xml-to-array`](https://github.com/mtownsend5512/xml-to-array)
library to convert raw XML to a reusable array structure.

## 🔥 Installation

[![Packagist](https://img.shields.io/packagist/v/eliashaeussler/valinor-xml?label=version&logo=packagist)](https://packagist.org/packages/eliashaeussler/valinor-xml)
[![Packagist Downloads](https://img.shields.io/packagist/dt/eliashaeussler/valinor-xml?color=brightgreen)](https://packagist.org/packages/eliashaeussler/valinor-xml)

```bash
composer require eliashaeussler/valinor-xml
```

## ⚡ Usage

Given the following XML:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<person>
    <name>Dr. Zane Stroman</name>
    <address>
        <street>439 Karley Loaf</street>
        <postcode>17916</postcode>
        <city>West Judge</city>
        <country>Falkland Islands (Malvinas)</country>
    </address>
    <contact>
        <phone>827-986-5852</phone>
    </contact>
</person>
```

These are the resulting classes:

```php
final readonly class Address
{
    public function __construct(
        public string $street,
        public string $postcode,
        public string $city,
        public string $country,
    ) {}
}

final readonly class Contact
{
    public function __construct(
        public string $phone,
    ) {}
}

final readonly class Person
{
    public function __construct(
        public string $name,
        public Address $address,
        public Contact $contact,
    ) {}
}
```

### Mapping from XML string

In order to map the given XML to the `Person` class, you need
to follow these three steps:

1. Create a new mapper as written in [Valinor's documentation](https://valinor.cuyz.io/latest/getting-started/)
2. Parse and prepare your XML using the shipped [`XmlSource`](src/Mapper/Source/XmlSource.php)
3. Use the mapper to map your XML to the `Person` class

```php
use CuyZ\Valinor;
use EliasHaeussler\ValinorXml;

$mapper = (new Valinor\MapperBuilder())->mapper();
$source = ValinorXml\Mapper\Source\XmlSource::fromXmlString($xml);
$person = $mapper->map(Person::class, $source); // instanceof Person
```

The resulting object will look something like this:

```
object(Person)#180 (3) {
  ["name"]=>
  string(16) "Dr. Zane Stroman"
  ["address"]=>
  object(Address)#135 (4) {
    ["street"]=>
    string(15) "439 Karley Loaf"
    ["postcode"]=>
    string(5) "17916"
    ["city"]=>
    string(10) "West Judge"
    ["country"]=>
    string(27) "Falkland Islands (Malvinas)"
  }
  ["contact"]=>
  object(Contact)#205 (1) {
    ["phone"]=>
    string(12) "827-986-5852"
  }
}
```

### Mapping from XML file

The XML can also be read from an external file:

```php
use CuyZ\Valinor;
use EliasHaeussler\ValinorXml;

$mapper = (new Valinor\MapperBuilder())->mapper();
$source = ValinorXml\Mapper\Source\XmlSource::fromXmlFile($file);
$person = $mapper->map(Person::class, $source); // instanceof Person
```

### Convert nodes to collections

Sometimes it might be necessary to always convert XML nodes to
collections. Given the following XML:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<community>
    <member><!-- ... --></member> <!-- NOTE: There's only one member -->
</community>
```

Let's assume you want to map this XML to the following class:

```php
final readonly class Community
{
    /**
      * @param list<Person> $member
      */
    public function __construct(
        public array $member,
    ) {}
}
```

You will recognize that this does not work as expected when using the
above mapping method:

```php
use CuyZ\Valinor;
use EliasHaeussler\ValinorXml;

$mapper = (new Valinor\MapperBuilder())->mapper();
$source = ValinorXml\Mapper\Source\XmlSource::fromXmlFile($file);
$person = $mapper->map(Community::class, $source); // throws exception
```

It will instead throw an exception like this:

```
CuyZ\Valinor\Mapper\TypeTreeMapperError: Could not map type `Community` with value array{member: array{…}}.
```

This is because the XML converter does not know whether `<member>` should
be a collection or if it's just a "normal" node. That's why the `XmlSource`
provides an appropriate method to convert such nodes to collections:

```php
use CuyZ\Valinor;
use EliasHaeussler\ValinorXml;

$mapper = (new Valinor\MapperBuilder())->mapper();
$source = ValinorXml\Mapper\Source\XmlSource::fromXmlFile($file)
    ->asCollection('member')
;
$person = $mapper->map(Community::class, $source); // instanceof Community
```

The resulting object will look something like this:

```
object(Community)#76 (1) {
  ["member"]=>
  array(1) {
    [0]=>
    object(Person)#126 (3) {
      ["name"]=>
      string(16) "Dr. Zane Stroman"
      ["address"]=>
      object(Address)#170 (4) {
        ["street"]=>
        string(15) "439 Karley Loaf"
        ["postcode"]=>
        string(5) "17916"
        ["city"]=>
        string(10) "West Judge"
        ["country"]=>
        string(27) "Falkland Islands (Malvinas)"
      }
      ["contact"]=>
      object(Contact)#252 (1) {
        ["phone"]=>
        string(12) "827-986-5852"
      }
    }
  }
}
```

However, this is only relevant if only one node of the collection
exists in your XML. If the XML contains more than one node, the XML
converter properly converts them to a collection:

```php
use CuyZ\Valinor;
use EliasHaeussler\ValinorXml;

$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<community>
    <member><!-- ... --></member>
    <member><!-- ... --></member>
    <member><!-- ... --></member>
</community>
XML;

$mapper = (new Valinor\MapperBuilder())->mapper();
$source = ValinorXml\Mapper\Source\XmlSource::fromXmlString($xml);
$person = $mapper->map(Community::class, $source); // instanceof Community
```

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
