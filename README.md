[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-8.2+-orange.svg)]
[![Symfony Version](https://img.shields.io/badge/symfony-7.3-blueviolet.svg)]

# SignForge

SignForge is a professional document generation platform built with Symfony. It allows companies to create, manage, and export business documents such as invoices, quotes, purchase orders, and delivery notes in PDF format.

## Table of Contents

1. [Project Overview](#project-overview)  
2. [Installation & Configuration](#installation-configuration)  
3. [HTML Structure](#html-structure)  
4. [CSS Files & Structure](#css-structure)  
5. [JavaScript Functionality](#javascript-functionality)  
6. [PSD Assets](#psd-assets)  
7. [Sources & Credits](#sources-credits)  
8. [PHP Code Explanation](#php-code-explanation)  
9. [API Integration](#api-integration)  
10. [Unique Features & Customizations](#unique-features-customizations)  
11. [FAQ & Support](#faq-support)  
12. [Unit Testing](#unit-testing)  
13. [Internationalization](#internationalization)  

**Full documentation**: [HTML version →](docs/index.html)

## 1. Project Overview

SignForge is a Symfony‑based platform that streamlines the creation, management and export of business documents by generating professional, branded PDFs in seconds.

### Key Features

- **PDF Generation:** Generate high‑quality PDF documents branded with your logo.  
- **Client Management:** Store and reuse client profiles, billing terms, and document history.  
- **All Document Types:** Create quotes, invoices, contracts, delivery notes, and more.  
- **Global Dashboard:** At‑a‑glance view of document statuses, client activity, and key metrics in real time.

### Target Audience

- Freelancers  
- Creative Agencies  
- Small & Medium Businesses  
- Enterprises  

### Version

Current Version: **v1.0**

### Roadmap

**Next (v2.0)**  
In version 2.0 we will introduce a dedicated “super admin” role to manage the entire application (today it’s designed for personal use only) and integrate AI‑powered capabilities for automated price comparison and dynamic description generation.

---

## 2. Installation and Configuration {#installation-configuration}

### 2.1 Prerequisites

- **PHP 8.2+** with extensions: `intl`, `mbstring`, `xml` (for DOMPDF)  
- **Symfony CLI** ≥ 7.x  
- **Node.js** ≥ 16  
- **Yarn** ≥ 1.22  

### 2.2 Installation

1. Unzip the archive:  
   Place the `signforge` folder inside your web server’s root directory:  
   - For **XAMPP**: `C:\xampp\htdocs\signforge`  
   - For **WAMP**: `C:\wamp64\www\signforge`  
   - Or use **any directory** if you're using **Symfony CLI**

```bash
   unzip signforge.zip
   cd signforge
```

2. Install PHP dependencies:
```bash
    composer install
```

3. Configure .env for your database:
```bash
DATABASE_URL="mysql://user:password@127.0.0.1:3306/signforge"
```
4. Create the database and schema:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Load sample data:
```bash
php bin/console doctrine:fixtures:load
```
6. Install frontend dependencies and build assets:
```bash
yarn install && yarn dev
```
7. Start the development server:

    symfony server:start

    Access the app at: http://localhost:8000

### 2.3 Environment Configuration

1. Copy `.env` to `.env.local`:

```bash
   cp .env .env.local
```
# Database connection
DATABASE_URL="mysql://user:password@127.0.0.1:3306/signforge"

# Environment (use 'prod' in production)
APP_ENV=dev
APP_SECRET=<your-secret-key>

# —————————————————————————————
# In production, override:
# APP_ENV=prod
# APP_SECRET=<your-secure-production-secret>
# —————————————————————————————
# To generate a fresh secret key:
```bash
   php bin/console secrets:generate-keys
```
### 2.4 Admin Access

Default admin credentials:

    Email: admin@admin.com

    Password: admin

    Please change the password after installation.

### 2.5 Demo Data

A set of example clients, documents, and companies is included to test the system quickly.

## License

This project is for commercial use. Redistribution or resale without permission is prohibited.

## Support  
For help, email [support@signforge.tech](mailto:support@signforge.tech).