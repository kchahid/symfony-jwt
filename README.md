<div align="center">
  <h1>Symfony-JWT</h1>
</div>

<!-- Badges -->
![GitHub contributors](https://img.shields.io/github/contributors/kchahid/symfony-docker)
![License](https://img.shields.io/badge/license-MIT-blue)
![GitHub last commit](https://img.shields.io/github/last-commit/kchahid/symfony-docker)
![GitHub issues](https://img.shields.io/github/issues/kchahid/symfony-docker)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/)
![code style action](https://github.com/kchahid/symfony-jwt/actions/workflows/code-style.yml/badge.svg)

<!-- Table of Contents -->
# Table of Contents
- [About the Project](#about-the-project)
    * [Built with](#built-with)
- [Getting Started](#getting-started)
    * [Prerequisites](#prerequisites)
    * [Run Locally](#run-locally)
    * [Deployment](#deployment)
    * [Usage](#usage)
- [License](#license)
- [Contact](#contact)

<!-- About the Project -->
## About the Project

Lcobucci JWT implementation in symfony application

<!-- TechStack -->
### Built With
![PHP](https://img.shields.io/static/v1?style=for-the-badge&message=8.2&color=777BB4&logo=PHP&logoColor=FFFFFF&label=PHP&labelColor=777BB4)
![Symfony](https://img.shields.io/static/v1?style=for-the-badge&message=6.3&color=000000&logo=Symfony&logoColor=FFFFFF&label=Symfony&labelColor=000000)
![PostgreSQL](https://img.shields.io/static/v1?style=for-the-badge&message=14&color=4169E1&logo=PostgreSQL&logoColor=FFFFFF&label=PostgreSQL&labelColor=4169E1)
![Docker](https://img.shields.io/static/v1?style=for-the-badge&message=Docker&color=2496ED&logo=Docker&logoColor=FFFFFF&label=&labelColor=2496ED)
![NGINX](https://img.shields.io/static/v1?style=for-the-badge&message=NGINX&color=009639&logo=NGINX&logoColor=FFFFFF&label=)

<!-- Getting Started -->
## Getting Started

<!-- Prerequisites -->
### Prerequisites

This project uses Docker to set up a development environment on your machine.

A Makefile has been designed to make it easy to use and no knowledge of docker is required.

<!-- Run Locally -->
### Run Locally

Clone the project

```bash
  git clone https://github.com/kchahid/symfony-jwt
```

Go to the project directory

```bash
  cd symfony-jwt
  make all
```

<!-- Deployment -->
### Deployment

For the first time, it will take a few minutes to download the docker images and install all necessary prerequisites.

Application will be exposed via port 8080:

```
http://127.0.0.1:8080
```

<!-- usage -->
### Usage

A collection postman is provided with the repo. See [Collection](https://github.com/kchahid/symfony-jwt/blob/master/doc/collection/symfony-jwt.postman_collection.json)

A route has also been developed to help generate the JWT.

![oauth token route](https://github.com/kchahid/symfony-jwt/blob/master/doc/screenshot/oauth_token.png)

<!-- workflow -->
### Workflow

![workflow](https://github.com/kchahid/symfony-jwt/blob/master/doc/screenshot/workflow.png)

<!-- License -->
## License

Distributed under the MIT License. See [Licence](https://github.com/kchahid/symfony-jwt/blob/master/LICENSE) for more information.

<!-- Contact -->
## Contact

![Microsoft Outlook](https://img.shields.io/static/v1?style=for-the-badge&message=Microsoft+Outlook&color=0078D4&logo=Microsoft+Outlook&logoColor=FFFFFF&label=)

Kamal Chahid - kchahid_@outlook.com

Project Link: [https://github.com/kchahid/symfony-jwt.git](https://github.com/kchahid/symfony-jwt.git)