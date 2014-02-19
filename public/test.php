<h2>test logfile</h2>
<?php

  $dbc = mysqli_connect('127.0.0.1', 'root', 'root', 'ttruus')
    or die('Error connecting to MySQL server.');

  $sql = "select * from users";
  $result = mysqli_query($dbc, $sql)
    or die('Error querying database.');
  foreach ($result as $rs){
      echo '<pre>'. print_r($rs, true).'</pre>';
  }
  

  mysqli_close($dbc);
