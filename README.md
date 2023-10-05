# AntiSpam
Antispam inputs to help detect form submission by spam-bots.

For license information check the [LICENSE](LICENSE.md) file.

## Features
* Two methods of detecting a form submitted by a spam-bot: Hash and Honey Pot
* No need to declare additional attributes, attribute labels, or rules in your model
* Minimal additions to your model: add a trait and a behavior, and call behavior methods from the attributeLabel() 
  and rules() methods
* `hasSpam` attribute added to your model (by the behavior) to determine whether a form has been submitted by a spam-bot

## Input Types
### Hash
Created a hidden input that receives the MD5 hash of the real input value on its blur event. Spam-bots to do not trigger
input events, so when the form is validated, if the content of _hash_ input does not equal the MD5 hash of the real 
input value the form has been submitted by a spam-bot.

### Honey Pot
Spam-bots look for _"standard"_ field names: e.g. email, and/or complete all fields on a form. When applied to a 
model attribute, an additional input is created; this field receives the name of the attribute and hidden by CSS so 
that it cannot be completed by a human but can be by a spam-bot. Therefore, when the form is validated if the _honey 
pot_ field contains a value the form has been submitted by a spam-bot. A separate input is created for a human to 
complete; the value in this field is copied to the real attribute after validation, so application code only ever uses 
the real attribute.

**There are few things to note:**
* Both Hash and Honey Pot _must_ be applied to attributes that generate text inputs.
* Hash and Honey Pot _**must not**_ be applied to the same attribute
* You may have more than one Hash and/or Honey Pot field in the same form

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
composer require --prefer-dist BeastBytes/antispam
```
or add

```json
"beastbytes/antispam": "^1.0.0"
```
to the `require` section of your composer.json.
