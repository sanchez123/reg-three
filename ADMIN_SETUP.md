# TIIR Party Registration System - Admin Setup Guide

## 📋 Overview
This is a complete admin management system for the TIIR Party Registration platform with full CRUD functionality for managing party members and candidates.

## 🚀 Quick Start

### 1. Database Setup

**Important:** You must import the database schema before the system can work.

1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Create a new database called `tiir_registration`
3. Import the `database-schema.sql` file:
   - Click on the `tiir_registration` database
   - Go to **Import** tab
   - Click **Choose File** and select `database-schema.sql`
   - Click **Import**

### 2. Admin Login Credentials

**Default Login:**
- **Username:** `admin`
- **Password:** `admin123`

**Login Page:** http://localhost/registration/login.php

### 3. System Features

#### Dashboard
- View statistics: Total Members, Candidates, Pending Approvals, Approved Members
- Quick access to member management
- System information display

#### Member Management
- ✅ **Add Members** - Add new members with complete form validation
- ✅ **View Members** - Display all members with filters and pagination
- ✅ **Edit Members** - Update member information and status
- ✅ **Delete Members** - Remove members with photo cleanup
- ✅ **Filters** - Filter by country, status, search by name/phone/email
- ✅ **Pagination** - Configurable entries per page

#### Candidate Management
- ✅ **View Candidates** - Display all registered candidates
- ✅ **Filters** - Same filtering options as members
- ✅ **Status Tracking** - Pending, Approved, Rejected

#### Audit Log
- Track all admin actions
- View who did what and when
- Maintain system integrity

---

## 🔒 Security Features

1. **Password Hashing** - All passwords use bcrypt hashing
2. **Session Management** - Secure session handling with login checks
3. **Input Validation** - All inputs are sanitized and validated
4. **SQL Injection Prevention** - Prepared statements used throughout
5. **XSS Protection** - HTML escaping on all outputs
6. **File Upload Security** - Files validated before upload
7. **File Size Limits** - Maximum 5MB for photos
8. **Audit Trail** - Complete action logging

---

## 📝 Form Validation

### Member Registration Form
✓ First Name - Required
✓ Mother's Name - Required
✓ Gender - Required (Male/Female)
✓ Date of Birth - Required, Must be 18+
✓ Place of Birth - Required
✓ Education Level - Required
✓ Occupation - Required
✓ Country - Required
✓ State/Region - Required
✓ District - Required
✓ Phone - Required, Valid format
✓ Email - Optional but must be valid if provided
✓ Photo - Optional, Image only (JPEG/PNG/GIF), Max 5MB
✓ Security Code - Required, Must match displayed code

### Member Edit Form
- All fields with same validation
- Can update photo (old one deleted automatically)
- Can change status (Pending/Approved/Rejected)

---

## 🎨 UI Color Scheme

The system uses colors from your custom CSS:
- **Primary Blue:** #083a9c, #2563eb
- **Danger Red:** #d73322
- **Success Green:** #16a34a
- **Warning Orange:** #fb923c
- **Backgrounds:** #f8fafc, white

---

## 📁 File Structure

```
registration/
├── admin/
│   ├── header.php          # Admin layout header
│   ├── footer.php          # Admin layout footer
│   ├── dashboard.php       # Main dashboard
│   ├── add-member.php      # Add new member form
│   ├── members-list.php    # View/manage members
│   ├── edit-member.php     # Edit member details
│   ├── candidates-list.php # View candidates
│   ├── audit-log.php       # Audit trail
│   └── logout.php          # Logout handler
├── uploads/
│   └── members/            # Member photo storage
├── inc/                    # Public templates
├── config.php              # Database configuration
├── login.php               # Admin login page
├── database-schema.sql     # Database structure
└── index.php               # Public homepage
```

---

## 🔄 Database Tables

### admin_users
Stores admin login credentials with security fields

### members
Complete member registration data with status tracking and audit info

### candidates
Candidate registration data with same structure as members

### audit_log
Logs all admin actions for security and compliance

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check if MySQL/WAMP is running
- Verify database credentials in `config.php`
- Ensure `tiir_registration` database exists

### "Member photo upload failed"
- Check if `uploads/members/` directory exists and is writable
- Verify file is a valid image format (JPEG, PNG, GIF)
- Check file size is under 5MB

### "Security code validation failed"
- Security code is regenerated on each page load
- Must match exactly as displayed
- Session must be active during form submission

### "Cannot edit/delete member"
- Verify admin is logged in
- Check admin session hasn't expired
- Ensure member ID exists in database

---

## 🔐 Changing Admin Password

To change the admin password:

1. Use a password hashing tool to hash your new password with bcrypt
2. Run this SQL query:
```sql
UPDATE admin_users SET password='[BCR_HASHED_PASSWORD]' WHERE username='admin';
```

Or access the password change through admin panel (if implemented).

---

## 📊 Creating Additional Admin Users

To add a new admin user, run:

```sql
INSERT INTO admin_users (username, email, password, full_name, status) 
VALUES ('newadmin', 'admin@example.com', '[BCR_HASHED_PASSWORD]', 'Admin Name', 'active');
```

Use an online bcrypt generator to hash passwords.

---

## 💡 Best Practices

1. **Regular Backups** - Backup database regularly
2. **Update Credentials** - Change default admin password immediately
3. **Monitor Audit Log** - Check for suspicious activities
4. **Clean Old Photos** - Periodically clean unused photo files
5. **Session Management** - Logout after session to prevent unauthorized access

---

## 📞 Support

For issues or questions, refer to:
- Code comments in each file
- Database structure in `database-schema.sql`
- Validation messages in form pages

---

## ✨ Features Summary

| Feature | Status |
|---------|--------|
| Admin Login | ✅ Complete |
| Admin Dashboard | ✅ Complete |
| Add Members | ✅ Complete |
| View Members | ✅ Complete |
| Edit Members | ✅ Complete |
| Delete Members | ✅ Complete |
| Member Filters | ✅ Complete |
| Member Pagination | ✅ Complete |
| Photo Upload | ✅ Complete |
| Form Validation | ✅ Complete |
| Status Management | ✅ Complete |
| Audit Logging | ✅ Complete |
| Security Features | ✅ Complete |
| Responsive Design | ✅ Complete |

---

**Last Updated:** May 29, 2026
**Version:** 1.0.0

