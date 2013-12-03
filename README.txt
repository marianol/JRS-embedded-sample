Tools:
	- Apache2
		- mod_jk and php_curl
	- Tomcat
    - JasperReports Server

Setup:
	- Take sql dump in project and import it into your choice of database, connect the drupal app that is included with this project to it.
	- Have Apache2 point to the project directory
	- Deploy JasperReports Server to tomcat
	- Set up mod_jk to connect between apache and tomcat

http://community.jaspersoft.com/wiki/connecting-apache-web-server-tomcat-and-writing-re-direct-rules

	Everything should work!

	*** Work in progress ***
