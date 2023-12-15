pipeline {
    agent { docker { image 'php:8.3.0-alpine3.19' } }
    stages {
        stage('build') {
            steps {
                sh 'composer install'
                sh 'php bin/console cache:warmup'
            }
        }
    }
    post {
        always {
            echo 'build has been completed'
        }
        success {
            echo 'build has been successful'
        }
        failure {
            echo 'build has failed'
        }
        unstable {
            echo 'build was unstable'
        }
        changed {
            echo 'something changed'
        }
    }
}
