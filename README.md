# Web Dictionary

## Installation

* Clone the project: `git clone https://gitlab.com/iotwatcher/ui.git web-dictionary`
* Init submodules: `cd web-dictionary; git submodule init && git submodule update`
* Create `docker-compose.override.yml` in the root of the project to set ports, for example:
```yaml
version: "3.3"
services:
  web:
    ports:
    - 8080:80
```
* Run: `docker-compose build`
* Run: `docker-compose up -d`