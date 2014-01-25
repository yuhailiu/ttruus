<?php
$file_delete = "../../images/userphotos/YUHAI.png";
   
   if (unlink($file_delete)) {
      echo "The file was deleted successfully.", "n";
   } else {
      echo "The specified file could not be deleted. Please try again.", "n";
   }