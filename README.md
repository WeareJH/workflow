<h1 align="center">JH Development Workflow Tool</h1>

<p align="center">

Some Badges would be nice. 

</p>

## Install

Add the following to `~/.composer/composer.json`:

```
"repositories" : [
    {
        "type": "vcs",
        "url": "git@github.com:WeareJH/workflow.git"
    }
]
```

Then run:

```
composer global require wearejh/workflow:dev-master
```

Make sure your composer global bin directory `~/.composer/vendor/bin` is available in your `$PATH` environment variable.

## Usage

Before you create any new project, first update the tool, in-case of any fixes or new features.

```
composer global update wearejh/workflow
```

Then run `workflow` to see the list of available commands.

Read the [wiki]() for detailed information on each command
