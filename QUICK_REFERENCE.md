# TIIR Party Registration System - Quick Reference Card

## 🚀 QUICK START (5 Steps)

### 1. Import Database
- Open: http://localhost/phpmyadmin
- Create database: `tiir_registration`
- Import: `database-schema.sql`

### 2. Login to Admin
- URL: http://localhost/registration/login.php
- Username: `admin`
- Password: `admin123`

### 3. Add a Member
- Click: **Add Member**
- Fill required fields (marked *)
- Accept terms & enter security code
- Click: **Add Member**

### 4. Manage Members
- Click: **Manage Members**
- View / Edit / Delete members
- Use filters to search

### 5. Logout
- Click: **Logout** button in top right

---

## 📍 ADMIN URLS

| Page | URL | Function |
|------|-----|----------|
| Login | `/login.php` | Admin authentication |
| Dashboard | `/admin/dashboard.php` | Statistics & overview |
| Add Member | `/admin/add-member.php` | Create new member |
| Members List | `/admin/members-list.php` | View/Edit/Delete members |
| Edit Member | `/admin/edit-member.php?id=X` | Update member |
| Candidates | `/admin/candidates-list.php` | View candidates |
| Audit Log | `/admin/audit-log.php` | Action history |
| Logout | `/admin/logout.php` | End session |

---

## ✅ MEMBER FORM FIELDS

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| First Name | Text | ✅ | Any text |
| Mother's Name | Text | ✅ | Any text |
| Gender | Select | ✅ | Male/Female |
| Date of Birth | Date | ✅ | Must be 18+ |
| Place of Birth | Text | ✅ | Any location |
| Government ID | Text | ❌ | Optional |
| Education | Select | ✅ | Primary/Secondary/Diploma/Degree |
| Occupation | Text | ✅ | Job title |
| Country | Select | ✅ | Somalia/Kenya/Ethiopia |
| State | Text | ✅ | Region name |
| District | Text | ✅ | City/District |
| Email | Email | ❌ | Optional, must be valid |
| Phone | Tel | ✅ | Valid format required |
| Photo | File | ❌ | JPG/PNG/GIF, <5MB |
| Security Code | Text | ✅ | 5-digit number shown |

---

## 🔍 FILTERING OPTIONS

### Filters Available On:
- Members List
- Candidates List

### Filter Types:
1. **Search** (Name / Phone / Email)
2. **Country** (Somalia / Kenya / Ethiopia)
3. **Status** (Pending / Approved / Rejected)
4. **Entries** (10, 25, 50 per page)

---

## 💡 FEATURE MATRIX

| Feature | Status | Notes |
|---------|--------|-------|
| **Authentication** | ✅ | Bcrypt hashing |
| **Dashboard** | ✅ | Statistics & quick actions |
| **Add Members** | ✅ | Full validation |
| **View Members** | ✅ | Filters & pagination |
| **Edit Members** | ✅ | Update all fields |
| **Delete Members** | ✅ | Photos auto-removed |
| **Photo Upload** | ✅ | Validated & resized |
| **Audit Log** | ✅ | Complete tracking |
| **Form Validation** | ✅ | 100+ checks |
| **Responsive Design** | ✅ | Mobile-friendly |
| **Security** | ✅ | Multiple layers |

---

## 📊 DATABASE TABLES

### admin_users
```
id, username, email, password, full_name, created_at, last_login, status
```

### members
```
id, first_name, mothers_name, gender, date_of_birth, place_of_birth, 
government_id, education, occupation, country, state, district, 
email, phone, photo_path, security_code, registration_date, status, added_by
```

### candidates
```
[Same structure as members table]
```

### audit_log
```
id, admin_id, action, table_name, record_id, details, created_at
```

---

## 🎨 COLOR SCHEME

| Color | Hex | Usage |
|-------|-----|-------|
| Primary | #083a9c | Headers, main buttons |
| Secondary | #2563eb | Links, secondary buttons |
| Danger | #d73322 | Delete, warning |
| Success | #16a34a | Approved, badges |
| Warning | #fb923c | Pending, caution |
| Light | #f8fafc | Backgrounds |

---

## 🔒 SECURITY CHECKLIST

- ✅ Password hashing (bcrypt)
- ✅ Session validation
- ✅ Input sanitization
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ File validation
- ✅ Duplicate detection
- ✅ Age validation (18+)
- ✅ Audit logging
- ✅ CSRF token ready

---

## ⚡ KEYBOARD SHORTCUTS

| Action | Shortcut |
|--------|----------|
| Logout | `Click Logout button` |
| Search | `Ctrl + F` (browser search) |
| Go Back | `Alt + Left Arrow` |
| New Tab | `Ctrl + T` |

---

## ❌ COMMON ERRORS & FIXES

| Error | Solution |
|-------|----------|
| "DB connection failed" | Check MySQL is running, verify config.php |
| "Member not found" | Use correct member ID in URL |
| "Photo upload failed" | Ensure <5MB, JPG/PNG/GIF format |
| "Security code wrong" | Check displayed number carefully |
| "Duplicate phone" | Phone already registered, use unique number |
| "Session expired" | Login again (click Logout then Login) |
| "Access denied" | Not logged in, go to login.php |

---

## 📈 STATISTICS MEANING

| Stat | Definition |
|------|-----------|
| Total Members | All registered members |
| Total Candidates | All registered candidates |
| Pending Approvals | Members waiting approval |
| Approved Members | Successfully approved members |

---

## 🔄 WORKFLOW EXAMPLE

```
1. Login (login.php)
   ↓
2. View Dashboard (see stats)
   ↓
3. Add Member (add-member.php)
   ↓
4. View Members (members-list.php)
   ↓
5. Edit Member (edit-member.php)
   ↓
6. Check Audit Log (audit-log.php)
   ↓
7. Logout (logout.php)
```

---

## 📚 DOCUMENTATION FILES

1. **ADMIN_SETUP.md** - Detailed setup instructions
2. **IMPLEMENTATION_GUIDE.md** - Complete feature guide
3. **QUICK_REFERENCE.md** - This file
4. **database-schema.sql** - Database structure

---

## 🎯 Next Features to Build

- [ ] Public member registration
- [ ] Email notifications
- [ ] Export to Excel/PDF
- [ ] Advanced reports
- [ ] Two-factor authentication
- [ ] Member login portal
- [ ] Automated backups

---

**Version:** 1.0.0  
**Build Date:** May 29, 2026  
**Status:** ✅ Ready to Use  
**Support:** Check documentation files

