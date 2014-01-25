<?php
echo 'test';
//echo $this->render('footer1.phtml');

echo $this->partial('footer1.phtml', array(
		'from' => 'Team Framework',
		'subject' => 'view partials'));
?>