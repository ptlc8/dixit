pipeline {
    agent any

    parameters {
        string(name: 'PIXABAY_API_KEY', defaultValue: params.PIXABAY_API_KEY ?: null, description: 'Pixabay API Key')
    }

    stages {
        stage('Build') {
            steps {
                sh 'docker compose build'
            }
        }
        stage('Deploy') {
            steps {
                sh 'docker compose up --remove-orphans -d'
            }
        }
    }
}
