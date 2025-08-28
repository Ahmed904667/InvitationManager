# 🎨 Hero Section Component

## 📁 File Location
```
resources/views/components/hero-section.blade.php
```

## 🚀 Usage

### Basic Usage
```php
@include('components.hero-section')
```

### In a Blade Template
```php
@extends('layouts.app')

@section('content')
    @include('components.hero-section')
    
    <!-- Other content -->
@endsection
```

## ✨ Features

### 🎯 **Core Features**
- **Responsive Design**: Mobile-first approach with breakpoints
- **Dark Mode Support**: Automatic theme switching
- **Animated Elements**: Floating blob animations
- **Gradient Text**: Animated gradient text effect
- **Interactive Buttons**: Hover effects and transitions

### 🎨 **Visual Elements**

#### **Hero Container**
- Full viewport height (`min-height: 100vh`)
- Gradient background with theme support
- Overflow hidden for blob containment

#### **Title & Text**
- **Responsive Typography**: `clamp(2.5rem, 5vw, 4rem)`
- **Gradient Text**: "Guest Management" with animated gradient
- **Theme-aware Colors**: Uses CSS custom properties

#### **Animated Blobs**
- **3 Floating Elements**: Blue, Purple, and Pink
- **7-second Animation**: Infinite loop with different delays
- **Blur Effect**: `filter: blur(40px)` for soft appearance
- **Mix-blend-mode**: Multiply for color interaction

#### **Interactive Buttons**
- **Primary Button**: Gradient background with hover effects
- **Secondary Button**: Outline style with hover states
- **Responsive Layout**: Stack on mobile, side-by-side on desktop

## 🎭 **Animations**

### **Blob Animation**
```css
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
    33% { transform: translate(30px, -50px) scale(1.1) rotate(120deg); }
    66% { transform: translate(-20px, 20px) scale(0.9) rotate(240deg); }
    100% { transform: translate(0px, 0px) scale(1) rotate(360deg); }
}
```

### **Gradient Text Animation**
```css
@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}
```

## 🌙 **Theme Support**

### **Light Mode**
- Background: Blue to white to purple gradient
- Text: Dark colors for readability
- Blobs: Soft pastel colors

### **Dark Mode**
- Background: Dark blue to navy gradient
- Text: Light colors for contrast
- Blobs: Deeper, more saturated colors

## 📱 **Responsive Breakpoints**

### **Desktop (Default)**
- Title: 4rem (64px)
- Description: 1.25rem (20px)
- Buttons: Side-by-side layout
- Blobs: 18rem (288px)

### **Tablet (768px and below)**
- Title: 2.5rem (40px)
- Description: 1.125rem (18px)
- Buttons: Stacked layout
- Blobs: 12rem (192px)

### **Mobile (480px and below)**
- Title: 2rem (32px)
- Description: 1rem (16px)
- Buttons: Compact size
- Blobs: 8rem (128px)

## ♿ **Accessibility Features**

### **Focus States**
- Visible focus outlines on buttons
- High contrast colors for focus indicators

### **Reduced Motion**
- Respects `prefers-reduced-motion` media query
- Disables animations for users who prefer less motion

### **High Contrast**
- Supports `prefers-contrast: high` media query
- Adjusts opacity and borders for better visibility

### **Print Styles**
- Optimized for printing
- Removes decorative elements
- Ensures text readability

## 🎨 **Customization**

### **Colors**
The component uses CSS custom properties defined in your main CSS file:
```css
:root {
    --primary-500: #8b2bfa;
    --primary-600: #7c3aed;
    --primary-700: #6d28d9;
    --text-primary: #111827;
    --text-secondary: #6b7280;
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
}
```

### **Content**
To modify the content, edit the HTML within the component:
- **Title**: Change the text in the `<h1>` element
- **Description**: Update the paragraph text
- **Buttons**: Modify button text and links

### **Styling**
All styles are contained within the component's `<style>` tag:
- **Layout**: Modify container and spacing
- **Typography**: Adjust font sizes and weights
- **Animations**: Customize timing and effects
- **Colors**: Update gradient and color values

## 🔧 **Technical Details**

### **Dependencies**
- **Tailwind CSS**: For utility classes
- **CSS Custom Properties**: For theme support
- **Blade Templating**: For dynamic content

### **Browser Support**
- **Modern Browsers**: Full support for all features
- **CSS Grid**: Used for responsive layouts
- **CSS Animations**: For blob and text effects
- **CSS Custom Properties**: For theme switching

### **Performance**
- **Optimized Animations**: Uses `transform` and `opacity` for smooth performance
- **Hardware Acceleration**: Animations trigger GPU acceleration
- **Efficient Selectors**: Minimal CSS specificity for better performance

## 📝 **Example Implementation**

### **In a Landing Page**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('components.hero-section')
    
    <!-- Additional content -->
    <section id="features">
        <!-- Features section -->
    </section>
</body>
</html>
```

### **With Navigation**
```php
@extends('layouts.app')

@section('content')
    <!-- Navigation -->
    <nav class="nav-bg">
        <!-- Navigation content -->
    </nav>
    
    <!-- Hero Section -->
    @include('components.hero-section')
    
    <!-- Other sections -->
@endsection
```

## 🎯 **Best Practices**

1. **Content**: Keep title concise and impactful
2. **Performance**: Component is self-contained for optimal loading
3. **Accessibility**: Always test with screen readers and keyboard navigation
4. **Responsive**: Test across different device sizes
5. **Theming**: Ensure contrast ratios meet WCAG guidelines

## 🔄 **Updates & Maintenance**

The component is designed to be:
- **Self-contained**: All styles included
- **Maintainable**: Clear structure and comments
- **Extensible**: Easy to modify and extend
- **Reusable**: Can be used across different pages 