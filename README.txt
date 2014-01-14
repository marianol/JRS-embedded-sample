Tools:
  - Apache2
    - with "mod_jk" and "php_curl" extensions
  - Tomcat
    - JasperReports Server Pro installed
  - Database of your choosing for JasperReports Server Pro (PostgreSQL was used for development of this project)
  - Database of your choosing for Drupal (PostgreSQL was used for development of this project)

Setup:
  - Project directory should be in an apache2 hosted directory
  - Take sql dump in project (drupal_database_dump.sql) and import it into the database connected with the drupal app that is included with this project
  - Deploy JasperReports Server to Tomcat
  - Set up "mod_jk" to connect between Apache2 and Tomcat
    - http://community.jaspersoft.com/wiki/connecting-apache-web-server-tomcat-and-writing-re-direct-rules
  - Upload "embedded_scdp" theme to JasperReports Server Pro using the interface
  - Upload the report "14Performance_Summary_export.zip"