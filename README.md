# Webix Queue

**Webix Queue** is a lightweight digital queue and pager notification system designed to help businesses notify customers when their turn is ready — without the need for physical pagers.

The system provides a simple interface for staff to manage customer queues and allows customers to receive notifications when they are called.

## Features

* Digital customer queue management
* Customer number generation
* Call the next customer
* Real-time queue updates
* Audio notification when a customer is called
* Customer-facing queue display
* Responsive design for desktop and mobile
* No physical pager devices required
* Simple and lightweight architecture

## How It Works

1. A customer joins the queue.
2. The system assigns them a queue number.
3. Staff manage the queue from the control panel.
4. When the customer's turn arrives, staff call their number.
5. The customer receives a notification and can proceed to the service counter.

## Tech Stack

* **PHP**
* **MySQL**
* **HTML5**
* **CSS3**
* **Vanilla JavaScript**
* **Web Audio API**

## Project Structure

```text
webix-queue/
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── admin/
│   └── ...
│
├── customer/
│   └── ...
│
├── api/
│   └── ...
│
├── config/
│   └── ...
│
├── index.php
└── README.md
```

> The exact structure may vary depending on the project version.

## Use Cases

Webix Queue can be adapted for:

* Cafés and restaurants
* Clinics
* Salons
* Government service centers
* Customer service counters
* Pickup orders
* Small businesses

## Why Webix Queue?

Traditional physical pagers can be inconvenient to maintain and replace.

Webix Queue provides a digital alternative that can run on devices customers already have, making queue management simpler and more accessible.

## Project Status

**MVP — In Development**

This project was created as a practical Webix project and can be expanded with additional features such as:

* SMS notifications
* WhatsApp notifications
* Multi-branch support
* Queue analytics
* Staff accounts
* Custom branding
* Advanced notification settings

## Screenshots

Add screenshots of the project here:

```text
/screenshots/
├── dashboard.png
├── queue.png
└── customer-view.png
```

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/YOUR-USERNAME/webix-queue.git
```

### 2. Move the project

Place the project inside your local server directory, for example:

```text
htdocs/webix-queue
```

### 3. Configure the database

Create a MySQL database and import the provided SQL file if available.

Update the database configuration with your credentials.

### 4. Run the project

Start Apache and MySQL using XAMPP, then open:

```text
http://localhost/webix-queue
```

## License

This project is created by **Webix** for demonstration and development purposes.

---

### Webix

**Websites • Web Applications • Digital Solutions**

Built with PHP, JavaScript, and a focus on simple and practical digital experiences.
