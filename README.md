# 🏪 Tech Inventory & Sales Management System

A complete, easy-to-use inventory and sales management system designed for retail stores, warehouses, and small businesses. Manage your products, track sales, monitor stock levels, and generate financial reports - all in one place!

---

## 📸 Screenshots

<img width="1920" height="2612" alt="image" src="https://github.com/user-attachments/assets/6843630d-bd4b-4b76-b11f-f5290f143f91" />


---

## ✨ What Can This System Do?

### 📦 Product & Inventory Management
- **Manage Products** - Add products with images, SKU codes, descriptions, and pricing
- **Track Stock Levels** - Real-time inventory tracking with automatic updates
- **Low Stock Alerts** - Get notified when products are running low
- **Stock Movements** - Track every stock change (purchases, sales, adjustments, losses)
- **Stock Reconciliation** - Verify and correct stock discrepancies
- **Opening Balance** - Set initial stock levels for new products
- **Bulk Corrections** - Adjust multiple products at once

### 💰 Sales Management
- **Create Sales Orders** - Quick and easy order creation
- **Customer Management** - Store customer information and purchase history
- **Sales Returns** - Handle returns and refunds efficiently
- **Point of Sale (POS)** - Fast checkout for in-store sales
- **Invoice Generation** - Professional invoices for every sale
- **Sales Reports** - Track daily, monthly, and yearly sales performance

### 🛒 Purchase Management
- **Purchase Orders** - Create orders to restock from suppliers
- **Supplier Management** - Maintain supplier contacts and details
- **Purchase Returns** - Return defective or incorrect items
- **Cost Tracking** - Monitor purchase costs and profit margins
- **Auto Stock Updates** - Inventory automatically updates on purchase receipt

### 💵 Financial Tracking
- **Profit & Loss Reports** - See your profits in real-time (daily, monthly, yearly)
- **Expense Tracking** - Record all operating expenses (rent, utilities, salaries, etc.)
- **Revenue Tracking** - Monitor income from all sales
- **Cost of Goods Sold (COGS)** - Automatic calculation of product costs
- **Net Profit Calculation** - See your actual profit after all expenses
- **Financial Dashboard** - Visual charts and metrics for quick insights

### 📊 Reports & Analytics
- **Stock Reports** - Detailed stock levels and valuations
- **Stock Movement Report** - See all stock changes over time
- **Low Stock Report** - Products that need reordering
- **Sales Performance** - Best-selling products and sales trends
- **Profit Margins** - Track profitability by product
- **Expense Reports** - Categorized expense summaries

### 👥 Team & User Management
- **Multi-User Support** - Add team members with different access levels
- **Role-Based Access** - Store Admin (full access) and Store User (sales only)
- **Permissions Control** - Fine-tune what each user can see and do
- **Activity Tracking** - See who made changes and when

### 🏢 Multi-Store Support
- **Multiple Stores** - Each store has its own separate data
- **Store Isolation** - Complete privacy between stores
- **Store Settings** - Customize currency, tax, and business details
- **Independent Operations** - Each store runs independently

### 📈 Dashboard Features
- **Today's Summary** - See today's sales, purchases, and profit at a glance
- **Monthly Overview** - Track this month's performance
- **Quick Stats** - Stock value, low stock items, top metrics
- **Recent Transactions** - Latest sales, purchases, and stock movements
- **Visual Charts** - Easy-to-understand graphs and metrics

---

## 🚀 Quick Start Guide

### Installation

1. **Download the system**
```bash
git clone <repository-url>
cd laravel-daily-starter
```

2. **Install required software**
```bash
composer install
npm install
```

3. **Setup configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Create database**
   - Create a new database in MySQL/PostgreSQL
   - Update `.env` file with your database details:
     ```
     DB_DATABASE=your_database_name
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

5. **Setup the database tables**
```bash
php artisan migrate
```

6. **Load demo data (optional but recommended)**
```bash
php artisan db:seed --class=StoreAdminRoleSeeder
php artisan db:seed --class=DemoUsersSeeder
```

7. **Build the interface**
```bash
npm run build
```

8. **Start the system**
```bash
php artisan serve
```

9. **Open in browser**
   - Go to: `http://localhost:8000`
   - Login with demo credentials (see below)

---

## 🔐 Demo Login Credentials

Try the system with these pre-configured accounts:

### 👨‍💼 Store Admin (Full Access)
- **Email:** `admin@demo.com`
- **Password:** `password`
- **What you can do:**
  - All features available
  - Manage products, customers, suppliers
  - Create sales and purchase orders
  - Track stock movements
  - View all reports
  - Manage team members
  - Configure store settings

### 👤 Store User (Sales Staff)
- **Email:** `user@demo.com`
- **Password:** `password`
- **What you can do:**
  - Create sales orders
  - View customers and products
  - Process sales transactions
  - View sales dashboard

---

## 📚 How to Use the System

### Getting Started

#### 1️⃣ Create Your Account
1. Click **"Register"** on the login page
2. Fill in your details:
   - Your name
   - Email address
   - Store name
   - Password
3. Click **"Create Account"**
4. You're automatically logged in as Store Admin!

#### 2️⃣ Set Up Your Products
1. Go to **Products** menu
2. Click **"Create"** button
3. Fill in product details:
   - Name
   - SKU (product code)
   - Cost price (what you pay)
   - Selling price (what customers pay)
   - Reorder level (when to restock)
   - Upload product image (optional)
4. Click **"Save"**

#### 3️⃣ Add Opening Stock
1. Go to **Stock Movements**
2. Click **"Opening Balance"**
3. Enter quantity for each product
4. Set the date
5. Click **"Save"**

#### 4️⃣ Create Your First Sale
1. Go to **Sales Orders**
2. Click **"Create"**
3. Select customer (or create new)
4. Add products to order
5. Review total amount
6. Click **"Create Order"**
7. Print invoice if needed

### Daily Operations

#### Making a Sale
1. **Sales Orders** → **Create**
2. Choose customer
3. Add products
4. System auto-calculates total
5. Save order
6. Stock automatically reduces

#### Recording a Purchase
1. **Purchase Orders** → **Create**
2. Select supplier
3. Add products you're buying
4. Enter cost per item
5. Save order
6. Stock automatically increases

#### Tracking Expenses
1. **Operating Expenses** → **Record Expense**
2. Select category (rent, utilities, salaries, etc.)
3. Enter amount
4. Add description
5. Select payment method
6. Save

#### Checking Profit/Loss
1. Go to **Dashboard**
2. See today's profit at the top
3. View "Today's Financial Summary" card
4. Check "This Month's Profit and Loss Report"
5. Green = Profit, Red = Loss

#### Managing Stock
1. **Stock Movements** menu shows all options:
   - **Stock In**: Add new stock
   - **Stock Out**: Record stock used/sold
   - **Stock Correction**: Fix errors
   - **Reconciliation**: Verify actual vs system stock
   - **Simple Report**: View all stock changes

### Reports

#### View Sales Report
1. **Reports** → **Daily Sales**
2. Select date range
3. See sales summary and details

#### Check Stock Levels
1. **Reports** → **Stock Report**
2. See all products with current quantities
3. Red = Out of stock
4. Yellow = Low stock
5. Green = In stock

#### Profit & Loss Report
1. **Reports** → **Profit & Loss**
2. Select period (daily, monthly, yearly)
3. See income, expenses, and net profit

---

## 👥 User Roles Explained

### 🔵 Store Admin (You)
**Full Control** - Can do everything:
- ✅ Manage products, stock, and pricing
- ✅ Create sales and purchase orders
- ✅ Add/edit customers and suppliers
- ✅ Track all expenses
- ✅ View all reports
- ✅ Add team members
- ✅ Change system settings
- ✅ Access all features

### 🟢 Store User (Your Staff)
**Sales Focused** - Limited to sales activities:
- ✅ Create sales orders
- ✅ View products and prices
- ✅ View customers
- ✅ See sales dashboard
- ❌ Cannot view financial reports
- ❌ Cannot manage stock
- ❌ Cannot add products
- ❌ Cannot view expenses

### Adding Team Members
1. **Users** → **Create**
2. Enter their details
3. Select role (Store Admin or Store User)
4. Click **"Save"**
5. Give them their login credentials

---

## 💡 Tips & Best Practices

### Stock Management
- ✅ Set reorder levels for all products
- ✅ Check "Low Stock Alerts" daily
- ✅ Reconcile stock monthly
- ✅ Use stock corrections for adjustments
- ✅ Document reasons for stock changes

### Sales
- ✅ Always add customer information
- ✅ Print invoices for every sale
- ✅ Process returns properly through the system
- ✅ Review daily sales report

### Financial
- ✅ Record all expenses immediately
- ✅ Categorize expenses correctly
- ✅ Review profit/loss weekly
- ✅ Check cost prices are accurate
- ✅ Monitor profit margins

### Security
- ✅ Use strong passwords
- ✅ Don't share admin credentials
- ✅ Give staff only necessary permissions
- ✅ Regularly backup your data
- ✅ Log out when done

---

## 🏢 Multi-Store Feature

### How It Works
- Each store's data is **completely separate**
- Users can only see their own store's information
- Perfect for:
  - Multiple branch locations
  - Different businesses
  - Franchise operations
  - Department separation

### Creating Another Store
1. Register a new account
2. Enter new store name
3. New store is automatically created
4. Completely independent from other stores

---

## 📱 System Requirements

### Minimum Requirements
- Web browser (Chrome, Firefox, Safari, Edge)
- Internet connection (for cloud hosting)
- OR local server (for on-premise):
  - PHP 8.2 or higher
  - MySQL 8.0 or PostgreSQL 13+
  - Composer
  - Node.js & NPM

---

## ❓ Common Questions

**Q: Can I access this from my phone?**
A: Yes! The system is fully responsive and works on phones and tablets.

**Q: How do I backup my data?**
A: Contact your system administrator or export reports regularly.

**Q: Can I customize prices for different customers?**
A: Currently, all customers see the same selling price per product.

**Q: What if I make a mistake?**
A: Most actions can be corrected using stock corrections or returns.

**Q: How many products can I add?**
A: Unlimited! Add as many products as your business needs.

**Q: Can I see who made changes?**
A: Yes, the system tracks which user made each transaction.

**Q: Is my data safe?**
A: Yes, each store's data is isolated and secure. Only your users can access it.

---

## 🆘 Getting Help

### Built-in Help
- Hover over field labels for tooltips
- Check the **Dashboard** for quick stats
- Review **Reports** for detailed insights

### Documentation
- `IMPLEMENTATION_SUMMARY.md` - Technical overview
- `MULTI_TENANT_IMPLEMENTATION.md` - Multi-store details
- `STORE_ADMIN_ROLE.md` - Permissions guide
- `USER_MANAGEMENT.md` - Team management

### Support
- Contact your system administrator
- Review this README file
- Check the documentation files

---

## 🎯 Quick Reference

| Task | Where to Go |
|------|------------|
| Add product | Products → Create |
| Make sale | Sales Orders → Create |
| Buy stock | Purchase Orders → Create |
| Add expense | Operating Expenses → Record |
| Check profit | Dashboard or Reports → Profit & Loss |
| View stock | Products or Stock Movements |
| Add customer | Customers → Create |
| Add supplier | Suppliers → Create |
| Add user | Users → Create |
| Generate report | Reports menu |

---

## 📋 Module Overview

The system includes these main modules:

- **Dashboard** - Overview and key metrics
- **Products** - Product catalog management
- **Stock Movements** - Inventory tracking
- **Sales Orders** - Customer orders
- **Purchase Orders** - Supplier orders
- **Customers** - Customer database
- **Suppliers** - Supplier database
- **Operating Expenses** - Expense tracking
- **Reports** - Analytics and reports
- **Users** - Team member management
- **Point of Sale** - Quick checkout
- **Returns** - Sales & purchase returns
- **Settings** - Store configuration

---

## 📄 License

This project is open-source software licensed under the MIT license.

---

## 🎉 Ready to Get Started?

1. **Login** with demo credentials
2. **Explore** the dashboard
3. **Add** your first product
4. **Create** your first sale
5. **Check** your profit report

**Welcome to easier inventory management!** 🚀
