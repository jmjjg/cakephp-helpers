sudo ant -u jenkins ant phpunit -f plugins/Helpers/vendor/Jenkins/build.xml

wget http://localhost:8080/jnlpJars/jenkins-cli.jar
/usr/lib/java/bin/java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 Plugin Helpers" < "plugins/Helpers/vendor/Jenkins/jobs/CakePHP3-Helpers-Plugin.xml"
/usr/lib/java/bin/java -jar jenkins-cli.jar -s http://localhost:8080/ create-job "CakePHP 3 Plugin Helpers Quality" < "plugins/Helpers/vendor/Jenkins/jobs/CakePHP3-Helpers-Plugin-Quality.xml"