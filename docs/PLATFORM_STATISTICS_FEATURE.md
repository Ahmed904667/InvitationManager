# Platform Statistics Feature

## Overview
This feature adds dynamic platform statistics to the landing page, displaying real-time data about the platform's usage including events created, invitations sent, guests managed, and active users.

## Components Added

### 1. PlatformStatsService (`app/Services/PlatformStatsService.php`)
- Calculates comprehensive platform statistics
- Formats numbers for display (e.g., 1.2K, 1.5M)
- Provides methods for different time periods
- Returns both raw and formatted statistics

### 2. LandingController (`app/Http/Controllers/LandingController.php`)
- Handles landing page display with statistics
- Provides API endpoints for statistics
- Manages authentication redirects

### 3. API Endpoints
- `GET /api/stats` - Returns formatted statistics for display
- `GET /api/stats/detailed` - Returns raw statistics data
- `GET /api/stats/period?period=month` - Returns statistics for specific time periods

### 4. Landing Page Updates
- Added statistics section between hero and features
- Responsive design with animated counters
- Real-time data display with fallback values

### 5. CSS Styling (`resources/css/landing.css`)
- Statistics section styling
- Hover effects and animations
- Responsive design for mobile devices
- Gradient text effects for numbers

### 6. JavaScript Animations (`resources/js/landing.js`)
- Intersection Observer for scroll-triggered animations
- Animated counter effects with easing
- Optional periodic statistics refresh

## Statistics Displayed

### Main Statistics
- **Total Events Created** - All events in the system
- **Total Invitations Sent** - Successfully sent invitations
- **Total Guests Managed** - All guests across all events
- **Active Users** - Total registered users

### Additional Statistics
- **Events This Month** - Events created in current month
- **Invitations This Month** - Invitations sent in current month
- **Trial Requests** - Total trial requests received

## Features

### Dynamic Data
- Statistics are calculated in real-time from the database
- Numbers are formatted for better readability (K, M suffixes)
- Fallback values ensure the page loads even if data is unavailable

### Animations
- Counters animate when scrolled into view
- Smooth easing animations with staggered delays
- Hover effects on statistic cards

### Responsive Design
- Mobile-optimized layout
- Adaptive grid system
- Touch-friendly interactions

### Performance
- Statistics are cached during page load
- API endpoints for dynamic updates
- Optimized database queries

## Usage

### For Developers
```php
// Get formatted statistics
$statsService = new PlatformStatsService();
$stats = $statsService->getFormattedStats();

// Get raw statistics
$rawStats = $statsService->getPlatformStats();

// Get statistics for specific period
$monthlyStats = $statsService->getStatsForPeriod('month');
```

### For Frontend
```javascript
// Fetch statistics via API
fetch('/api/stats')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatsDisplay(data.data);
        }
    });
```

## Customization

### Adding New Statistics
1. Add method to `PlatformStatsService`
2. Update `getPlatformStats()` method
3. Add display element to landing page
4. Update CSS if needed

### Modifying Animations
- Adjust timing in `landing.js`
- Modify CSS animations in `landing.css`
- Change intersection observer settings

### Styling Changes
- Update CSS variables for colors
- Modify card layouts in `.stat-card`
- Adjust responsive breakpoints

## Database Dependencies
- `events` table
- `invitations` table
- `guests` table
- `users` table
- `trials` table

## Browser Support
- Modern browsers with Intersection Observer support
- Graceful degradation for older browsers
- Mobile-responsive design
