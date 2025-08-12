# Laravel Salesforce

This package provides a streamlined way to integrate Salesforce objects into your Laravel application.  
It includes a custom query builder, model base class, and an Artisan command to automatically generate strongly-typed PHP classes for your Salesforce SObjects.

## Installation

Install via Composer:

```bash
composer require your-vendor/laravel-salesforce
```

### Environment Variables

Before using the package, you must configure your Salesforce credentials in your Laravel .env file:

| Variable                   | Description                                                                                                            |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `SALESFORCE_CLIENT_ID`     | The **Consumer Key** from your Salesforce Connected App.                                                               |
| `SALESFORCE_CLIENT_SECRET` | The **Consumer Secret** from your Salesforce Connected App.                                                            |
| `SALESFORCE_USERNAME`      | Salesforce username used for authentication.                                                                           |
| `SALESFORCE_PASSWORD`      | Salesforce password **plus** security token (if required).                                                             |
| `SALESFORCE_LOGIN_URL`     | Salesforce login URL — usually `https://login.salesforce.com` (production) or `https://test.salesforce.com` (sandbox). |

#### Example .env

```env
SALESFORCE_CLIENT_ID=your-client-id
SALESFORCE_CLIENT_SECRET=your-client-secret
SALESFORCE_USERNAME=your-username
SALESFORCE_PASSWORD=your-password-and-token
SALESFORCE_LOGIN_URL=https://login.salesforce.com
```

## Generating Salesforce Objects

This package ships with an Artisan command to generate PHP classes for your Salesforce SObjects.

### All Objects

```bash
php artisan salesforce:generate-objects
```

This will connect to your Salesforce instance, retrieve all available objects, and create corresponding PHP classes in the app/SalesforceObjects directory.
Please be warned, if you have a large salesforce instance, with lots of objects, this will be a long running command. I recommend you generate specific objects only
(as shown below).

### Specific Objects

You can limit generation to one or more objects using the `--objects` option:

```bash
php artisan salesforce:generate-objects --objects=Account,Contact,Opportunity
```

This will only generate classes for the specified Salesforce Objects.

## Example Usage

Once an object has been generated, you can make use of Eloquent style operations.

### Create an Object

```php
$contact = Contact::create([
    'Name' => 'Test Contact'
]); // $contact will be type Contact, with the Id field populated only.
```

### List Objects

```php
Contact::fields(['Id', 'Email', 'Name__c',]) // Use fields() to specify limited field sets for queries
    ->where('Email', 'LIKE', 'maurice%')
    ->orWhere('Id', '=', '003D000002TND2QIAX')
    ->all() // Fetches ALL records, includes pagination handling for Salesforce 2000 row pages.
    ->records() // Returns collection of Contact;
```

```php
Contact::fields(['Id', 'Email', 'Name__c',]) // Use fields() to specify limited field sets for queries
    ->where('Email', 'LIKE', 'maurice%')
    ->orWhere('Id', '=', '003D000002TND2QIAX')
    ->limit(20) // Set a hard limit. orderBy methods available as well.
    ->get() // Fetches without handling pages.
    ->records() // Returns collection of Contact;
```

### Find an Object

```php
Contact::find('MyId'); // Returns an instance of Contact, runs FIELDS(ALL) behind the scenes.
```

### Update an Object

```php
$contact = Contact::find('MyId');
$contact->update([
    'Name__c' => 'New Name'
]); // Returns no new data. Suggest a refetch/find operation afterwards to update state.
```

### Delete an Object

```php
$contact = Contact::find('MyId');
$contact->delete(); // Returns null
```
