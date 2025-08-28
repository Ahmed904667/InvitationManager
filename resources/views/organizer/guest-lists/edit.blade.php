@extends('layouts.organizer')

@section('title', $guestList->name)

@push('styles')
<style>
/* --- Toolbar and Main Content Responsive Styles --- */

/* Floating toolbar (desktop) and bottom toolbar (mobile) visibility */
@media (min-width: 891px) {
  .bottom-toolbar-891 {
    display: none !important;
  }
  .side-toolbar-891 {
    display: flex !important;
  }
}
@media (max-width: 890px) {
  .side-toolbar-891 {
    display: none !important;
  }
}

/* Main content width rules */
.main-content-1149 {
  max-width: 773px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 2rem;
  padding-right: 2rem;
}
@media (min-width: 1024px) and (max-width: 1148.98px) {
  .main-content-1149 {
    max-width: 768px !important;
  }
}

/* --- Custom Animations and Styles --- */
.toolbar-button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toolbar-button:hover .button-bg {
  transform: scale(1.05);
}
.toolbar-button:active .button-bg {
  transform: scale(0.95);
}
.expand-text {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateX(10px);
}
.toolbar-button:hover .expand-text,
.toolbar-button:focus .expand-text {
  transform: translateX(0);
}
.dropdown-menu {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateX(10px) scale(0.95);
  opacity: 0;
  pointer-events: none;
}
.dropdown-menu.show {
  transform: translateX(0) scale(1);
  opacity: 1;
  pointer-events: auto;
}
.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}
.icon-glow {
  filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.3));
}
.floating-toolbar {
  animation: float 6s ease-in-out infinite;
}
.expand-all .toolbar-option .toolbar-button {
  width: 208px !important;
}
.expand-all .toolbar-option .expand-text {
  opacity: 1 !important;
  transform: translateX(0) !important;
}
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-3px); }
}
.rotate-180 {
  transform: rotate(180deg);
}
.ddm-hover-bg:hover {
  background: var(--bg-secondary);
}
</style>
@stack('styles')


@section('content')
<div class="container mx-auto px-4 py-8 main-content-1149">
   <!-- Improved Slim Toolbar (Right) -->
   <div class="fixed top-1/4 right-4 z-50 h-auto flex flex-col items-center floating-toolbar side-toolbar-891" style="display: none;">
        <!-- Enhanced Toolbar background with glassmorphism -->
        <div class="absolute top-0 right-0 h-full w-14 glass-effect  shadow-2xl rounded-3xl ring-1 ring-black/5" style="background-color: var(--bg-tertiary);"></div>
        
        <!-- Expand All Toggle Button -->
        <div class="group relative h-12 mb-1">
            <button id="expandAllBtn" class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 overflow-hidden z-20 focus:outline-none focus:ring-2 "
                onclick="toggleExpandAll()" 
                title="Expand All Options"
                style="background: var(--bg-tertiary);"
                aria-label="Toggle expand all options">
                <div class="button-g h-12 glass-effect border  rounded-2xl transition-all duration-300" style="background: var(--bg-tertiary);"></div>
                <div class="absolute inset-0 flex items-center px-3">
                    <svg id="expandIcon" class="w-6 h-6 text-indigo-600 flex-shrink-0 transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="expandIconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="expand-text ml-3 text-sm text-indigo-700 font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap">
                        <span id="expandText">Expand All</span>
                    </span>
                </div>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="relative flex flex-col gap-2 p-2">
            <!-- Selection Mode -->
            <div class="toolbar-option group relative h-12">
                <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-52 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 border-2 border-transparent focus:border-blue-500"
                    onclick="toggleSelectionMode()" 
                    title="Selection Mode"
                    aria-label="Toggle selection mode"
                    onfocus="this.style.width='3rem'" onblur="this.style.width=''">
                    <!-- Toolbar button background -->
                    <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-5xl focus:shadow-5xl" style="background: var(--bg-tertiary);"></div>
                    <div class="absolute inset-0 flex items-center px-3">
                        <svg class="w-6 h-6 flex-shrink-0 icon-glow transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">Selection Mode</span>
                    </div>
                </button>
            </div>

            <!-- Add Group (dynamic) -->
            <div id="toolbarAddGroup"></div>

            <!-- Add Guest (static) -->
            <div class="toolbar-option group relative h-12">
                <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-48 focus:w-48 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-purple-500/50"
                    onclick="showAddGuestModal()" 
                    title="Add Guest"
                    aria-label="Add new guest">
                    <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-xl focus:shadow-xl" style="background: var(--bg-tertiary);"></div>
                    <div class="absolute inset-0 flex items-center px-3">
                        <svg class="w-6 h-6 flex-shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #a78bfa;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">Add Guest</span>
                    </div>
                </button>
            </div>

            <!-- Import Dropdown -->
            <div class="toolbar-option group relative h-12">
                <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-44 focus:w-44 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-yellow-500/50"
                    onclick="toggleImportMenu(event)" 
                    title="Import Data"
                    aria-label="Import data from various sources">
                    <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-xl focus:shadow-xl" style="background: var(--bg-tertiary);"></div>
                    <div class="absolute inset-0 flex items-center px-3">
                        <svg class="w-6 h-6 flex-shrink-0 transition-transform group-hover:scale-110 group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #eab308;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">Import</span>
                    </div>
                </button>
                
                <!-- Enhanced Import Dropdown -->
                <div id="importMenu" class="dropdown-menu absolute left-auto right-0 top-full mt-2 w-64 glass-effect border border-gray-200 rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden opacity-0 pointer-events-none scale-95 transition-all duration-200 z-20 group-[.show]:opacity-100 group-[.show]:pointer-events-auto group-[.show]:scale-100" style="background: var(--bg-tertiary);">
                    <!-- Improved Caret/arrow -->
                    <div class="absolute -top-3 right-8 w-8 h-8 flex items-center justify-center z-10">
                        <div class="w-5 h-5 glass-effect border border-gray-200 shadow-lg rotate-45" style="background: var(--bg-primary);"></div>
                    </div>
                    <div class="p-1">
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="showGoogleContactsModal()">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Google Contacts</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Import from your Google account</div>
                            </div>
                        </button>
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="showGoogleSheetsModal()">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Google Sheets</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Import from spreadsheet</div>
                            </div>
                        </button>
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="showImportModal()">
                            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Excel/CSV</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Upload local files</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Export Dropdown -->
            <div class="toolbar-option group relative h-12">
                <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-44 focus:w-44 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-red-500/50"
                    onclick="toggleExportMenu(event)" 
                    title="Export Data"
                    aria-label="Export data to various formats">
                    <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-xl focus:shadow-xl" style="background: var(--bg-tertiary);"></div>
                    <div class="absolute inset-0 flex items-center px-3">
                        <svg class="w-6 h-6 flex-shrink-0 transition-transform group-hover:scale-110 group-hover:translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ef4444;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">Export</span>
                    </div>
                </button>
                
                <!-- Enhanced Export Dropdown -->
                <div id="exportMenu" class="dropdown-menu absolute left-auto right-0 top-full mt-2 w-48 glass-effect border border-gray-200 rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden opacity-0 pointer-events-none scale-95 transition-all duration-200 z-20 group-[.show]:opacity-100 group-[.show]:pointer-events-auto group-[.show]:scale-100" style="background: var(--bg-tertiary);">
                    <!-- Improved Caret/arrow -->
                    <div class="absolute -top-3 right-8 w-8 h-8 flex items-center justify-center z-10">
                        <div class="w-5 h-5 glass-effect border border-gray-200 shadow-lg rotate-45" style="background: var(--bg-primary);"></div>
                    </div>
                    <div class="p-1">
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="exportToCSV()">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Export CSV</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Spreadsheet format</div>
                            </div>
                        </button>
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="exportToExcel()">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Export Excel</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Excel format</div>
                            </div>
                        </button>
                        <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 group ddm-hover-bg" onclick="exportToPDF()">
                            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium" style="color: var(--text-primary);">Export PDF</div>
                                <div class="text-xs" style="color: var(--text-secondary);">Document format</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            <!-- List Settings Button (bottom) -->
            <div class="toolbar-option group relative h-12 mt-auto">
                <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-48 focus:w-48 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-gray-500/50"
                    onclick="showListSettingsModal()"
                    title="List Settings"
                    aria-label="List Settings">
                    <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-xl focus:shadow-xl" style="background: var(--bg-tertiary);"></div>
                    <div class="absolute inset-0 flex items-center px-3">
                        <svg class="w-6 h-6 flex-shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            <circle cx="12" cy="12" r="9" stroke-width="2.5"/>
                        </svg>
                        <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">List Settings</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Import Dropdown -->
    <div id="mobileImportDropdown" class="fixed bottom-16 left-0 right-0 z-50 flex justify-center md:hidden hidden" onclick="closeMobileDropdowns()">
        <div class="w-full max-w-xs bg-white rounded-2xl shadow-2xl p-2 flex flex-col gap-1 border border-gray-200" style="background: var(--bg-primary); border-color: var(--border-primary);" onclick="event.stopPropagation()">
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="showGoogleContactsModal(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Google Contacts</span>
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="importFromGoogleSheets(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Google Sheets</span>
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="showImportModal(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Excel/CSV</span>
            </button>
        </div>
    </div>
    <!-- Mobile Export Dropdown -->
    <div id="mobileExportDropdown" class="fixed bottom-16 left-0 right-0 z-50 flex justify-center md:hidden hidden" onclick="closeMobileDropdowns()">
        <div class="w-full max-w-xs bg-white rounded-2xl shadow-2xl p-2 flex flex-col gap-1 border border-gray-200" style="background: var(--bg-primary); border-color: var(--border-primary);" onclick="event.stopPropagation()">
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="exportToCSV(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Export CSV</span>
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="showExportModal(); exportToExcel(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Export Excel</span>
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-150 ddm-hover-bg" onclick="exportToPDF(); closeMobileDropdowns();">
                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" style="color: var(--text-primary);">Export PDF</span>
            </button>
        </div>
    </div>

    <!-- Mobile Bottom Toolbar (md:hidden) -->
    <div class="fixed bottom-0 left-0 right-0 z-50 flex justify-around items-center py-2 shadow-2xl overflow-x-auto bottom-toolbar-891"
         style="background: var(--bg-primary); border-top: 1px solid var(--border-primary);">
        <button class="flex flex-col items-center focus:outline-none" onclick="toggleSelectionMode()">
            <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-xs" style="color: var(--text-primary);">Select</span>
        </button>
        <button class="flex flex-col items-center focus:outline-none" onclick="showModal('addGroupModal')">
            <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success-600);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="text-xs" style="color: var(--text-primary);">Add Group</span>
        </button>
        <button class="flex flex-col items-center focus:outline-none" onclick="showAddGuestModal()">
            <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="text-xs" style="color: var(--text-primary);">Add Guest</span>
        </button>
        <button class="flex flex-col items-center focus:outline-none" onclick="toggleMobileImportDropdown(event)">
            <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success-600);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
            </svg>
            <span class="text-xs" style="color: var(--text-primary);">Import</span>
        </button>
        <button class="flex flex-col items-center focus:outline-none" onclick="toggleMobileExportDropdown(event)">
            <svg class="w-7 h-7 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-xs" style="color: var(--text-primary);">Export</span>
        </button>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('organizer.guest-lists.index') }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 id="guestListName" class="text-3xl font-bold text-primary">{{ $guestList->name }}</h1>
                    <p id="guestListDescription" class="text-gray-600 mt-1">{{ $guestList->description }}</p>
                </div>
            </div>
            @if($guestList->event_date)
            <div class="mt-4 flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($guestList->event_date)->format('F j, Y') }}
                    @if($guestList->event_time)
                        at {{ \Carbon\Carbon::parse($guestList->event_time)->format('g:i A') }}
                    @endif
                </div>
                @if($guestList->event_location)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $guestList->event_location }}
                </div>
                @endif
            </div>
            @endif
        </div>
        <div class="flex space-x-3">
            <!-- <button class="btn-secondary" onclick="showImportModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                Import
            </button>
            <button class="btn-secondary" onclick="exportGuests()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
            <button class="btn-primary" onclick="showAddGuestModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Guest
            </button> -->
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $guestList->guests->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--success-500);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #fff;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Groups Count</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);"><span id="groupsCount"></span></p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--yellow-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--yellow-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Pending</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $guestList->guests->where('checked_in_at', null)->count() }}</p>
                </div>
            </div>
        </div>
                <div class="rounded-xl shadow-sm border p-6 cursor-pointer transition-all duration-200 hover:shadow-md active:scale-95" 
             style="background: var(--bg-primary); border-color: var(--border-primary);" 
             onclick="showValidationDetails()">
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1 min-w-0">
                    <div class="flex-shrink-0 p-3 rounded-xl" id="healthIconContainer">
                        @if(isset($errorSummary) && ($errorSummary['total_errors'] > 0 || $errorSummary['total_warnings'] > 0))
                            @if($errorSummary['total_errors'] > 0)
                                <div class="p-2 rounded-lg bg-red-100">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="p-2 rounded-lg bg-orange-100">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            @endif
                        @else
                            <div class="p-2 rounded-lg bg-green-100">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">List Health</p>
                        <div class="flex items-center space-x-2" data-error-display>
                            @if(isset($errorSummary) && ($errorSummary['total_errors'] > 0 || $errorSummary['total_warnings'] > 0))
                                @if($errorSummary['total_errors'] > 0)
                                    <span class="text-xl font-bold text-red-600">{{ $errorSummary['total_errors'] }}</span>
                                    <span class="text-sm font-medium text-red-600">errors</span>
                                @endif
                                @if($errorSummary['total_warnings'] > 0)
                                    @if($errorSummary['total_errors'] > 0)
                                        <span class="text-gray-400 mx-1">•</span>
                                    @endif
                                    <span class="text-xl font-bold text-orange-600">{{ $errorSummary['total_warnings'] }}</span>
                                    <span class="text-sm font-medium text-orange-600">warnings</span>
                                @endif
                            @else
                                <span class="text-xl font-bold text-green-600">0</span>
                                <span class="text-sm font-medium text-green-600">issues</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Search Guests</label>
                <input type="text" id="searchInput" placeholder="Search by name or email..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Guests</option>
                    <option value="checked_in">Checked In</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Group</label>
                <select id="groupFilter" class="form-select">
                    <option value="">All Groups</option>
                    <!-- Will be populated via AJAX -->
                </select>
            </div>
            <div class="flex items-end">
                <button class="btn-secondary w-full" onclick="clearFilters()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Guests Table -->
    <div id="guestsTableContainer"></div>
    <!-- Mobile Bottom Spacer (to prevent toolbar overlap) -->
    <div class="h-20 md:hidden"></div>

    
</div>

<!-- Add Guest Modal -->
<div id="addGuestModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Guest</h3>
            <button type="button" class="modal-close" onclick="hideModal('addGuestModal')"></button>
        </div>
        <form id="addGuestForm" class="m-0 p-0">
            <div class="modal-body">
                <div class="space-y-6" id="addGuestFields">
                    <!-- Dynamic fields will be rendered here -->
                </div>
                <div id="addGuestError" class="text-red-500 text-sm hidden mt-2"></div>
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('addGuestModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Add Guest</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Guest Modal -->
<div id="editGuestModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Guest</h3>
            <button type="button" class="modal-close" onclick="hideModal('editGuestModal')"></button>
        </div>
        <form id="editGuestForm" class="m-0 p-0">
            <div class="modal-body">
                <div class="space-y-6" id="editGuestFields">
                    <!-- Dynamic fields will be rendered here -->
                </div>
                <div id="editGuestError" class="text-red-500 text-sm hidden mt-2"></div>
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('editGuestModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Update Guest</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Group Modal -->
<div id="addGroupModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Group</h3>
            <button type="button" class="modal-close" onclick="hideModal('addGroupModal')"></button>
        </div>
        <form id="addGroupForm" class="m-0 p-0">
            <div class="modal-body">
                <div class="space-y-6">
                    <div>
                        <label class="form-label">Group Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Group Name" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" placeholder="Description (optional)"></textarea>
                    </div>
                    <div id="addGroupError" class="text-red-500 text-sm hidden"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('addGroupModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Add Group</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-primary">Import Guests</h3>
            <button type="button" class="modal-close" onclick="hideModal('importModal')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="importForm" class="m-0 p-0">
            <div class="space-y-6">
                <div>
                    <label class="form-label">Import File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file" type="file" class="sr-only" accept=".csv,.xlsx,.xls">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">CSV, Excel files up to 10MB</p>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Import Format</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Your file should include columns: Name, Email, Phone (optional), Language (optional), Group (optional)</p>
                                <p class="text-xs mt-1">Group column will be used to automatically assign guests to groups. New groups will be created if they don't exist.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="fileImportGroupInfo" class="text-xs text-blue-600">
                    <strong>Note:</strong> The Group column will be used to automatically assign guests to groups. New groups will be created if they don't exist.
                </div>
            </div>
            <div class="modal-footer" style="margin-top: 0; padding-top: 16px; padding-bottom: 16px; border-top: 1px solid var(--border-primary); background: var(--bg-secondary); gap: 0;">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('importModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Import Guests</button>
            </div>
        </form>
    </div>
</div>

<!-- List Settings Modal -->
<div id="listSettingsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">List Settings</h3>
            <button type="button" class="modal-close" onclick="hideModal('listSettingsModal')"></button>
        </div>
        <form id="listSettingsForm" class="m-0 p-0" action="{{ route('organizer.guest-lists.updateSettings', $guestList) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="space-y-6">
                    <div>
                        <label class="form-label">List Name</label>
                        <input type="text" name="name" class="form-input" placeholder="Enter list name" value="{{ $guestList->name }}" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" placeholder="Describe your List">{{ $guestList->description }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Enable Guest Fields</label>
                            <div class="flex flex-col gap-2 mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="enable_email" class="form-checkbox" @if($guestList->settings['fields']['email'] ?? false) checked @endif>
                                    <span class="ml-2">Email</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enable_phone" class="form-checkbox" @if($guestList->settings['fields']['phone'] ?? false) checked @endif>
                                    <span class="ml-2">Phone</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enable_group" class="form-checkbox" @if($guestList->settings['fields']['group'] ?? false) checked @endif>
                                    <span class="ml-2">Group</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enable_language" class="form-checkbox" @if($guestList->settings['fields']['language'] ?? false) checked @endif>
                                    <span class="ml-2">Preferred Language</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Defaults</label>
                            <div class="mt-2">
                                <label class="block text-sm font-medium mb-1">Default Country Code</label>
                                <select name="default_country_code" class="form-input">
                                    <option value="+1" @if(($guestList->settings['default_country_code'] ?? '+1') == '+1') selected @endif>+1 (USA)</option>
                                    <option value="+44" @if(($guestList->settings['default_country_code'] ?? '+1') == '+44') selected @endif>+44 (UK)</option>
                                    <option value="+20" @if(($guestList->settings['default_country_code'] ?? '+1') == '+20') selected @endif>+20 (Egypt)</option>
                                    <!-- Add more as needed -->
                                </select>
                            </div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium mb-1">Default Language</label>
                                <input type="text" name="default_language" class="form-input" placeholder="e.g. en, ar, fr" value="{{ $guestList->settings['default_language'] ?? 'en' }}">
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">These settings control which fields are shown or required for guests, and set defaults for new guests.</div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('listSettingsModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Google Contacts Import Modal -->
<div id="googleContactsModal" class="modal hidden">
    <div class="modal-content w-full max-w-lg min-w-[90vw] sm:min-w-[400px] md:min-w-[500px] lg:min-w-[600px] max-h-[90vh] flex flex-col">
        <div class="modal-header">
            <h3 class="modal-title">Import from Google Contacts</h3>
            <button type="button" class="modal-close" onclick="hideModal('googleContactsModal')"></button>
        </div>
        <form id="googleContactsImportForm" class="m-0 p-0 flex flex-col flex-1">
            <div class="modal-body flex-1 flex flex-col min-h-0">
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Select contacts to import into this guest list.</p>
                    <p class="text-xs text-blue-600 mt-2" id="googleContactsGroupInfo" style="display: none;">
                        <strong>Note:</strong> When groups are enabled, contacts will be assigned to groups based on their contact information. New groups will be created if needed.
                    </p>
                </div>
                <!-- Search Bar -->
                <div class="mb-4">
                    <input type="text" id="googleContactsSearchInput" class="form-input w-full" placeholder="Search...">
                </div>
                <!-- Flex-grow table container -->
                <div class="flex-1 overflow-x-auto mb-4 min-h-[200px]">
                    <table class="min-w-full divide-y divide-gray-200 w-full">
                        <thead style="position: sticky; top: 0; background: var(--bg-primary); z-index: 2;">
                            <tr>
                                <th class="px-4 py-2"><input type="checkbox" class="form-checkbox" id="selectAllContacts" /></th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            </tr>
                        </thead>
                        <tbody id="googleContactsTable">
                            <!-- Contacts will be rendered here by JS -->
                        </tbody>
                    </table>
                </div>
                <div id="googleContactsGroupDiv" class="mb-4 hidden">
                    <label class="form-label">Assign to Group</label>
                    <select id="googleContactsGroupSelect" name="group_id" class="form-input">
                        <option value="">No group</option>
                        <!-- Groups will be populated by JS -->
                    </select>
                </div>
                <!-- Auto apply default language checkbox -->
                <div id="googleContactsDefaultLangDiv" class="mb-4 hidden">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="googleContactsApplyDefaultLanguage" class="form-checkbox w-4 h-4">
                        <span class="text-xs text-gray-500">Auto apply default language for imported guests</span>
                    </label>
                </div>
                
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('googleContactsModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Import Selected</button>
            </div>
        </form>
    </div>
</div>

<!-- Google Sheets Import Modal -->
<div id="googleSheetsModal" class="modal hidden">
    <div class="modal-content w-full max-w-lg min-w-[90vw] sm:min-w-[400px] md:min-w-[500px] lg:min-w-[600px] max-h-[90vh] flex flex-col">
        <div class="modal-header">
            <h3 class="modal-title">Import from Google Sheets</h3>
            <button type="button" class="modal-close" onclick="hideModal('googleSheetsModal')"></button>
        </div>
        <form id="googleSheetsImportForm" class="m-0 p-0 flex flex-col flex-1">
            <div class="modal-body flex-1 flex flex-col min-h-0">
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Select a Google Sheet with the required columns to import guests.</p>
                    <ul id="googleSheetsRequiredColumns" class="text-xs text-gray-500 mt-1 list-disc list-inside"></ul>
                    <p class="text-xs text-blue-600 mt-2" id="googleSheetsGroupInfo" style="display: none;">
                        <strong>Note:</strong> When groups are enabled, the Group column will be used to automatically assign guests to groups. New groups will be created if they don't exist.
                    </p>
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <input type="text" id="googleSheetsSearchInput" class="form-input flex-1" placeholder="Search sheets by name or title...">
                    <button type="button" id="refreshSheetsBtn" class="modal-btn modal-btn-secondary whitespace-nowrap">Refresh Sheets</button>
                </div>
                <div class="mb-4">
                    <div id="googleSheetsListContainer" class="overflow-y-auto max-h-64 pr-2">
                        <div class="text-xs text-gray-500 mb-2">Loading sheets...</div>
                    </div>
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <input type="checkbox" id="showUnvalidSheets" class="form-checkbox">
                    <span class="text-xs text-gray-500">Show unvalid sheets</span>
                </div>
                <div id="googleSheetsPreviewContainer" class="mb-4 hidden">
                    <div class="text-xs text-gray-500 mb-2">Sheet Preview:</div>
                    <div id="googleSheetsPreviewTable" class="overflow-x-auto"></div>
                </div>
                <div id="googleSheetsGroupDiv" class="mb-4 hidden">
                    <label class="form-label">Assign to Group</label>
                    <select id="googleSheetsGroupSelect" name="group_id" class="form-input">
                        <option value="">No group</option>
                        <!-- Groups will be populated by JS -->
                    </select>
                </div>

                
            </div>
            <div class="modal-footer d-flex justify-between items-center">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('googleSheetsModal')">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary" id="importGoogleSheetBtn" disabled>Import Selected Sheet</button>
            </div>
        </form>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="modal hidden">
    <div class="modal-content w-full max-w-lg min-w-[90vw] sm:min-w-[400px] md:min-w-[500px] lg:min-w-[600px]">
        <div class="modal-header">
            <h3 class="modal-title">Export Guest List</h3>
            <button type="button" class="modal-close" onclick="hideExportModal()"></button>
        </div>
        <div class="modal-body">
            <div class="mb-6">
                <h4 class="text-lg font-semibold mb-3">Export Statistics</h4>
                <div id="exportStats" class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center">
                            <div class="font-bold text-lg">-</div>
                            <div class="text-gray-600">Total Guests</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-lg text-green-600">-</div>
                            <div class="text-gray-600">Checked In</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-lg text-yellow-600">-</div>
                            <div class="text-gray-600">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-lg text-blue-600">-</div>
                            <div class="text-gray-600">Groups</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h4 class="text-lg font-semibold mb-3">Export Options</h4>
                <p class="text-sm text-gray-600 mb-4">Choose your preferred export format. The export will include all guests matching your current filters.</p>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 cursor-pointer" onclick="exportToCsv()">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium">CSV Export</div>
                                <div class="text-sm text-gray-500">Comma-separated values file</div>
                            </div>
                        </div>
                        <button id="exportCsvBtn" class="btn-primary">Export CSV</button>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 cursor-pointer" onclick="exportToExcel()">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium">Excel Export</div>
                                <div class="text-sm text-gray-500">Microsoft Excel file (.xlsx)</div>
                            </div>
                        </div>
                        <button id="exportExcelBtn" class="btn-primary">Export Excel</button>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 cursor-pointer" onclick="exportToPdf()">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium">PDF Export</div>
                                <div class="text-sm text-gray-500">Printable document</div>
                            </div>
                        </div>
                        <button id="exportPdfBtn" class="btn-primary">Export PDF</button>
                    </div>
                </div>
            </div>
            
            <div class="text-xs text-gray-500">
                <strong>Note:</strong> The export will respect your current search filters and group selections. 
                Only guests matching your criteria will be included in the export.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="hideExportModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedGuests = [];
let isExpandedAll = false;
window.guestListSettings = @json($guestList->settings);
window.guestListId = {{ $guestList->id }};


window.guestListGuests = @json($allGuests);
// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGroups();
    setupEventListeners();
    if (window.guestListSettings && window.guestListGuests && window.renderGuestTable) {
        window.renderGuestTable(window.guestListSettings, window.guestListGuests);
    }
});

function setupEventListeners() {
    // Search input
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterGuests();
        }, 300));
    }

    // Filters
    var statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterGuests);
    }
    var groupFilter = document.getElementById('groupFilter');
    if (groupFilter) {
        groupFilter.addEventListener('change', filterGuests);
    }

    // Select all checkbox
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.guest-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedGuests.push(checkbox.value);
                } else {
                    selectedGuests = [];
                }
            });
        });
    }

    // File upload preview
    var fileUpload = document.getElementById('file-upload');
    if (fileUpload) {
        fileUpload.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const label = document.querySelector('label[for=\"file-upload\"] span');
                label.textContent = fileName;
            }
        });
    }
}

function filterGuests() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const group = document.getElementById('groupFilter').value;
    
    const rows = document.querySelectorAll('#guestsTable tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2) .text-primary').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2) .text-gray-500').textContent.toLowerCase();
        const statusCell = row.querySelector('td:nth-child(4) .badge').textContent;
        const groupCell = row.querySelector('td:nth-child(3) .badge, td:nth-child(3) .text-gray-400');
        const groupText = groupCell ? groupCell.textContent : '';
        
        const matchesSearch = name.includes(search) || email.includes(search);
        const matchesStatus = !status || 
            (status === 'checked_in' && statusCell === 'Checked In') ||
            (status === 'pending' && statusCell === 'Pending');
        const matchesGroup = !group || groupText === group;
        
        row.style.display = matchesSearch && matchesStatus && matchesGroup ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('groupFilter').value = '';
    filterGuests();
}


function hideAddGuestModal() {
    hideModal('addGuestModal');
    document.getElementById('addGuestForm').reset();
}



// function loadGroups() {
//     fetch(`/organizer/guest-lists/{{ $guestList->id }}/groups`)
//         .then(response => response.json())
//         .then(data => {
//             const select = document.querySelector('select[name="group_id"]');
//             const filterSelect = document.getElementById('groupFilter');
            
//             select.innerHTML = '<option value="">No group</option>';
//             filterSelect.innerHTML = '<option value="">All Groups</option>';
            
//             data.forEach(group => {
//                 const option = document.createElement('option');
//                 option.value = group.id;
//                 option.textContent = group.name;
//                 select.appendChild(option.cloneNode(true));
//                 filterSelect.appendChild(option);
//             });
//         })
//         .catch(error => console.error('Error loading groups:', error));
// }





function editGuest(guestId) {
    // Redirect to edit page or show edit modal
    window.location.href = `/organizer/guests/${guestId}/edit`;
}



function exportGuests() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const group = document.getElementById('groupFilter').value;
    
    const url = `/organizer/guest-lists/{{ $guestList->id }}/export?search=${search}&status=${status}&group=${group}`;
    window.location.href = url;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Dropdown toggles
// Enhanced JavaScript functionality
let activeDropdown = null;

function toggleSelectionMode() {
    console.log('Selection mode toggled');
    // Add visual feedback
    const button = event.currentTarget;
    button.style.transform = 'scale(0.95)';
    setTimeout(() => button.style.transform = '', 150);
    collapseExpandAll();
}

function toggleImportMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('importMenu');
    const group = menu.closest('.group');
    
    // If clicking on the same menu that's already open, close it
    if (group.classList.contains('show')) {
        group.classList.remove('show');
        activeDropdown = null;
    } else {
        // Close any other open dropdowns first
        closeAllDropdowns();
        // Open this menu
        group.classList.add('show');
        activeDropdown = group;
    }
    collapseExpandAll();
}

function toggleExportMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('exportMenu');
    const group = menu.closest('.group');
    
    // If clicking on the same menu that's already open, close it
    if (group.classList.contains('show')) {
        group.classList.remove('show');
        activeDropdown = null;
    } else {
        // Close any other open dropdowns first
        closeAllDropdowns();
        // Open this menu
        group.classList.add('show');
        activeDropdown = group;
    }
    collapseExpandAll();
}

// New function to close all dropdowns
function closeAllDropdowns() {
    document.querySelectorAll('.group.show').forEach(group => {
        group.classList.remove('show');
    });
    activeDropdown = null;
}

// Enhanced closeDropdowns function
function closeDropdowns() {
    closeAllDropdowns();
}

function toggleExpandAll() {
    const toolbar = document.querySelector('.floating-toolbar');
    const expandIcon = document.getElementById('expandIcon');
    const expandText = document.getElementById('expandText');
    const expandBtn = document.getElementById('expandAllBtn');
    isExpandedAll = !isExpandedAll;
    if (isExpandedAll) {
        toolbar.classList.add('expand-all');
        expandBtn.classList.add('expand-all-collapse');
        expandText.textContent = 'Collapse All';
        expandIcon.classList.add('rotate-180');
    } else {
        toolbar.classList.remove('expand-all');
        expandBtn.classList.remove('expand-all-collapse');
        expandText.textContent = 'Expand All';
        expandIcon.classList.remove('rotate-180');
    }
    expandBtn.style.transform = 'scale(0.95)';
    setTimeout(() => expandBtn.style.transform = '', 150);
}

// Add this utility function for collapsing the expanded toolbar
function collapseExpandAll() {
    const toolbar = document.querySelector('.floating-toolbar');
    const expandBtn = document.getElementById('expandAllBtn');
    const expandIcon = document.getElementById('expandIcon');
    const expandText = document.getElementById('expandText');
    if (toolbar && toolbar.classList.contains('expand-all')) {
        toolbar.classList.remove('expand-all');
        expandBtn.classList.remove('expand-all-collapse');
        expandText.textContent = 'Expand All';
        expandIcon.classList.remove('rotate-180');
        isExpandedAll = false;
    }
}

// Add this utility function for closing both mobile dropdowns
function closeMobileDropdowns() {
    document.getElementById('mobileImportDropdown').classList.add('hidden');
    document.getElementById('mobileExportDropdown').classList.add('hidden');
    document.body.style.overflow = '';
}

function toggleMobileImportDropdown(event) {
    event.stopPropagation();
    const importDropdown = document.getElementById('mobileImportDropdown');
    const exportDropdown = document.getElementById('mobileExportDropdown');
    // Close export dropdown if open
    if (!exportDropdown.classList.contains('hidden')) {
        exportDropdown.classList.add('hidden');
    }
    importDropdown.classList.toggle('hidden');
    // Prevent background scroll when dropdown is open
    if (!importDropdown.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    } else if (exportDropdown.classList.contains('hidden')) {
        document.body.style.overflow = '';
    }
}

function toggleMobileExportDropdown(event) {
    event.stopPropagation();
    const importDropdown = document.getElementById('mobileImportDropdown');
    const exportDropdown = document.getElementById('mobileExportDropdown');
    // Close import dropdown if open
    if (!importDropdown.classList.contains('hidden')) {
        importDropdown.classList.add('hidden');
    }
    exportDropdown.classList.toggle('hidden');
    // Prevent background scroll when dropdown is open
    if (!exportDropdown.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    } else if (importDropdown.classList.contains('hidden')) {
        document.body.style.overflow = '';
    }
}

function showListSettingsModal() {
    const modal = document.getElementById('listSettingsModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
}
function hideListSettingsModal() {
    const modal = document.getElementById('listSettingsModal');
    modal.classList.remove('show');
    modal.classList.add('hidden');
}

// Close dropdowns when clicking outside (works for both mobile and desktop)
document.addEventListener('click', function(e) {
    // Check if click is outside any dropdown menu and dropdown button
    const isClickInsideDropdown = e.target.closest('.dropdown-menu') || e.target.closest('.toolbar-button');
    
    if (!isClickInsideDropdown) {
        closeAllDropdowns();
    }
});

// Enhanced keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAllDropdowns();
        const importDropdown = document.getElementById('mobileImportDropdown');
        const exportDropdown = document.getElementById('mobileExportDropdown');
        if (!importDropdown.classList.contains('hidden')) {
            importDropdown.classList.add('hidden');
            document.body.style.overflow = '';
        }
        if (!exportDropdown.classList.contains('hidden')) {
            exportDropdown.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
});


const addGroupForm = document.getElementById('addGroupForm');
if (addGroupForm) {
    addGroupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(addGroupForm);
        
        // Add _method field for POST request
        formData.append('_method', 'POST');
        
        const errorDiv = document.getElementById('addGroupError');
        if (errorDiv) errorDiv.classList.add('hidden');
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...';
        submitBtn.disabled = true;
        
        window.addGroup(formData)
            .then(() => {
                if (typeof hideModal === 'function') hideModal('addGroupModal');
                addGroupForm.reset();
            })
            .catch(err => {
                if (errorDiv) {
                    errorDiv.textContent = err.message || 'Error adding group';
                    errorDiv.classList.remove('hidden');
                } else {
                    alert(err.message || 'Error adding guest');
                }
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    });
}

const addGuestForm = document.getElementById('addGuestForm');
if (addGuestForm) {
    addGuestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(addGuestForm);
        
        // Handle empty group_id - remove if empty to avoid validation issues
        const groupId = formData.get('group_id');
        if (groupId === '') {
            formData.delete('group_id');
        }
        
        // Add _method field for POST request
        formData.append('_method', 'POST');
        
        const errorDiv = document.getElementById('addGuestError');
        if (errorDiv) errorDiv.classList.add('hidden');
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...';
        submitBtn.disabled = true;
        
        window.addGuest(formData)
            .then(() => {
                if (typeof hideAddGuestModal === 'function') hideAddGuestModal();
                addGuestForm.reset();
            })
            .catch(err => {
                if (errorDiv) {
                    errorDiv.textContent = err.message || 'Error adding guest';
                    errorDiv.classList.remove('hidden');
                } else {
                    alert(err.message || 'Error adding guest');
                }
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    });
}

const editGuestForm = document.getElementById('editGuestForm');
if (editGuestForm) {
    editGuestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Collect form data as regular object instead of FormData
        const formDataObj = {};
        const formElements = this.elements;
        
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i];
            if (element.name && element.type !== 'submit') {
                if (element.type === 'checkbox') {
                    formDataObj[element.name] = element.checked;
                } else if (element.type === 'select-multiple') {
                    const selectedOptions = Array.from(element.selectedOptions).map(option => option.value);
                    formDataObj[element.name] = selectedOptions;
                } else {
                    formDataObj[element.name] = element.value;
                }
            }
        }
        
        // Handle empty group_id - remove if empty to avoid validation issues
        if (formDataObj.group_id === '') {
            delete formDataObj.group_id;
        }
        
        const errorDiv = document.getElementById('editGuestError');
        if (errorDiv) errorDiv.classList.add('hidden');
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        
        window.updateGuest(formDataObj)
            .then(() => {
                if (typeof hideModal === 'function') hideModal('editGuestModal');
                editGuestForm.reset();
            })
            .catch(err => {
                if (errorDiv) {
                    errorDiv.textContent = err.message || 'Error updating guest';
                    errorDiv.classList.remove('hidden');
                } else {
                    alert(err.message || 'Error updating guest');
                }
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    });
}


</script>

<script>
window.guestListId = {{ $guestList->id }};
window.guestListSettings = @json($guestList->settings);
window.validationErrors = @json($validationErrors ?? []);
window.errorSummary = @json($errorSummary ?? []);

// Initialize the error display on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updateErrorDisplay === 'function') {
        updateErrorDisplay();
    }
    
    // Refresh validation errors to ensure we have the most up-to-date data
    if (typeof refreshValidationErrors === 'function') {
        refreshValidationErrors();
    }
});

// Function to show validation details
window.showValidationDetails = function() {
    const modal = document.getElementById('validationDetailsModal');
    const content = document.getElementById('validationDetailsContent');
    
    if (!modal || !content) return;
    
    let html = '';
    
    if (window.validationErrors && window.validationErrors.length > 0) {
        // Add summary section
        const totalErrors = window.validationErrors.filter(e => e.severity === 'error').reduce((sum, e) => sum + e.count, 0);
        const totalWarnings = window.validationErrors.filter(e => e.severity === 'warning').reduce((sum, e) => sum + e.count, 0);
        
        html += `
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Summary</h3>
                        <p class="text-sm text-gray-600">Overview of all issues found in your guest list</p>
                    </div>
                    <div class="flex space-x-4">
                        ${totalErrors > 0 ? `
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">${totalErrors}</div>
                                <div class="text-xs text-red-600 font-medium">Errors</div>
                            </div>
                        ` : ''}
                        ${totalWarnings > 0 ? `
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">${totalWarnings}</div>
                                <div class="text-xs text-orange-600 font-medium">Warnings</div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        window.validationErrors.forEach(error => {
            const severityColor = error.severity === 'error' ? 'text-red-600' : 'text-orange-600';
            const severityBg = error.severity === 'error' ? 'bg-red-50' : 'bg-orange-50';
            const severityBorder = error.severity === 'error' ? 'border-red-200' : 'border-orange-200';
            const severityIcon = error.severity === 'error' ? 'bg-red-100' : 'bg-orange-100';
            
            html += `
                <div class="bg-white rounded-xl border ${severityBorder} p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="p-2 rounded-lg ${severityIcon} mr-3">
                            <svg class="w-6 h-6 ${severityColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">${error.message}</h4>
                            <p class="text-sm text-gray-600">${error.count} issue${error.count > 1 ? 's' : ''} found</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${severityBg} ${severityColor}">
                            ${error.severity === 'error' ? 'Critical' : 'Warning'}
                        </span>
                    </div>
                    <div class="space-y-3">
                        ${generateErrorDetails(error)}
                    </div>
                </div>
            `;
        });
    } else {
        html = `
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Perfect! No Issues Found</h3>
                <p class="text-gray-600 max-w-md mx-auto">Your guest list is clean and ready to use. All data is properly formatted and complete.</p>
            </div>
        `;
    }
    
    content.innerHTML = html;
    showModal('validationDetailsModal');
};

// Function to generate error details based on error type
function generateErrorDetails(error) {
    let details = '';
    
    switch (error.type) {
        case 'duplicate_email':
            details = error.details.map(dup => `
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="p-2 rounded-lg bg-red-100 mr-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Duplicate Email</p>
                                <p class="text-sm text-gray-600 font-mono">${dup.value}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${dup.count} guest${dup.count > 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Affected guests:</p>
                        <div class="space-y-1">
                            ${dup.guest_names.map(name => `
                                <div class="flex items-center text-sm text-gray-600">
                                    <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                    ${name}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
            break;
            
        case 'duplicate_phone':
            details = error.details.map(dup => `
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="p-2 rounded-lg bg-orange-100 mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Duplicate Phone</p>
                                <p class="text-sm text-gray-600 font-mono">${dup.value}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            ${dup.count} guest${dup.count > 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Affected guests:</p>
                        <div class="space-y-1">
                            ${dup.guest_names.map(name => `
                                <div class="flex items-center text-sm text-gray-600">
                                    <div class="w-2 h-2 bg-orange-400 rounded-full mr-2"></div>
                                    ${name}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
            break;
            
        case 'missing_fields':
            details = error.details.map(guest => `
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="p-2 rounded-lg bg-red-100 mr-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Missing Required Fields</p>
                                <p class="text-sm text-gray-600">${guest.guest_name}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${guest.missing_fields.length} field${guest.missing_fields.length > 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Missing fields:</p>
                        <div class="flex flex-wrap gap-2">
                            ${guest.missing_fields.map(field => `
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ${field.charAt(0).toUpperCase() + field.slice(1)}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
            break;
            
        case 'invalid_phone_format':
            details = error.details.map(phone => `
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="p-2 rounded-lg bg-orange-100 mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Invalid Phone Format</p>
                                <p class="text-sm text-gray-600">${phone.guest_name}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Format Issue
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Current:</span>
                            <span class="text-sm font-mono text-gray-600">${phone.phone}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Suggested:</span>
                            <span class="text-sm font-mono text-blue-600 font-medium">${phone.suggestion}</span>
                        </div>
                    </div>
                </div>
            `).join('');
            break;
    }
    
    return details;
}
</script>
<script src="/js/organizerJs/guest-list-table.js"></script>
<script src="/js/organizerJs/guest-list-settings.js"></script>
<script src="/js/organizerJs/guest-list-actions.js"></script>
<script src="/js/organizerJs/guest-list-google-import.js"></script>
<script src="{{ asset('js/organizerJs/guest-list-file-import.js') }}"></script>
<script src="{{ asset('js/organizerJs/guest-list-export.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('import_google_contacts') === '1') {
        setTimeout(() => {
            if (window.showGoogleContactsModal) {
                window.showGoogleContactsModal();
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }, 300);
    } else if (urlParams.get('import_google_sheets') === '1') {
        setTimeout(() => {
            if (window.showGoogleSheetsModal) {
                window.showGoogleSheetsModal();
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }, 300);
    }
});
</script>

<!-- Validation Details Modal -->
<div id="validationDetailsModal" class="modal hidden">
    <div class="modal-content max-w-4xl mx-4">
        <div class="modal-header border-b border-gray-200 pb-4">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100 mr-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="modal-title text-xl font-semibold text-gray-900">List Health Report</h3>
                    <p class="text-sm text-gray-600 mt-1">Detailed analysis of your guest list</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="hideModal('validationDetailsModal')"></button>
        </div>
        <div class="modal-body py-6">
            <div id="validationDetailsContent" class="space-y-6">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer border-t border-gray-200 pt-4">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="hideModal('validationDetailsModal')">Close</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<x-confirmation-modal 
    id="confirmationModal"
    title="Confirm Action"
    message="Are you sure you want to proceed with this action?"
    confirm-text="Confirm"
    cancel-text="Cancel"
/>

@endpush 