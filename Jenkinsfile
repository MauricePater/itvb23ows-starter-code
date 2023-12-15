pipeline {
    agent { docker { image 'composer/composer' } }
    stages {
        stage('build') {
            steps {
                sh 'php --version'
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
