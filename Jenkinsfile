pipeline {
  agent any
  stages {
    stage('test') {
      steps {
        echo 'C\'est moi'
        sh '''printenv
whoami
echo $MINECRAFT'''
        sh 'docker run --rm -v $PWD:/app composer/composer install'
        sh 'docker run -v $PWD:/app --rm phpunit/phpunit:4.8.4 -c phpunit.xml.dist'
      }
    }
  }
}