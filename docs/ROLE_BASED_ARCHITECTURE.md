# Role-Based Architecture Implementation

## 🎯 **Overview**

This document outlines the **User Role-Based Organization** architecture for the Guest Manager Laravel application with three distinct user roles:

- **👑 Admin** - Full system access and management
- **📋 Organizer** - Event and guest list management
- **📱 Scanner** - Guest check-in and event scanning

## 🏗️ **Folder Structure**

```
app/
├── Admin/                    # Admin role functionality
│   ├── Controllers/
│   │   └── AdminController.php
│   ├── Services/
│   │   └── AdminService.php
│   ├── Policies/
│   └── Views/
│
├── Organizer/               # Organizer role functionality
│   ├── Controllers/
│   │   └── OrganizerController.php
│   ├── Services/
│   │   └── OrganizerService.php
│   ├── Policies/
│   └── Views/
│
├── Scanner/                 # Scanner role functionality
│   ├── Controllers/
│   │   └── ScannerController.php
│   ├── Services/
│   │   └── ScannerService.php
│   ├── Policies/
│   └── Views/
│
├── Shared/                  # Shared components
│   ├── Models/
│   │   ├── User.php
│   │   ├── GuestList.php
│   │   ├── Guest.php
│   │   └── GuestGroup.php
│   ├── Services/
│   │   ├── NotificationService.php
│   │   └── FileService.php
│   ├── Middleware/
│   │   ├── AdminMiddleware.php
│   │   ├── OrganizerMiddleware.php
│   │   └── ScannerMiddleware.php
│   └── Traits/
│       ├── HasPermissions.php
│       └── Searchable.php
│
└── Http/
    ├── Controllers/
    ├── Middleware/
    └── Requests/
```

## 👑 **Admin Role**

### **Responsibilities**
- System-wide user management
- System settings and configuration
- Reports and analytics
- Event oversight

### **Key Features**
```php
// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);           // Dashboard
    Route::get('/users', [AdminController::class, 'userManagement']); // User management
    Route::get('/settings', [AdminController::class, 'systemSettings']); // System settings
    Route::get('/reports', [AdminController::class, 'reports']);      // Reports
});
```

### **Admin Capabilities**
- ✅ View all users and their statistics
- ✅ Create, update, and delete users
- ✅ Assign roles (admin, organizer, scanner)
- ✅ Configure system settings
- ✅ View comprehensive reports
- ✅ Export data and analytics
- ✅ Monitor system usage

## 📋 **Organizer Role**

### **Responsibilities**
- Create and manage guest lists
- Add, edit, and remove guests
- Import/export guest data
- Event planning and management

### **Key Features**
```php
// Organizer Routes
Route::middleware(['auth', 'organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/', [OrganizerController::class, 'dashboard']);           // Dashboard
    Route::resource('guest-lists', OrganizerController::class);           // Guest lists
    Route::post('/guest-lists/{guestList}/import', [OrganizerController::class, 'importGuests']); // Import
    Route::get('/guest-lists/{guestList}/export', [OrganizerController::class, 'exportGuests']);  // Export
});
```

### **Organizer Capabilities**
- ✅ Create and manage guest lists
- ✅ Add, edit, and delete guests
- ✅ Import guests from Excel/CSV files
- ✅ Export guest lists
- ✅ Organize guests by groups
- ✅ View guest statistics
- ✅ Generate event reports

## 📱 **Scanner Role**

### **Responsibilities**
- Check-in guests at events
- Scan QR codes or barcodes
- Search for guests manually
- View check-in history

### **Key Features**
```php
// Scanner Routes
Route::middleware(['auth', 'scanner'])->prefix('scanner')->name('scanner.')->group(function () {
    Route::get('/', [ScannerController::class, 'dashboard']);                    // Dashboard
    Route::get('/events', [ScannerController::class, 'availableEvents']);        // Available events
    Route::get('/events/{guestList}/scan', [ScannerController::class, 'scanEvent']); // Scan interface
    Route::post('/events/{guestList}/scan', [ScannerController::class, 'scanGuest']); // Process scan
    Route::post('/events/{guestList}/checkin', [ScannerController::class, 'manualCheckIn']); // Manual check-in
});
```

### **Scanner Capabilities**
- ✅ View available events for scanning
- ✅ Scan QR codes or barcodes
- ✅ Manual guest search and check-in
- ✅ View guest details
- ✅ Check-in history
- ✅ Offline mode for events
- ✅ Scanner settings configuration

## 🔐 **Authorization & Middleware**

### **Role Hierarchy**
```
Admin > Organizer > Scanner
```

### **Middleware Implementation**
```php
// AdminMiddleware.php
if ($user->role !== 'admin') {
    abort(403, 'Admin access required.');
}

// OrganizerMiddleware.php
if (!in_array($user->role, ['admin', 'organizer'])) {
    abort(403, 'Organizer access required.');
}

// ScannerMiddleware.php
if (!in_array($user->role, ['admin', 'organizer', 'scanner'])) {
    abort(403, 'Scanner access required.');
}
```

### **Gate Policies**
```php
// Example policies for different actions
Gate::define('admin-access', function (User $user) {
    return $user->role === 'admin';
});

Gate::define('organizer-access', function (User $user) {
    return in_array($user->role, ['admin', 'organizer']);
});

Gate::define('scanner-access', function (User $user) {
    return in_array($user->role, ['admin', 'organizer', 'scanner']);
});
```

## 🛣️ **Route Organization**

### **Separate Route Files**
```
routes/
├── web.php          # Main routes (login, register, etc.)
├── admin.php        # Admin-specific routes
├── organizer.php    # Organizer-specific routes
└── scanner.php      # Scanner-specific routes
```

### **Route Registration**
```php
// bootstrap/app.php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    admin: __DIR__.'/../routes/admin.php',
    organizer: __DIR__.'/../routes/organizer.php',
    scanner: __DIR__.'/../routes/scanner.php',
)
```

## 🎨 **View Organization**

### **Role-Based Views**
```
resources/views/
├── admin/
│   ├── dashboard.blade.php
│   ├── user-management.blade.php
│   ├── system-settings.blade.php
│   └── reports.blade.php
│
├── organizer/
│   ├── dashboard.blade.php
│   ├── guest-lists/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── show.blade.php
│   │   └── edit.blade.php
│   └── reports.blade.php
│
├── scanner/
│   ├── dashboard.blade.php
│   ├── events/
│   │   ├── index.blade.php
│   │   ├── scan.blade.php
│   │   └── history.blade.php
│   ├── guests/
│   │   └── details.blade.php
│   └── settings.blade.php
│
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   ├── organizer.blade.php
│   └── scanner.blade.php
│
└── components/
    ├── ui/
    └── forms/
```

## 📦 **Service Layer**

### **Role-Specific Services**
```php
// AdminService.php
class AdminService
{
    public function getDashboardStats(): array
    public function getAllUsers()
    public function updateUser(User $user, array $data): User
    public function getSystemSettings(): array
    public function getReports(): array
}

// OrganizerService.php
class OrganizerService
{
    public function getDashboardStats(): array
    public function getMyGuestLists()
    public function createGuestList(array $data): GuestList
    public function importGuests(GuestList $guestList, $file): array
    public function exportGuests(GuestList $guestList)
}

// ScannerService.php
class ScannerService
{
    public function getDashboardStats(): array
    public function getAvailableEvents()
    public function scanGuest(GuestList $guestList, string $identifier): array
    public function manualCheckIn(GuestList $guestList, int $guestId, ?string $notes): array
    public function getCheckInHistory(GuestList $guestList)
}
```

## 🗄️ **Database Schema**

### **User Role Field**
```php
// Migration for adding role to users table
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['admin', 'organizer', 'scanner'])->default('scanner');
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
});
```

### **Check-in Tracking**
```php
// Migration for check-ins table
Schema::create('check_ins', function (Blueprint $table) {
    $table->id();
    $table->foreignId('guest_id')->constrained()->onDelete('cascade');
    $table->foreignId('guest_list_id')->constrained()->onDelete('cascade');
    $table->foreignId('scanned_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('checked_in_at');
    $table->string('check_in_method')->default('scan'); // scan, manual
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

## 🧪 **Testing Strategy**

### **Test Organization**
```
tests/
├── Feature/
│   ├── Admin/
│   │   ├── UserManagementTest.php
│   │   ├── SystemSettingsTest.php
│   │   └── ReportsTest.php
│   ├── Organizer/
│   │   ├── GuestListTest.php
│   │   ├── GuestManagementTest.php
│   │   └── ImportExportTest.php
│   └── Scanner/
│       ├── CheckInTest.php
│       ├── ScanTest.php
│       └── OfflineModeTest.php
│
├── Unit/
│   ├── Admin/
│   │   └── AdminServiceTest.php
│   ├── Organizer/
│   │   └── OrganizerServiceTest.php
│   └── Scanner/
│       └── ScannerServiceTest.php
│
└── Integration/
    ├── RoleAccessTest.php
    └── CrossRolePermissionsTest.php
```

## 🚀 **Implementation Steps**

### **Phase 1: Foundation**
1. ✅ Create role-based folder structure
2. ✅ Implement middleware for each role
3. ✅ Update User model with role field
4. ✅ Create base controllers and services

### **Phase 2: Admin Features**
1. ✅ Implement AdminController and AdminService
2. ✅ Create admin routes and views
3. ✅ Add user management functionality
4. ✅ Implement system settings

### **Phase 3: Organizer Features**
1. ✅ Implement OrganizerController and OrganizerService
2. ✅ Create organizer routes and views
3. ✅ Add guest list management
4. ✅ Implement import/export functionality

### **Phase 4: Scanner Features**
1. ✅ Implement ScannerController and ScannerService
2. ✅ Create scanner routes and views
3. ✅ Add check-in functionality
4. ✅ Implement offline mode

### **Phase 5: Integration**
1. ✅ Test role-based access control
2. ✅ Implement cross-role permissions
3. ✅ Add comprehensive testing
4. ✅ Performance optimization

## 📋 **Benefits of This Architecture**

### **✅ Scalability**
- Easy to add new roles
- Clear separation of concerns
- Modular structure

### **✅ Security**
- Role-based access control
- Granular permissions
- Secure data isolation

### **✅ Maintainability**
- Clear code organization
- Easy to find and modify features
- Reduced complexity

### **✅ Team Collaboration**
- Different teams can work on different roles
- Clear ownership boundaries
- Reduced merge conflicts

### **✅ User Experience**
- Role-specific interfaces
- Optimized workflows
- Intuitive navigation

This architecture provides a solid foundation for a scalable guest management system with clear role separation and comprehensive functionality for each user type.