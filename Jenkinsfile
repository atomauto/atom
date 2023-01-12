pipeline {
    agent {
        dockerfile {
            additionalBuildArgs  "--build-arg version=1.0.${env.BUILD_ID}"
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