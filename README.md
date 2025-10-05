# Laravel Salesforce

This package provides a streamlined way to integrate Salesforce objects into your Laravel application.  
It includes a custom query builder, model base class, and an Artisan command to automatically generate strongly-typed PHP classes for your Salesforce SObjects.

## Installation

Install via Composer:

```bash
composer require flinty916/laravel-salesforce
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
| `SALESFORCE_GRANT_TYPE`    | Salesforce `grant_type` - currently supports `password` flow (legacy) and `client_credentials`                         |
| `SALESFORCE_API_VERSION`   | Salesforce API version to use. Defaults to `58.0`                                                                      |

#### Example .env

```env
SALESFORCE_CLIENT_ID=your-client-id
SALESFORCE_CLIENT_SECRET=your-client-secret
SALESFORCE_USERNAME=your-username // password grant type only
SALESFORCE_PASSWORD=your-password-and-token // password grant type only
SALESFORCE_LOGIN_URL=https://login.salesforce.com
SALESFORCE_GRANT_TYPE=client_credentials
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

### Describe an Object

Occasionally you'll need to obtain the metadata of an object, including Picklist values, fields, and more. This
is the use case for the `describe` method, which returns the API response from a salesforce `sobject/<object>/describe`
request.

```php
$description = Contact::describe(); // Returns SalesforceDescription
$myPicklistValues = $description->fields->where('name', 'myField')->first()->picklistValues; // returns Collection of SalesforcePicklistValue
```

## Working with Chatter Feeds

This package includes first-class support for Salesforce Chatter feeds, allowing you to retrieve, post, update, and comment on feed items related to any Salesforce record (e.g., an Opportunity, Account, or Case).

### Retrieving Feed Items

You can access a record’s chatter feed through the chatter() helper:
```php
$opportunity = Opportunity::find('0068d00000ABC123');

$feed = $opportunity->chatter()->all();

// Returns a collection of feed elements (posts, comments, files, etc.)
foreach ($feed as $element) {
    echo $element['actor']['displayName'] . ': ' . $element['body']['text'];
}
```

Under the hood, this calls the Salesforce Chatter REST API:
```
GET /services/data/v{api_version}/chatter/feeds/record/{recordId}/feed-elements
```
and automatically maps the response into PHP objects for easy access.

### Posting a New Message

To create a new post on a record’s feed, call post():
```php
$opportunity->chatter()->post('We should increase the deal size to £200k.');
```

This issues a POST request to:
```
POST /services/data/v{api_version}/chatter/feed-elements
```
and creates a FeedItem linked to the record.

### Updating a Message

If you need to edit a feed message (your own post), you can use update():
```php
$opportunity->chatter()->update('0D5xx00000ABCDe', 'Updated deal size to £250k.');
```

This sends a PATCH request to:
```
PATCH /services/data/v{api_version}/chatter/feed-elements/{feedElementId}
```
and updates the feed item’s body content.

### Commenting on a Message

To add a comment to an existing feed item, use comment():
```php
$opportunity->chatter()->comment('0D5xx00000ABCDe', 'Agreed — let’s go for it.');
```
This issues a POST request to:
```
POST /services/data/v{api_version}/chatter/feed-elements/{feedElementId}/capabilities/comments/items
```
and appends a new FeedComment to the specified post.

### Example Workflow
```php
$opportunity = Opportunity::find('0068d00000ABC123');

// Post a message
$post = $opportunity->chatter()->post('Initial quote sent to client.');

// Add a follow-up comment
$opportunity->chatter()->comment($post['id'], 'Client requested updated pricing.');

// Retrieve all feed items
$feed = $opportunity->chatter()->all();
```

### Notes

Feed actions are available for any Salesforce record with an associated Chatter feed (FeedEnabled objects).
Returned data is normalized through SalesforceChatterResponse, so you can iterate and inspect feed elements easily.
Posting and commenting automatically handle message segment formatting — plain text is supported by default, but future versions will support rich mentions, links, and file attachments.
