-- SQL statements for creating the database tables

CREATE TABLE paper_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    quantity_received INT,
    received_by VARCHAR(255)
);

CREATE TABLE paper_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    department VARCHAR(255),
    quantity_issued INT,
    issued_by VARCHAR(255)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
