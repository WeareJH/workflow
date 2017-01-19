# JH Development Workflow Tool

## Usage

1. Pull in with composer
```
composer require wearejh/workflow
```

2. Enable autoloading of classes for scripts
```
...
"autoload-dev": {
        "psr-4": {
            ...
            "Jh\\Workflow\\": "vendor/wearejh/workflow/src"
        }
    },
...
```

3. Add a script to Composer to use the tool

```
...
"scripts": {
    ...
    "run": "Jh\\Workflow\\CommandRouter::route"
}
...
```