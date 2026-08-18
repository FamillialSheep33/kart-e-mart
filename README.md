#  Kart-e-Mart

**Kart-e-Mart** is a web-based e-commerce project developed as a practical software engineering project.

The application provides a catalog-based shopping experience backed by a relational database, with a focus on building a complete web application from the frontend to the database layer.

**Live website:** https://kart-e-mart.com/sideCatalogo/index.php

---

##  Overview
Kart-e-Mart was developed to gain practical experience building and deploying a web application.
The project involves:
* Dynamic product information
* Database-driven content
* Server-side programming
* Client-side functionality
* Relational database management
* Web application deployment

The project is designed around a traditional web application architecture where the frontend communicates with server-side code, which in turn interacts with a MySQL database.
---

##  Architecture

```text
┌─────────────────────┐
│       Browser       │
│   HTML / CSS / JS   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│     PHP Backend     │
│  Server-side logic  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│       MySQL         │
│   Relational Data   │
└─────────────────────┘
```
This architecture allowed me to work with multiple layers of a web application rather than focusing exclusively on frontend development.
---
## Technologies

### Backend

* PHP

### Frontend

* HTML
* CSS
* JavaScript

### Database

* MySQL

### Deployment

* Linux-based web server
* Apache/PHP environment

---

##  Main Features

* Product catalog
* Dynamic product information
* MySQL database integration
* Server-side processing with PHP
* Web-based interface
* Browser-based access
* Deployed web application

---

## Database

Kart-e-Mart uses **MySQL** as its relational database system.

The database is responsible for storing application data and allowing the PHP backend to retrieve and process information dynamically.

The project gave me practical experience with:

* Relational databases
* SQL queries
* Database connections
* CRUD operations
* Data persistence
* Backend/database integration

---

## Running the Project Locally

### Requirements

* PHP
* MySQL
* Apache or another compatible web server
* Git

### Clone the repository

```bash
git clone https://github.com/FamillialSheep33/kart-e-mart.git
cd kart-e-mart
```

### Configure the database

create a database and then execute instalar.sql to create the necessary tables

> Never commit real database passwords, API keys, or other credentials to the repository.

### Start the application

If using Apache, place the project inside the server's document root and start Apache and MySQL.

Then open:

```text
http://localhost/kart-e-mart/
```

---

## What I Learned

Working on Kart-e-Mart helped me develop practical experience with the complete lifecycle of a web application.

Some of the main areas I worked with were:

* Developing server-side applications with PHP
* Connecting applications to MySQL
* Designing and querying relational databases
* Using JavaScript to add client-side functionality
* Structuring a web application
* Debugging web applications
* Deploying an application to a real server
* Working with Linux-based infrastructure
* Managing a project using Git

---

## Future Improvements

Possible improvements include:

* [ ] Improve authentication and authorization
* [ ] Add automated tests
* [ ] Improve input validation
* [ ] Improve error handling
* [ ] Add a REST API
* [ ] Improve responsive design
* [ ] Add better database documentation
* [ ] Containerize the application with Docker
* [ ] Add CI/CD
* [ ] Improve security practices

---

## Author

**Angel Edell **

IT & Digital Innovation Engineering Student

Interested in:

* Linux
* Backend Development
* DevOps
* Infrastructure
* Networking
* Automation
* Open Source

GitHub: **[@FamillialSheep33](https://github.com/FamillialSheep33)**

---

## License

This project is primarily intended as an educational and portfolio project.
