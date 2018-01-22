<h1 align="center">JH Development Workflow Tool</h1>

<p align="center">

Some Badges would be nice. 

</p>

## Install

Make sure you have `fswatch` installed. You can install via homebrew:

```
brew install fswatch
```

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

Notes: 

- Make sure your composer global bin directory `~/.composer/vendor/bin` is available in your `$PATH` environment variable.
- Because packages installed globally with composer share dependencies, you may need to run `composer global update` if the 
previous command failed.

## Usage

Before you create any new project, first update the tool, in-case of any fixes or new features.

```
composer global update wearejh/workflow
```

Then run `workflow` to see the list of available commands.

Read the [wiki](https://github.com/WeareJH/workflow/wiki) for detailed information on each command

## Troubleshooting

If you are experiencing very slow speeds (i.e. it's hanging for minutes inbetween commands), it may be due to a slow DNS lookup to localunixsocket.local. See relevant [GitHub issue](https://github.com/docker/compose/issues/3419#issuecomment-221793401)
A quick fix is to add the following to your hosts file.

`127.0.0.1 localunixsocket.local`
