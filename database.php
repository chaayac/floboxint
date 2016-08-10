<?php
   # database.php
   # Interview task for Flobox
   # Created by Christopher Chaaya 11/08/2016
   # Sets up the database

   $host        = "host=127.0.0.1";
   $port        = "port=5432";
   $dbname      = "dbname=floboxint";
   // $credentials = "user=postgres password=pass123";

   $db = pg_connect( "$host $port $dbname"  );
   if(!$db){
      echo "Error : Unable to open database\n";
   }
?>