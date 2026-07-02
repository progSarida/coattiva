<?php

	switch($_GET['file'])
	{
		case 'pchart-piegraph':
			$file = 'pchart/piegraph.php';
			break;

		case 'pchart-linegraph':
			$file = 'pchart/linegraph.php';
			break;

		case 'google-piegraph':
			$file = 'google/piegraph.htm';
			break;

		case 'google-linegraph':
			$file = 'google/linegraph.htm';
			break;
	}

	highlight_file($file);

?>