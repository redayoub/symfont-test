# Development environment setup

## Requirements

- make
- git
- docker & docker-compose

## Steps

```
git clone https://github.com/redayoub/symfont-test symfony-test
cd symfony-test/
make install
```

Then go to http://localhost:8001

This project is using symfony messenger in order to process CSV files in the background, in order to run it please run:

```
make run-messenger
```