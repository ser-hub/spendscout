# SpendScout

An application that helps you manage and track your expenses efficiently.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## Introduction

SpendScout is an application built on the Symfony framework, offering a robust platform for tracking expenses. 
It provides an intuitive interface to input and categorize expenses, view reports, and manage your financial transactions effectively.

## Features

- **Expense Entry**: Easily add and categorize expenses.
- **Expense Tags**: Organize expenses into customizable categories.
- **Reporting**: Generate reports to analyze spending patterns.
- **User Authentication**: Secure user authentication and authorization.
- **Responsive Design**: Access the application from any device.

## Requirements

Before anything, make sure you have this software installed on your machine:

- PHP >= 8.1
- Composer
- Symfony 6.4
- MariaDB 11.4.0

## Installation

1. Clone the repository
2. Install dependencies:
```
composer install
```
symfony console importmap:install

4. Set up your environment variables by copying the `.env` file and update `.env.local` with your database credentials and other configuration options.
5. Create the database schema and populate it with some data:
```
symfony console doctrine:database:create
```
```
symfony console doctrine:migrations:migrate
```
```
symfony console doctrine:fixtures:load
```
6. Install certificate authority for the local server:
```
symfony server:ca:install
```
7. Start the Symfony server by runing the command:
```
symfony server:start
```
## Usage

1. Register a new account and log in.
2. Add your expenses, specifying the name, tag, amount, currency and date.
3. Explore different features such as reports and tags to manage your expenses effectively.




