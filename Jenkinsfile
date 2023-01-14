pipeline {
    agent {
        dockerfile {
            additionalBuildArgs  "--build-arg version=1.0.${env.BUILD_ID},tag=atom-staging"
            args '-v /tmp:/tmp'
            }
    }
    stages {
        stage('Build') {
            steps {
                echo 'Building docker container...'
            }
            }
            }
        }