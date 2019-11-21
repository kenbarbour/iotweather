This is a really simple weather station server for IoT

## Installation
1. Run composer to install dependencies: `php composer.phar install`
2. Create/Edit `.env` configuration file
3. Create database from schema.sql file:

```(source .env && cat schema.sql | mysql -u $DB_USER -h $DB_HOST --password="$DB_PASS" $DB_NAME)```

Note: The above command will use the variables defined in the .env file and can be pasted directly into the shell.

## POSTing data
Send POST requests to index.php with an X-API-KEY header and urlencoded form data using of the form: `temperature=25.5&pressure=10015.2&humidity=35.25`

Use the same API key defined in the .env file.

An example using cURL:
```
curl -X POST -H 'X-API-KEY: ThisIsNotVerySecretSoChangeMe' -H 'Content-Type: application/x-www-form-urlencoded' -i 'http://localhost:9010/index.php' --data 'temperature=25.5&pressure=10015.2&humidity=35.25'
```