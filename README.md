# UserSearch hometask project

## Installation

### Tested on xampp 8.0.25 / PHP 8.0.25

### Sample.rar contains a sample of a data that was used for testing feel free to use it.

### If needed change the database details at src/defines.php
```
    define("DB_HOST", "localhost");
    define("DB_NAME", "task");
    define("DB_USER", "root");
    define("DB_PASS", "");
```

### Change htaccess file RewriteBase at .htaccess
If project is under a subdir change the /hometask/ to the name of the subdir.
If you use the project on a root directory just remove the line of the RewriteBase
```
RewriteBase /hometask/
```

### Installing the database
Create schema named task and import the task.sql file

### Genearte data
Run the generate.php file using php command or web


## How to run
### Open index.html file using the web to use the search engine
