pipeline {
  agent any
  stages {
    stage('test') {
      steps {
        echo 'C\'est moi'
        sh '''printenv'''
        sh '''docker run --rm -v /Users/aurore/jenkins/blueocean_home/workspace/son_lesson-finder_jenkinsci-FJVYUE2X2I3GYUZ3C3DM247JEJKRIAARINNUTVYBL2R2UBTG2UBQ:/app composer/composer install'''
        sh '''docker run -v /Users/aurore/jenkins/blueocean_home/workspace/son_lesson-finder_jenkinsci-FJVYUE2X2I3GYUZ3C3DM247JEJKRIAARINNUTVYBL2R2UBTG2UBQ:/app --rm phpunit/phpunit:4.8.4 -c phpunit.xml.dist'''
      }
    }
  }
}
