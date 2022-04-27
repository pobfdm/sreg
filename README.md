# Sreg
A simple class for registering users.

Change the database connection settings directly to "sreg.php" and take a look at "example.php"

Table schema:
```
CREATE TABLE  "users" (
  "id" INTEGER NOT NULL ,
  "user" varchar(1) ,
  "pass" varchar(256) ,
  "regcode" varchar(256),
  "enable" INTEGER,
  "cookie" varchar(256) ,
  "name"	TEXT,
  PRIMARY KEY ("id") 
);
```
Example of use:

```
<?php
$myreg = new reg();
$myreg->do($header,$body,$footer);
?>
```

Where header, body and footer are the variables that contain the html. See "example.php" to get an idea.