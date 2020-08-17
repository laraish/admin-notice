# Introduction
This is a simple helper lib for sending notice in WordPress admin pages.

## Usage
Before you can use it be sure init it by:
```php
use Laraish\AdminNotice\AdminNotice;

AdminNotice::init();
```

### Basic
```php
AdminNotice::success('Task Completed!');
```

Other type of notices

* success
* error
* warning
* info

### Dismissible Notice
The second parameter determine if the notice has a 'dismiss' button. 

```php
AdminNotice::success('Task Completed!', true);
```

### Persistent Notice
To make the notice showing persistently, passing a notice id to the method. 
```php
AdminNotice::success('Task Completed!', true, 'your-unique-notice-id');
```
