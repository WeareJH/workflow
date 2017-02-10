<h1 align="center">{project-namespace}</h1>

<p align="center">

Some Badges would be nice. 

</p>

## Initial Setup

Ensure you have [Docker ](https://docs.docker.com/docker-for-mac/) and the [Workflow tool](https://github.com/WeareJH/workflow) installed and it's pre-requisites. 

If you want to supply a database seed, put the SQL file in `.docker/db` before starting.

```sh
$ git clone {project-reop} && cd {project-name}
$ cp .docker/local.env.dist .docker/local.env
$ workflow start
```

Your recommended to run two terminal windows as the start command will start a file watcher copying in files to the container on change which you should keep running. 

In a new terminal you can now finish off your setup dependant on your installation type.

### Supplied Database Seed

If you supplied a database seed you simply need to run the configure command. 

```sh
$ workflow mc
```

### Fresh Magento Database

If you want a fresh database and haven't supplied a database seed you need to run a full install.

```sh
$ workflow mfi
```

After the initial setup you will be able to use the `workflow` too to start, stop development as and when you wish, dynamic data will stay persistant until you remove it through docker. 

*Note: You can configure the `.docker/local.env` file to meet your requirements but likely not required* 

## Mailhog

Mailhog is accessible on `http://{project-domain}:8025/`

## Tests

```bash
composer test
```