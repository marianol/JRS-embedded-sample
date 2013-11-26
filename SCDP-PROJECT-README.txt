Simple Corporate Demo Portal Sample Application - Embedding JasperReports Server functionality into a Drupal application:

	This will be a brief overview of structure and rational behind it:

		# Images folder contains all of the images the web-app uses.
		# jasperScripts contains all of the Javascript scripts that are used in the web-app.
			# loading scripts use a library called spinJS which allows you to inject a ajax style loading icon, it is used on most pages
                	# the other scripts in the folder handle all the functionality of the pages
		# runreport.php constructs the PHP REST client and the functions are called by jQuery in order to be used in the web-app.
                	# newRunReport.php was supposed to be the same as above but for the newest version of the client available, but is not used as the new client is not fully supported yet
                #
