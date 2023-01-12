pipeline {
    agent {
        dockerfile {
            additionalBuildArgs  "--build-arg version=1.0.${env.BUILD_ID}"
            args '-t atom-staging:latest'
            args '-v /tmp:/tmp'
            }
    }
    stages {
        stage('Build') {
            steps {
                echo 'Building...'
            }
            }
            }
        }